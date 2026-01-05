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
        $chartMode = $request->input('chart_mode', 'trend'); // trend, jenis, jurusan, kelas

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

        // 4. UNIFIED CHART DATA - Based on chart_mode
        $chartData = $this->getChartData($chartMode, $startDate, $endDate, $jurusanId, $kelasId);

        // STATS
        $totalSiswa = Siswa::count(); 
        $totalKasus = $kasusBaru->count();
        $totalKasusMenunggu = $kasusMenunggu->count();
        
        // Count pelanggaran for stats
        $totalPelanggaran = DB::table('riwayat_pelanggaran')
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->count();

        // AJAX RESPONSE
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._kepsek_stats', compact('totalSiswa', 'totalPelanggaran', 'totalKasus', 'totalKasusMenunggu'))->render(),
                'table' => view('dashboards._kepsek_table', compact('kasusMenunggu'))->render(),
                'charts' => [
                    'mainChart' => $chartData,
                ]
            ]);
        }
        
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();

        return view('dashboards.kepsek', compact(
            'kasusBaru', 'kasusMenunggu', 
            'chartData',
            'totalSiswa', 'totalKasus', 'totalKasusMenunggu', 'totalPelanggaran',
            'startDate', 'endDate', 'allJurusan', 'allKelas', 'chartMode'
        ));
    }

    /**
     * Get chart data based on mode
     */
    private function getChartData(string $mode, string $startDate, string $endDate, ?string $jurusanId, ?string $kelasId): array
    {
        $baseQuery = DB::table('riwayat_pelanggaran')
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate);

        // Apply class/jurusan filter
        if ($kelasId) {
            $baseQuery->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $baseQuery->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }

        switch ($mode) {
            case 'jenis':
                return $this->getChartByJenis($baseQuery->clone());
            
            case 'jurusan':
                return $this->getChartByJurusan($startDate, $endDate, $kelasId);
            
            case 'kelas':
                return $this->getChartByKelas($startDate, $endDate, $jurusanId);
            
            case 'trend':
            default:
                return $this->getChartTrend($kelasId, $jurusanId);
        }
    }

    private function getChartTrend(?string $kelasId, ?string $jurusanId): array
    {
        $query = DB::table('riwayat_pelanggaran')
            ->select(DB::raw("DATE_FORMAT(tanggal_kejadian, '%Y-%m') as label"), DB::raw('count(*) as value'))
            ->where('tanggal_kejadian', '>=', now()->subMonths(6));

        if ($kelasId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }

        $data = $query->groupBy('label')->orderBy('label', 'asc')->get();

        return [
            'type' => 'line',
            'title' => 'Tren Pelanggaran',
            'subtitle' => '6 Bulan Terakhir',
            'labels' => $data->pluck('label')->toArray(),
            'data' => $data->pluck('value')->toArray(),
        ];
    }

    private function getChartByJenis($query): array
    {
        $data = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->select('jenis_pelanggaran.nama_pelanggaran as label', DB::raw('count(*) as value'))
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', now()->subMonths(6))
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        return [
            'type' => 'doughnut',
            'title' => 'Berdasarkan Jenis',
            'subtitle' => 'Top 10 Pelanggaran',
            'labels' => $data->pluck('label')->toArray(),
            'data' => $data->pluck('value')->toArray(),
        ];
    }

    private function getChartByJurusan(string $startDate, string $endDate, ?string $kelasId): array
    {
        $query = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate);

        if ($kelasId) {
            $query->where('siswa.kelas_id', $kelasId);
        }

        $data = $query
            ->select('jurusan.nama_jurusan as label', DB::raw('count(*) as value'))
            ->groupBy('label')
            ->orderByDesc('value')
            ->get();

        return [
            'type' => 'bar',
            'title' => 'Berdasarkan Jurusan',
            'subtitle' => 'Sebaran Per Jurusan',
            'labels' => $data->pluck('label')->toArray(),
            'data' => $data->pluck('value')->toArray(),
        ];
    }

    private function getChartByKelas(string $startDate, string $endDate, ?string $jurusanId): array
    {
        $query = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate);

        if ($jurusanId) {
            $query->where('kelas.jurusan_id', $jurusanId);
        }

        $data = $query
            ->select('kelas.nama_kelas as label', DB::raw('count(*) as value'))
            ->groupBy('label')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        return [
            'type' => 'bar',
            'title' => 'Berdasarkan Kelas',
            'subtitle' => 'Top 10 Kelas',
            'labels' => $data->pluck('label')->toArray(),
            'data' => $data->pluck('value')->toArray(),
            'options' => [
                'indexAxis' => 'y'
            ]
        ];
    }
}
