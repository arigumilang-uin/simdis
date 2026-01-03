<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TindakLanjut;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Kelas;

class KepsekDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. FILTER INPUTS
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $jurusanId = $request->input('jurusan_id');
        $kelasId = $request->input('kelas_id');

        // 2. KASUS (Monitoring)
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Kepala Sekolah')
            ->whereHas('suratPanggilan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->when($kelasId || $jurusanId, function($q) use ($kelasId, $jurusanId) {
                $q->whereHas('siswa.kelas', function($sq) use ($kelasId, $jurusanId) {
                    if ($kelasId) $sq->where('id', $kelasId);
                    elseif ($jurusanId) $sq->where('jurusan_id', $jurusanId);
                });
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->limit(10)
            ->get();

        // 3. KASUS MENUNGGU (Action Required)
        $kasusMenunggu = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Kepala Sekolah')
            ->where('status', 'Menunggu Persetujuan')
            ->when($kelasId || $jurusanId, function($q) use ($kelasId, $jurusanId) {
                $q->whereHas('siswa.kelas', function($sq) use ($kelasId, $jurusanId) {
                    if ($kelasId) $sq->where('id', $kelasId);
                    elseif ($jurusanId) $sq->where('jurusan_id', $jurusanId);
                });
            })
            ->latest()
            ->limit(10)
            ->get();
            
        // 4. CHART PELANGGARAN POPULER
        $queryChart = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate);
            
         if ($kelasId) {
            $queryChart->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $queryChart->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }
        
        $chartPelanggaran = $queryChart
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
            
        $chartLabels = $chartPelanggaran->pluck('nama_pelanggaran');
        $chartData = $chartPelanggaran->pluck('total');

        // 5. CHART JURUSAN
        $queryJurusan = DB::table('riwayat_pelanggaran')
             ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
             ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
             ->join('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
             ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
             ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate);
             
         if ($kelasId) {
             $queryJurusan->where('siswa.kelas_id', $kelasId);
         } elseif ($jurusanId) {
             $queryJurusan->where('kelas.jurusan_id', $jurusanId);
         }
         
        $chartJurusan = $queryJurusan
             ->select('jurusan.nama_jurusan', DB::raw('count(*) as total'))
             ->groupBy('jurusan.nama_jurusan')
             ->orderByDesc('total')
             ->get();

        $chartJurusanLabels = $chartJurusan->pluck('nama_jurusan');
        $chartJurusanData = $chartJurusan->pluck('total');
        
        // 6. TREND CHART (Monthly)
        $queryTrend = DB::table('riwayat_pelanggaran')
            ->select(DB::raw("DATE_FORMAT(tanggal_kejadian, '%Y-%m') as bulan"), DB::raw('count(*) as total'))
            ->where('tanggal_kejadian', '>=', now()->subMonths(6));
        
        if ($kelasId) {
             $queryTrend->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                 ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $queryTrend->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }

        $chartTrend = $queryTrend
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();
            
        $chartTrendLabels = $chartTrend->pluck('bulan');
        $chartTrendData = $chartTrend->pluck('total');

        // STATS
        $totalSiswa = Siswa::count(); 
        $totalKasus = $kasusBaru->count();
        $totalKasusMenunggu = $kasusMenunggu->count();
        $totalPelanggaran = $chartPelanggaran->sum('total');

        // AJAX RESPONSE
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._kepsek_stats', compact('totalSiswa', 'totalPelanggaran', 'totalKasus', 'totalKasusMenunggu'))->render(),
                'table' => view('dashboards._kepsek_table', compact('kasusMenunggu'))->render(),
                'charts' => [
                    'trend' => ['labels' => $chartTrendLabels, 'data' => $chartTrendData],
                    'pelanggaran' => ['labels' => $chartLabels, 'data' => $chartData],
                    'jurusan' => ['labels' => $chartJurusanLabels, 'data' => $chartJurusanData],
                ]
            ]);
        }
        
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();

        return view('dashboards.kepsek', compact(
            'kasusBaru', 'kasusMenunggu', 
            'chartLabels', 'chartData', 
            'chartJurusanLabels', 'chartJurusanData',
            'chartTrendLabels', 'chartTrendData',
            'totalSiswa', 'totalKasus', 'totalKasusMenunggu', 'totalPelanggaran',
            'startDate', 'endDate', 'allJurusan', 'allKelas'
        ));
    }
}
