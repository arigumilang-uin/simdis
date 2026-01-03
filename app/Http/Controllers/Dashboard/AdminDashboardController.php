<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Traits\HasStatistics;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// Import Model
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran;

class AdminDashboardController extends Controller
{
    use HasStatistics;

    public function index(Request $request)
    {
        $user = Auth::user();

        // =============================================================
        // SCENARIO A: OPERATOR SEKOLAH
        // =============================================================
        if ($user->hasRole('Operator Sekolah')) {
            $totalUser = User::count();
            $totalSiswa = Siswa::count();
            $totalKelas = Kelas::count();
            $totalJurusan = Jurusan::count();
            $totalAturan = JenisPelanggaran::count();

            return view('dashboards.operator', compact(
                'totalUser', 'totalSiswa', 'totalKelas', 'totalJurusan', 'totalAturan'
            ));
        }

        // =============================================================
        // SCENARIO B: WAKA KESISWAAN
        // =============================================================
        
        // Tangkap Input Filter
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $jurusanId = $request->input('jurusan_id');
        $kelasId = $request->input('kelas_id');

        // 2. KASUS SURAT
        $daftarKasus = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Waka Kesiswaan')
            ->whereHas('suratPanggilan')
            ->when($kelasId || $jurusanId, function($q) use ($kelasId, $jurusanId) {
                $q->whereHas('siswa.kelas', function($sq) use ($kelasId, $jurusanId) {
                    if ($kelasId) {
                        $sq->where('id', $kelasId);
                    } elseif ($jurusanId) {
                        $sq->where('jurusan_id', $jurusanId);
                    }
                });
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->take(20)
            ->get();

        // 3. DIAGRAM: Pelanggaran Populer
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

        $statistikPelanggaran = $queryChart
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartLabels = $statistikPelanggaran->pluck('nama_pelanggaran');
        $chartData = $statistikPelanggaran->pluck('total');

        // 4. DIAGRAM 2: Kelas Ternakal
        $queryKelas = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate);

        if ($jurusanId) {
            $queryKelas->where('kelas.jurusan_id', $jurusanId);
        }

        $chartKelas = $queryKelas
            ->select('kelas.nama_kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas.nama_kelas')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartKelasLabels = $chartKelas->pluck('nama_kelas');
        $chartKelasData = $chartKelas->pluck('total');

        // 5. STATISTIK
        $totalSiswa = Siswa::count();
        $totalKasus = $daftarKasus->count(); // Untuk table count jika perlu
        $kasusAktif = TindakLanjut::forPembina('Waka Kesiswaan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->count();
        $butuhPersetujuan = TindakLanjut::forPembina('Waka Kesiswaan')
            ->where('status', 'Menunggu Persetujuan')
            ->count();
        $pelanggaranFiltered = $statistikPelanggaran->sum('total');

        // AJAX Response for Live Filtering including Charts
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._waka_stats', compact(
                    'totalSiswa', 'pelanggaranFiltered', 'kasusAktif', 'butuhPersetujuan'
                ))->render(),
                'table' => view('dashboards._waka_table', compact('daftarKasus'))->render(),
                'charts' => [
                    'pelanggaran' => [
                        'labels' => $chartLabels,
                        'data' => $chartData
                    ],
                    'kelas' => [
                        'labels' => $chartKelasLabels,
                        'data' => $chartKelasData
                    ]
                ]
            ]);
        }

        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();

        return view('dashboards.waka', compact(
            'totalSiswa', 
            'pelanggaranFiltered', 
            'kasusAktif', 
            'butuhPersetujuan',
            'daftarKasus', 
            'chartLabels', 
            'chartData',
            'chartKelasLabels',
            'chartKelasData',
            'allJurusan', 
            'allKelas', 
            'startDate', 
            'endDate'
        ));
    }
}