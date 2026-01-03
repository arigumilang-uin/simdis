<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TindakLanjut;
use App\Models\RiwayatPelanggaran;

class WaliKelasDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $kelas = $user->kelasDiampu;

        if (!$kelas) {
            return view('dashboards.walikelas_no_data');
        }

        $siswaIds = $kelas->siswa->pluck('id');

        // FILTER WAKTU (Default: Bulan Ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // KASUS SURAT (Clean & Informatif)
        // Hanya tampilkan kasus yang:
        // 1. Siswa di kelas ini
        // 2. Melibatkan Wali Kelas
        // 3. Punya surat panggilan (trigger surat)
        $kasusBaru = TindakLanjut::with(['siswa', 'suratPanggilan'])
            ->whereIn('siswa_id', $siswaIds)
            ->forPembina('Wali Kelas')  // Filter: Hanya yang melibatkan Wali Kelas
            ->whereHas('suratPanggilan')  // Filter: Harus punya surat
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->limit(10)
            ->get();

        // DIAGRAM: Pelanggaran Populer di Kelas Ini (Filter Waktu)
        $chartPelanggaran = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereIn('riwayat_pelanggaran.siswa_id', $siswaIds)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)  // Top 10 pelanggaran
            ->get();

        $chartLabels = $chartPelanggaran->pluck('nama_pelanggaran');
        $chartData = $chartPelanggaran->pluck('total');

        // STATISTIK SINGKAT
        $totalSiswa = $siswaIds->count();
        $totalKasus = $kasusBaru->count();
        $totalPelanggaran = RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->count();
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._walikelas_stats', compact('totalSiswa', 'totalPelanggaran', 'totalKasus'))->render(),
                'table' => view('dashboards._walikelas_table', compact('kasusBaru'))->render(),
                'charts' => [
                    'pelanggaran' => [
                        'labels' => $chartLabels,
                        'data' => $chartData
                    ]
                ]
            ]);
        }

        return view('dashboards.walikelas', compact(
            'kelas', 
            'kasusBaru', 
            'chartLabels', 
            'chartData',
            'totalSiswa',
            'totalKasus',
            'totalPelanggaran',
            'startDate', 
            'endDate'
        ));
    }
}
