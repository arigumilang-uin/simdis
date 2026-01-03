<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Traits\HasStatistics;
use App\Models\Jurusan;
use App\Models\RiwayatPelanggaran;
use App\Services\Statistics\StatisticsService;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    use HasStatistics;

    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display list of jurusan with statistics (read-only)
     * Refactored to use StatisticsService
     */
    public function index()
    {
        $jurusanList = Jurusan::withCount('kelas')
            ->with('kelas.siswa')
            ->get()
            ->map(function ($jurusan) {
                $siswaIds = $jurusan->kelas->flatMap(fn($k) => $k->siswa->pluck('id'));
                $stats = $this->statisticsService->getSiswaStatistics($siswaIds);
                
                return [
                    'jurusan' => $jurusan,
                    'total_siswa' => $stats['total_siswa'],
                    'total_kelas' => $jurusan->kelas->count(),
                    'total_pelanggaran' => $stats['total_pelanggaran'],
                    'pelanggaran_bulan_ini' => $stats['pelanggaran_bulan_ini'],
                ];
            });

        return view('data_jurusan.index', compact('jurusanList'));
    }

    /**
     * Display detail jurusan with charts and statistics
     * Refactored to use StatisticsService (DRY principle)
     */
    public function show(Jurusan $jurusan)
    {
        $jurusan->load(['kelas.siswa', 'kaprodi']);
        
        $siswaIds = $jurusan->kelas->flatMap(fn($k) => $k->siswa->pluck('id'));
        
        // Get all statistics using service (single method call!)
        $statistics = $this->statisticsService->getCompleteStatistics($siswaIds);
        
        // Add jurusan-specific data
        $statistics['stats']['total_kelas'] = $jurusan->kelas->count();
        
        return view('data_jurusan.show', [
            'jurusan' => $jurusan,
            'stats' => $statistics['stats'],
            'chartData' => $statistics['chartData'],
            'topSiswa' => $statistics['topSiswa'],
            'pelanggaranPerKategori' => $statistics['pelanggaranPerKategori'],
        ]);
    }
}


