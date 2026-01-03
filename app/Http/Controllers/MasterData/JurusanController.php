<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Data\MasterData\JurusanData;
use App\Http\Requests\MasterData\CreateJurusanRequest;
use App\Http\Requests\MasterData\UpdateJurusanRequest;
use App\Services\MasterData\JurusanService;
use App\Services\MasterData\JurusanStatisticsService;

/**
 * Jurusan Controller
 * 
 * REFACTORED: 2025-12-11
 * PATTERN: Clean Architecture (Thin Controller)
 * RESPONSIBILITY: HTTP Request/Response ONLY
 * 
 * ALL business logic delegated to:
 * - JurusanService (business logic)
 * - JurusanRepository (data access)
 * - CreateJurusanRequest/UpdateJurusanRequest (validation)
 * 
 * BEFORE: 355 lines with mixed concerns
 * AFTER: ~90 lines, clean separation
 */
class JurusanController extends Controller
{
    public function __construct(
        private JurusanService $jurusanService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of jurusan
     */
    public function index()
    {
        $jurusanList = $this->jurusanService->getAllJurusan();
        
        return view('jurusan.index', compact('jurusanList'));
    }

    /**
     * Show the form for creating a new jurusan
     */
    public function create()
    {
        return view('jurusan.create');
    }

    /**
     * Store a newly created jurusan
     * 
     * REFACTORED from 60 lines to 12 lines
     * ALL logic moved to JurusanService
     */
    public function store(CreateJurusanRequest $request)
    {
        $jurusanData = JurusanData::from($request->validated());
        
        $jurusan = $this->jurusanService->createJurusan($jurusanData);
        
        return redirect()
            ->route('jurusan.index')
            ->with('success', 'Jurusan berhasil dibuat.');
    }

    /**
     * Display the specified jurusan
     */
    public function show(Jurusan $jurusan)
    {
        $jurusan = $this->jurusanService->getJurusan($jurusan->id);
        
        return view('jurusan.show', compact('jurusan'));
    }

    /**
     * Show the form for editing the specified jurusan
     */
    public function edit(Jurusan $jurusan)
    {
        return view('jurusan.edit', compact('jurusan'));
    }

    /**
     * Update the specified jurusan
     * 
     * REFACTORED from 120 lines to 15 lines
     * ALL logic moved to JurusanService
     */
    public function update(UpdateJurusanRequest $request, Jurusan $jurusan)
    {
        $jurusanData = JurusanData::from($request->validated());
        
        $jurusan = $this->jurusanService->updateJurusan($jurusan, $jurusanData);
        
        return redirect()
            ->route('jurusan.show', $jurusan)
            ->with('success', 'Jurusan diperbarui. Perubahan nama kode telah dipropagasi ke kelas terkait.');
    }

    /**
     * Remove the specified jurusan
     * 
     * REFACTORED from 40 lines to 17 lines
     * ALL logic moved to JurusanService
     */
    public function destroy(Jurusan $jurusan)
    {
        $result = $this->jurusanService->deleteJurusan($jurusan);
        
        if ($result['success']) {
            return redirect()
                ->route('jurusan.index')
                ->with('success', $result['message']);
        } else {
            return redirect()
                ->route('jurusan.index')
                ->with('error', $result['message']);
        }
    }

    /**
     * Index view for monitoring (Kepala Sekolah & Waka Kesiswaan)
     * Shows jurusan with enriched statistics, not CRUD interface
     * 
     * CLEAN ARCHITECTURE: Uses Service Layer
     */
    public function indexForMonitoring()
    {
        $jurusanList = $this->jurusanService->getAllForMonitoring();
        
        return view('kepala_sekolah.jurusan.index', compact('jurusanList'));
    }
    
    /**
     * Show view for monitoring (Kepala Sekolah & Waka Kesiswaan)
     * 
     * CLEAN ARCHITECTURE: Delegates to Services
     * PERFORMANCE OPTIMIZED: Minimal model hydration
     */
    public function showForMonitoring(Jurusan $jurusan)
    {
        $statsService = app(JurusanStatisticsService::class);
        
        // Get jurusan with optimized relationships
        $jurusan = $this->jurusanService->getForMonitoringShow($jurusan->id);
        
        // Get statistics via Service
        $statistics = $statsService->getJurusanStatistics($jurusan);
        
        // Get pelanggaran count per kelas (batch)
        $pelanggaranPerKelas = $statsService->getPelanggaranCountPerKelas(
            $jurusan->kelas->pluck('id')
        );
        
        // Attach pelanggaran count to kelas for view
        foreach ($jurusan->kelas as $kelas) {
            $kelas->pelanggaran_count = $pelanggaranPerKelas[$kelas->id] ?? 0;
        }
        
        // Extract statistics
        $totalSiswa = $statistics['total_siswa'];
        $totalPelanggaran = $statistics['total_pelanggaran'];
        $siswaPerluPembinaan = $statistics['siswa_perlu_pembinaan'];
        
        return view('kepala_sekolah.jurusan.show', compact(
            'jurusan',
            'totalSiswa',
            'totalPelanggaran',
            'siswaPerluPembinaan'
        ));
    }
}
