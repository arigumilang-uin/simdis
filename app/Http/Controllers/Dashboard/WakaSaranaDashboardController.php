<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TindakLanjut;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;

class WakaSaranaDashboardController extends Controller
{
    public function index(Request $request)
    {
        // FILTER (Default: Bulan Ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // KASUS SURAT (Clean & Informatif)
        // Waka Sarana: Tampilkan SEMUA kasus yang melibatkan dia
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Waka Sarana')     // Filter: Melibatkan Waka Sarana
            ->whereHas('suratPanggilan')    // Filter: Punya surat
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->get();

        // DIAGRAM: Pelanggaran Populer (SEMUA SISWA DI SEKOLAH)
        $chartPelanggaran = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartLabels = $chartPelanggaran->pluck('nama_pelanggaran');
        $chartData = $chartPelanggaran->pluck('total');

        // DIAGRAM 2: Pelanggaran Per Kelas (Top 10 Kelas Ternakal)
        $chartKelas = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->select('kelas.nama_kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas.nama_kelas')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartKelasLabels = $chartKelas->pluck('nama_kelas');
        $chartKelasData = $chartKelas->pluck('total');

        // STATISTIK
        $totalSiswa = Siswa::count();
        $totalKasus = $kasusBaru->count();
        $kasusAktif = TindakLanjut::forPembina('Waka Sarana')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->count();
        $totalPelanggaran = RiwayatPelanggaran::whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->count();

        return view('dashboards.waka_sarana', compact(
            'kasusBaru',
            'chartLabels', 
            'chartData',
            'chartKelasLabels',
            'chartKelasData',
            'totalSiswa',
            'totalKasus',
            'kasusAktif',
            'totalPelanggaran',
            'startDate', 
            'endDate'
        ));
    }
}
