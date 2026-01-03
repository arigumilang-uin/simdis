<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Traits\HasStatistics;
use App\Models\Kelas;
use App\Services\Statistics\StatisticsService;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    use HasStatistics;

    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display list of kelas with statistics (read-only)
     * Refactored to use StatisticsService
     */
    public function index()
    {
        $kelasList = Kelas::with(['jurusan', 'waliKelas', 'siswa'])
            ->get()
            ->map(function ($kelas) {
                $siswaIds = $kelas->siswa->pluck('id');
                $stats = $this->statisticsService->getSiswaStatistics($siswaIds);
                
                return [
                    'kelas' => $kelas,
                    'total_siswa' => $stats['total_siswa'],
                    'total_pelanggaran' => $stats['total_pelanggaran'],
                    'pelanggaran_bulan_ini' => $stats['pelanggaran_bulan_ini'],
                ];
            });

        return view('data_kelas.index', compact('kelasList'));
    }

    /**
     * Display detail kelas with charts and statistics
     * Refactored to use StatisticsService (DRY principle)
     */
    public function show(Kelas $kelas)
    {
        $kelas->load(['jurusan', 'waliKelas', 'siswa']);
        
        $siswaIds = $kelas->siswa->pluck('id');
        
        // Get all statistics using service (single method call!)
        $statistics = $this->statisticsService->getCompleteStatistics($siswaIds);
        
        return view('data_kelas.show', [
            'kelas' => $kelas,
            'stats' => $statistics['stats'],
            'chartData' => $statistics['chartData'],
            'topSiswa' => $statistics['topSiswa'],
            'pelanggaranPerKategori' => $statistics['pelanggaranPerKategori'],
        ]);
    }
}


