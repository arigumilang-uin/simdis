<?php

namespace App\Http\Controllers\Pembinaan;

use App\Http\Controllers\Controller;
use App\Models\PembinaanStatus;
use App\Models\PembinaanInternalRule;
use App\Enums\StatusPembinaan;
use App\Services\Pelanggaran\PelanggaranRulesEngine;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Pembinaan Status Controller
 * 
 * Mengelola workflow pembinaan internal siswa:
 * - List siswa perlu pembinaan (dengan status tracking)
 * - Mulai pembinaan
 * - Selesaikan pembinaan
 */
class PembinaanStatusController extends Controller
{
    protected PelanggaranRulesEngine $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }

    /**
     * Display list siswa yang perlu pembinaan dengan status tracking.
     */
    public function index(Request $request): View
    {
        $ruleId = $request->get('rule_id');
        $kelasId = $request->get('kelas_id');
        $jurusanId = $request->get('jurusan_id');
        $statusFilter = $request->get('status');
        
        // Get all rules untuk filter dropdown
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        // Get siswa perlu pembinaan dari RulesEngine
        $poinMin = null;
        $poinMax = null;
        
        if ($ruleId) {
            $selectedRule = $rules->find($ruleId);
            if ($selectedRule) {
                $poinMin = $selectedRule->poin_min;
                $poinMax = $selectedRule->poin_max;
            }
        }
        
        $siswaList = $this->rulesEngine->getSiswaPerluPembinaan($poinMin, $poinMax);
        
        // Apply role-based filtering
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        $siswaList = $siswaList->filter(function ($item) use ($userRole, $user) {
            $pembinaRoles = $item['rekomendasi']['pembina_roles'] ?? [];
            
            if (is_string($pembinaRoles)) {
                $pembinaRoles = json_decode($pembinaRoles, true) ?? [];
            }
            
            if (!in_array($userRole, $pembinaRoles)) {
                return false;
            }
            
            $siswa = $item['siswa'];
            
            if ($userRole === 'Wali Kelas') {
                $kelasBinaan = $user->kelasDiampu;
                if (!$kelasBinaan || $siswa->kelas_id !== $kelasBinaan->id) {
                    return false;
                }
            }
            
            if ($userRole === 'Kaprodi') {
                $jurusanBinaan = $user->jurusanDiampu;
                if (!$jurusanBinaan || !$siswa->kelas || $siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Filter by kelas
        if ($kelasId) {
            $siswaList = $siswaList->filter(fn($item) => $item['siswa']->kelas_id == $kelasId);
        }
        
        // Filter by jurusan
        if ($jurusanId) {
            $siswaList = $siswaList->filter(fn($item) => $item['siswa']->kelas->jurusan_id == $jurusanId);
        }
        
        // =====================================================================
        // CRITICAL: Sync pembinaan status records
        // =====================================================================
        // Untuk setiap siswa, pastikan ada record di pembinaan_status
        // jika belum ada yang aktif untuk rule yang sesuai.
        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            $rekomendasi = $item['rekomendasi'];
            $totalPoin = $item['total_poin'];
            
            // Find matching rule
            $matchingRule = $rules->first(fn($rule) => $rule->matchesPoin($totalPoin));
            
            if ($matchingRule) {
                // Auto-create pembinaan status if not exists
                PembinaanStatus::createIfNotExists(
                    $siswa->id,
                    $matchingRule->id,
                    $totalPoin,
                    $rekomendasi['range_text'],
                    $rekomendasi['keterangan'],
                    $rekomendasi['pembina_roles']
                );
            }
        }
        
        // =====================================================================
        // Get pembinaan status records with eager loading
        // =====================================================================
        $query = PembinaanStatus::with([
            'siswa.kelas.jurusan',
            'rule',
            'dibinaOleh',
            'diselesaikanOleh',
        ]);
        
        // Apply role-based scope
        if ($userRole === 'Wali Kelas') {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasBinaan->id));
            }
        } elseif ($userRole === 'Kaprodi') {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanBinaan->id));
            }
        }
        
        // Filter by status
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        // Filter by additional criteria
        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }
        if ($jurusanId) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanId));
        }
        if ($ruleId) {
            $query->where('pembinaan_rule_id', $ruleId);
        }
        
        // Filter by pembina_roles (user's role must be in pembina_roles)
        $query->whereJsonContains('pembina_roles', $userRole);
        
        $pembinaanList = $query->orderByDesc('created_at')->get();
        
        // Statistics
        $stats = [
            'total' => $pembinaanList->count(),
            'perlu_pembinaan' => $pembinaanList->where('status', StatusPembinaan::PERLU_PEMBINAAN)->count(),
            'sedang_dibina' => $pembinaanList->where('status', StatusPembinaan::SEDANG_DIBINA)->count(),
            'selesai' => $pembinaanList->where('status', StatusPembinaan::SELESAI)->count(),
        ];
        
        // Get kelas & jurusan untuk filter dropdown
        $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get();
        $jurusanList = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
        
        return view('pembinaan.index', compact(
            'pembinaanList',
            'rules',
            'kelasList',
            'jurusanList',
            'stats',
            'ruleId',
            'kelasId',
            'jurusanId',
            'statusFilter'
        ));
    }

    /**
     * Mulai pembinaan.
     */
    public function mulaiPembinaan(int $id, Request $request): RedirectResponse
    {
        $pembinaan = PembinaanStatus::findOrFail($id);
        
        // Authorization check
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        if (!in_array($userRole, $pembinaan->pembina_roles)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk membina siswa ini.');
        }
        
        if (!$pembinaan->mulaiPembinaan($user->id)) {
            return back()->with('error', 'Tidak dapat memulai pembinaan. Status saat ini bukan "Perlu Pembinaan".');
        }
        
        // Simpan catatan jika ada
        if ($request->filled('catatan')) {
            $pembinaan->update(['catatan_pembinaan' => $request->input('catatan')]);
        }
        
        // Log activity
        activity()
            ->performedOn($pembinaan)
            ->causedBy($user)
            ->withProperties([
                'siswa_nama' => $pembinaan->siswa->nama_siswa,
                'old_status' => 'Perlu Pembinaan',
                'new_status' => 'Sedang Dibina',
            ])
            ->log('Memulai pembinaan internal');
        
        return back()->with('success', 'Pembinaan untuk ' . $pembinaan->siswa->nama_siswa . ' berhasil dimulai!');
    }

    /**
     * Selesaikan pembinaan.
     */
    public function selesaikanPembinaan(int $id, Request $request): RedirectResponse
    {
        $pembinaan = PembinaanStatus::findOrFail($id);
        
        // Authorization check
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        // User yang menyelesaikan harus yang mulai atau pembina yang ditugaskan
        $canComplete = $pembinaan->dibina_oleh_user_id === $user->id 
                    || in_array($userRole, $pembinaan->pembina_roles);
        
        if (!$canComplete) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyelesaikan pembinaan ini.');
        }
        
        $hasilPembinaan = $request->input('hasil_pembinaan', '');
        
        if (!$pembinaan->selesaikanPembinaan($user->id, $hasilPembinaan)) {
            return back()->with('error', 'Tidak dapat menyelesaikan pembinaan. Status saat ini bukan "Sedang Dibina".');
        }
        
        // Log activity
        activity()
            ->performedOn($pembinaan)
            ->causedBy($user)
            ->withProperties([
                'siswa_nama' => $pembinaan->siswa->nama_siswa,
                'old_status' => 'Sedang Dibina',
                'new_status' => 'Selesai',
                'hasil_pembinaan' => $hasilPembinaan,
            ])
            ->log('Menyelesaikan pembinaan internal');
        
        return back()->with('success', 'Pembinaan untuk ' . $pembinaan->siswa->nama_siswa . ' berhasil diselesaikan!');
    }

    /**
     * Show detail pembinaan.
     */
    public function show(int $id): View
    {
        $pembinaan = PembinaanStatus::with([
            'siswa.kelas.jurusan',
            'rule',
            'dibinaOleh',
            'diselesaikanOleh',
        ])->findOrFail($id);
        
        return view('pembinaan.show', compact('pembinaan'));
    }

    /**
     * Export to CSV.
     */
    public function exportCsv(Request $request)
    {
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        $query = PembinaanStatus::with(['siswa.kelas.jurusan', 'rule', 'dibinaOleh'])
            ->whereJsonContains('pembina_roles', $userRole);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $list = $query->orderByDesc('created_at')->get();
        
        $filename = 'pembinaan_status_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($list) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            
            fputcsv($file, [
                'NISN', 'Nama Siswa', 'Kelas', 'Jurusan', 
                'Total Poin', 'Range', 'Keterangan', 
                'Status', 'Dibina Oleh', 'Tanggal Dibina', 'Tanggal Selesai'
            ]);
            
            foreach ($list as $item) {
                fputcsv($file, [
                    $item->siswa->nisn,
                    $item->siswa->nama_siswa,
                    $item->siswa->kelas->nama_kelas ?? '-',
                    $item->siswa->kelas->jurusan->nama_jurusan ?? '-',
                    $item->total_poin_saat_trigger,
                    $item->range_text,
                    $item->keterangan_pembinaan,
                    $item->status->value,
                    $item->dibinaOleh->nama ?? '-',
                    $item->dibina_at?->format('d/m/Y H:i') ?? '-',
                    $item->selesai_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
