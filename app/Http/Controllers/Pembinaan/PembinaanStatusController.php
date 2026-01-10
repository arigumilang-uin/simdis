<?php

namespace App\Http\Controllers\Pembinaan;

use App\Http\Controllers\Controller;
use App\Services\Pembinaan\PembinaanService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pembinaan Status Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil PembinaanService
 * - Return Response
 * 
 * Mengelola workflow pembinaan internal siswa:
 * - List siswa perlu pembinaan
 * - Mulai pembinaan
 * - Selesaikan pembinaan
 * 
 * @package App\Http\Controllers\Pembinaan
 */
class PembinaanStatusController extends Controller
{
    public function __construct(
        private PembinaanService $pembinaanService
    ) {}

    /**
     * Display list siswa yang perlu pembinaan dengan status tracking.
     */
    public function index(Request $request): View
    {
        $filters = [
            'rule_id' => $request->get('rule_id'),
            'kelas_id' => $request->get('kelas_id'),
            'jurusan_id' => $request->get('jurusan_id'),
            'status' => $request->get('status'),
        ];

        $user = auth()->user();
        $result = $this->pembinaanService->getPembinaanList($user, $filters);

        return view('pembinaan.index', [
            'pembinaanList' => $result['list'],
            'stats' => $result['stats'],
            'rules' => $this->pembinaanService->getAllRules(),
            'kelasList' => $this->pembinaanService->getAllKelas(),
            'jurusanList' => $this->pembinaanService->getAllJurusan(),
            'ruleId' => $filters['rule_id'],
            'kelasId' => $filters['kelas_id'],
            'jurusanId' => $filters['jurusan_id'],
            'statusFilter' => $filters['status'],
        ]);
    }

    /**
     * Mulai pembinaan.
     */
    public function mulaiPembinaan(int $id, Request $request): RedirectResponse
    {
        $user = auth()->user();
        $catatan = $request->input('catatan');

        $result = $this->pembinaanService->mulaiPembinaan($id, $user, $catatan);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Selesaikan pembinaan.
     */
    public function selesaikanPembinaan(int $id, Request $request): RedirectResponse
    {
        $user = auth()->user();
        $hasilPembinaan = $request->input('hasil_pembinaan') ?? '';

        $result = $this->pembinaanService->selesaikanPembinaan($id, $user, $hasilPembinaan);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Show detail pembinaan.
     */
    public function show(int $id): View
    {
        $pembinaan = $this->pembinaanService->getPembinaanDetail($id);
        return view('pembinaan.show', compact('pembinaan'));
    }

    /**
     * Export to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $list = $this->pembinaanService->getExportData($user, $request->status);

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
