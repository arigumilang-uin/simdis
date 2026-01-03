<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show report index/builder
     */
    public function index()
    {
        $jurusans = Jurusan::all();
        $kelas = Kelas::all();
        
        return view('kepala_sekolah.reports.index', [
            'jurusans' => $jurusans,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Generate report preview (HTML)
     * 
     * Support multiple report types:
     * - pelanggaran: Daftar riwayat pelanggaran
     * - siswa: Daftar siswa bermasalah (dari tindak lanjut)
     * - tindakan: Daftar tindak lanjut
     */
    public function preview(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:pelanggaran,siswa,tindakan',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date',
        ]);

        $reportType = $request->report_type;
        $filters = $request->all();

        // Choose model based on report type
        if ($reportType === 'pelanggaran') {
            $data = $this->getPelanggaranData($request);
        } else {
            // For 'siswa' and 'tindakan', use TindakLanjut model
            $data = $this->getTindakLanjutData($request, $reportType);
        }

        // Store in session for export
        session([
            'report_data' => $data, 
            'report_filters' => $filters,
            'report_type' => $reportType
        ]);

        return view('kepala_sekolah.reports.preview', [
            'data' => $data,
            'filters' => $filters,
            'reportType' => $this->getReportTypeLabel($reportType),
        ]);
    }

    /**
     * Get Pelanggaran data for report
     */
    private function getPelanggaranData(Request $request)
    {
        $query = RiwayatPelanggaran::query();

        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('periode_mulai')) {
            $query->where('tanggal_kejadian', '>=', $request->periode_mulai);
        }

        if ($request->filled('periode_akhir')) {
            $query->where('tanggal_kejadian', '<=', $request->periode_akhir);
        }

        return $query->with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'user'])
                     ->orderBy('tanggal_kejadian', 'desc')
                     ->get();
    }

    /**
     * Get TindakLanjut data for report
     */
    private function getTindakLanjutData(Request $request, string $reportType)
    {
        $query = \App\Models\TindakLanjut::with(['siswa.kelas.jurusan']);

        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('periode_mulai')) {
            $query->where('created_at', '>=', $request->periode_mulai . ' 00:00:00');
        }

        if ($request->filled('periode_akhir')) {
            $query->where('created_at', '<=', $request->periode_akhir . ' 23:59:59');
        }

        // Filter by status based on report type
        if ($reportType === 'siswa') {
            // Siswa bermasalah: semua yang punya tindak lanjut aktif
            $query->whereIn('status', ['Menunggu Persetujuan', 'Disetujui', 'Ditangani']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get human-readable report type label
     */
    private function getReportTypeLabel(string $type): string
    {
        return match($type) {
            'pelanggaran' => 'Laporan Pelanggaran',
            'siswa' => 'Laporan Siswa Bermasalah',
            'tindakan' => 'Laporan Tindak Lanjut',
            default => 'Laporan',
        };
    }

    /**
     * Export to CSV - Supports both RiwayatPelanggaran and TindakLanjut data
     */
    public function exportCsv()
    {
        $data = session('report_data', []);
        $filters = session('report_filters', []);
        $reportType = session('report_type', 'pelanggaran');

        if (empty($data) || (is_countable($data) && count($data) === 0)) {
            return redirect()->route('kepala-sekolah.reports.index')
                ->with('error', 'Tidak ada data untuk diexport. Buat laporan terlebih dahulu.');
        }

        $typeSlug = str_replace(' ', '_', strtolower($reportType));
        $filename = "laporan_{$typeSlug}_" . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($data, $reportType) {
            // UTF-16LE BOM untuk Excel
            echo "\xFF\xFE";
            
            // Header based on report type
            if ($reportType === 'pelanggaran') {
                $headerRow = "NISN\tNama Siswa\tKelas\tJurusan\tJenis Pelanggaran\tTanggal Kejadian\tDicatat Oleh\n";
            } else {
                $headerRow = "NISN\tNama Siswa\tKelas\tJurusan\tKeterangan\tTanggal\tStatus\n";
            }
            echo mb_convert_encoding($headerRow, 'UTF-16LE', 'UTF-8');
            
            foreach ($data as $row) {
                if ($reportType === 'pelanggaran') {
                    // RiwayatPelanggaran model
                    $dataRow = (
                        ($row->siswa->nisn ?? '') . "\t" .
                        ($row->siswa->nama_siswa ?? '') . "\t" .
                        ($row->siswa->kelas->nama_kelas ?? '') . "\t" .
                        ($row->siswa->kelas->jurusan->nama_jurusan ?? '') . "\t" .
                        ($row->jenisPelanggaran->nama ?? '') . "\t" .
                        ($row->tanggal_kejadian ?? '') . "\t" .
                        ($row->user->nama ?? $row->user->username ?? '') . "\n"
                    );
                } else {
                    // TindakLanjut model
                    $statusValue = is_object($row->status) ? $row->status->value : $row->status;
                    $dataRow = (
                        ($row->siswa->nisn ?? '') . "\t" .
                        ($row->siswa->nama_siswa ?? '') . "\t" .
                        ($row->siswa->kelas->nama_kelas ?? '') . "\t" .
                        ($row->siswa->kelas->jurusan->nama_jurusan ?? '') . "\t" .
                        ($row->sanksi_deskripsi ?? $row->pemicu ?? '') . "\t" .
                        ($row->created_at ? $row->created_at->format('Y-m-d H:i') : '') . "\t" .
                        $statusValue . "\n"
                    );
                }
                echo mb_convert_encoding($dataRow, 'UTF-16LE', 'UTF-8');
            }
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export to PDF - Supports both RiwayatPelanggaran and TindakLanjut data
     * Note: Install via: composer require barryvdh/laravel-dompdf
     */
    public function exportPdf()
    {
        $data = session('report_data', []);
        $filters = session('report_filters', []);
        $reportType = session('report_type', 'pelanggaran');

        if (empty($data) || (is_countable($data) && count($data) === 0)) {
            return redirect()->route('kepala-sekolah.reports.index')
                ->with('error', 'Tidak ada data untuk diexport. Buat laporan terlebih dahulu.');
        }

        // Check if dompdf is installed
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            return redirect()->back()
                ->with('warning', 'PDF export belum dikonfigurasi. Gunakan CSV untuk sekarang.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kepala_sekolah.reports.pdf', [
            'data' => $data,
            'filters' => $filters,
            'reportType' => $this->getReportTypeLabel($reportType),
        ]);

        $typeSlug = str_replace(' ', '_', strtolower($reportType));
        return $pdf->download("laporan_{$typeSlug}_" . now()->format('Ymd_His') . '.pdf');
    }
}


