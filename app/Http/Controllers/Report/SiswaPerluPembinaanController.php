<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\PembinaanInternalRule;
use App\Services\Pelanggaran\PelanggaranRulesEngine;
use Illuminate\Http\Request;

class SiswaPerluPembinaanController extends Controller
{
    protected $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }

    /**
     * Display list siswa yang perlu pembinaan berdasarkan akumulasi poin.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $ruleId = $request->get('rule_id');
        $kelasId = $request->get('kelas_id');
        $jurusanId = $request->get('jurusan_id');
        
        // Get all rules untuk filter dropdown
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        // Get siswa perlu pembinaan
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
        
        // =====================================================================
        // CRITICAL FIX: Filter by pembina role authorization
        // =====================================================================
        // Siswa hanya ditampilkan jika role user saat ini termasuk dalam
        // pembina_roles untuk siswa tersebut.
        // 
        // Logic:
        // - Kepala Sekolah: Lihat semua (sudah diberi akses penuh)
        // - Waka Kesiswaan: Lihat siswa yang melibatkan "Waka Kesiswaan"
        // - Kaprodi: Lihat siswa di jurusan binaan yang melibatkan "Kaprodi"
        // - Wali Kelas: Lihat siswa di kelas binaan yang melibatkan "Wali Kelas"
        // =====================================================================
        
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        // Apply pembina_roles filtering for ALL roles (including Kepala Sekolah)
        $siswaList = $siswaList->filter(function ($item) use ($userRole, $user) {
            $pembinaRoles = $item['rekomendasi']['pembina_roles'] ?? [];
            
            // BUGFIX: Handle if pembina_roles is still JSON string
            if (is_string($pembinaRoles)) {
                $pembinaRoles = json_decode($pembinaRoles, true) ?? [];
            }
            
            // Check if user's role is in the recommended pembina roles
            if (!in_array($userRole, $pembinaRoles)) {
                return false;
            }
            
            // Additional context-based filtering (only for Wali Kelas & Kaprodi)
            $siswa = $item['siswa'];
            
            // Wali Kelas: hanya siswa di kelas binaan (STRICT - no exceptions)
            if ($userRole === 'Wali Kelas') {
                $kelasBinaan = $user->kelasDiampu;
                
                // If no kelas assigned, show nothing
                if (!$kelasBinaan) {
                    return false;
                }
                
                // Strict kelas match
                if ($siswa->kelas_id !== $kelasBinaan->id) {
                    return false;
                }
            }
            
            // Kaprodi: hanya siswa di jurusan binaan (STRICT)
            if ($userRole === 'Kaprodi') {
                $jurusanBinaan = $user->jurusanDiampu;
                
                // If no jurusan assigned, show nothing
                if (!$jurusanBinaan) {
                    return false;
                }
                
                // Strict jurusan match
                if (!$siswa->kelas || $siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                    return false;
                }
            }
            
            // Waka Kesiswaan & Kepala Sekolah: tidak perlu filter additional (semua sekolah)
            // Sudah cukup dengan pembina_roles check di atas
            
            return true;
        });
        
        // Filter by kelas
        if ($kelasId) {
            $siswaList = $siswaList->filter(function ($item) use ($kelasId) {
                return $item['siswa']->kelas_id == $kelasId;
            });
        }
        
        // Filter by jurusan
        if ($jurusanId) {
            $siswaList = $siswaList->filter(function ($item) use ($jurusanId) {
                return $item['siswa']->kelas->jurusan_id == $jurusanId;
            });
        }
        
        // Get kelas & jurusan untuk filter dropdown
        $kelasList = \App\Models\Kelas::orderBy('nama_kelas')->get();
        $jurusanList = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
        
        // Statistics
        $stats = [
            'total_siswa' => $siswaList->count(),
            'by_range' => [],
        ];
        
        foreach ($rules as $rule) {
            $count = $siswaList->filter(function ($item) use ($rule) {
                return $rule->matchesPoin($item['total_poin']);
            })->count();
            
            $stats['by_range'][] = [
                'rule' => $rule,
                'count' => $count,
            ];
        }
        
        return view('kepala_sekolah.siswa_perlu_pembinaan.index', compact(
            'siswaList',
            'rules',
            'kelasList',
            'jurusanList',
            'stats',
            'ruleId',
            'kelasId',
            'jurusanId'
        ));
    }

    /**
     * Export to CSV.
     */
    public function exportCsv(Request $request)
    {
        $ruleId = $request->get('rule_id');
        $kelasId = $request->get('kelas_id');
        $jurusanId = $request->get('jurusan_id');
        
        // Get filtered data (same logic as index)
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
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
        
        // =====================================================================
        // Apply same role-based filtering as index method
        // =====================================================================
        $user = auth()->user();
        $userRole = \App\Services\User\RoleService::effectiveRoleName($user);
        
        // Apply pembina_roles filtering for ALL roles (including Kepala Sekolah)
        $siswaList = $siswaList->filter(function ($item) use ($userRole, $user) {
            $pembinaRoles = $item['rekomendasi']['pembina_roles'] ?? [];
            
            if (is_string($pembinaRoles)) {
                $pembinaRoles = json_decode($pembinaRoles, true) ?? [];
            }
            
            // Check if user's role is in the recommended pembina roles
            if (!in_array($userRole, $pembinaRoles)) {
                return false;
            }
            
            $siswa = $item['siswa'];
            
            // Wali Kelas: hanya siswa di kelas binaan
            if ($userRole === 'Wali Kelas') {
                $kelasBinaan = $user->kelasDiampu;
                if ($kelasBinaan && $siswa->kelas_id !== $kelasBinaan->id) {
                    return false;
                }
            }
            
            // Kaprodi: hanya siswa di jurusan binaan
            if ($userRole === 'Kaprodi') {
                $jurusanBinaan = $user->jurusanDiampu;
                if (!$jurusanBinaan || !$siswa->kelas || $siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                    return false;
                }
            }
            
            return true;
        });
        
        if ($kelasId) {
            $siswaList = $siswaList->filter(fn($item) => $item['siswa']->kelas_id == $kelasId);
        }
        
        if ($jurusanId) {
            $siswaList = $siswaList->filter(fn($item) => $item['siswa']->kelas->jurusan_id == $jurusanId);
        }
        
        // Generate CSV
        $filename = 'siswa_perlu_pembinaan_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($siswaList) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['NIS', 'Nama', 'Kelas', 'Jurusan', 'Total Poin', 'Range Poin', 'Rekomendasi Pembinaan', 'Pembina']);
            
            // Data
            foreach ($siswaList as $item) {
                fputcsv($file, [
                    $item['siswa']->nis,
                    $item['siswa']->nama_lengkap,
                    $item['siswa']->kelas->nama_kelas ?? '-',
                    $item['siswa']->kelas->jurusan->nama_jurusan ?? '-',
                    $item['total_poin'],
                    $item['rekomendasi']['range_text'],
                    $item['rekomendasi']['keterangan'],
                    implode(', ', $item['rekomendasi']['pembina_roles']),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF.
     */
    public function exportPdf(Request $request)
    {
        // Similar to exportCsv but generate PDF
        // For now, redirect to CSV (PDF implementation can be added later)
        return $this->exportCsv($request);
    }
}
