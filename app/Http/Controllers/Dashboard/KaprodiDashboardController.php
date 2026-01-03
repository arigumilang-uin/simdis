<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Kelas;

class KaprodiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $jurusan = $user->jurusanDiampu;

        if (!$jurusan) {
            return view('dashboards.kaprodi_no_data');
        }

        // FILTER (Default: Bulan Ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $kelasId = $request->input('kelas_id'); // Filter per kelas (optional)

        // DATA KELAS UNTUK DROPDOWN
        $kelasJurusan = Kelas::where('jurusan_id', $jurusan->id)->get();

        // SISWA IDS (untuk scope filtering)
        $siswaIds = DB::table('siswa')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->where('kelas.jurusan_id', $jurusan->id)
            ->when($kelasId, function($q) use ($kelasId) {
                return $q->where('kelas.id', $kelasId);
            })
            ->pluck('siswa.id');

        // KASUS SURAT (Clean & Informatif)
        // Hanya tampilkan kasus yang:
        // 1. Siswa di jurusan ini
        // 2. Melibatkan Kaprodi
        // 3. Punya surat panggilan
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->whereIn('siswa_id', $siswaIds)
            ->forPembina('Kaprodi')  // Filter: Hanya yang melibatkan Kaprodi
            ->whereHas('suratPanggilan')  // Filter: Harus punya surat
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->get();

        // DIAGRAM: Pelanggaran Populer di Jurusan (Filter Waktu & Kelas)
        $chartPelanggaran = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->where('kelas.jurusan_id', $jurusan->id)
            ->when($kelasId, function($q) use ($kelasId) {
                return $q->where('kelas.id', $kelasId);
            })
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartLabels = $chartPelanggaran->pluck('nama_pelanggaran');
        $chartData = $chartPelanggaran->pluck('total');

        // STATISTIK
        $totalSiswa = $siswaIds->count();
        $totalKasus = $kasusBaru->count();
        $totalPelanggaran = RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->count();
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._kaprodi_stats', compact('totalSiswa', 'totalPelanggaran', 'totalKasus'))->render(),
                'table' => view('dashboards._kaprodi_table', compact('kasusBaru'))->render(),
                'charts' => [
                    'pelanggaran' => [
                        'labels' => $chartLabels,
                        'data' => $chartData
                    ]
                ]
            ]);
        }

        return view('dashboards.kaprodi', compact(
            'jurusan', 
            'kasusBaru',
            'chartLabels', 
            'chartData',
            'totalSiswa',
            'totalKasus',
            'totalPelanggaran',
            'kelasJurusan', 
            'startDate', 
            'endDate'
        ));
    }
}