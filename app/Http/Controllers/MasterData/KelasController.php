<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Data\MasterData\KelasData;
use App\Http\Requests\MasterData\CreateKelasRequest;
use App\Http\Requests\MasterData\UpdateKelasRequest;
use App\Services\MasterData\KelasService;
use App\Services\MasterData\KelasStatisticsService;

/**
 * Kelas Controller
 * 
 * REFACTORED: 2025-12-11
 * PATTERN: Clean Architecture (Thin Controller)
 * RESPONSIBILITY: HTTP Request/Response ONLY
 * 
 * ALL business logic delegated to:
 * - KelasService (business logic)
 * - KelasRepository (data access)
 * - CreateKelasRequest/UpdateKelasRequest (validation)
 * 
 * BEFORE: 256 lines with mixed concerns
 * AFTER: ~110 lines, clean separation
 */
class KelasController extends Controller
{
    public function __construct(
        private KelasService $kelasService
    ) {}

    /**
     * Display a listing of kelas
     */
    public function index()
    {
        $kelasList = $this->kelasService->getAllKelas();
        
        return view('kelas.index', compact('kelasList'));
    }

    /**
     * Show the form for creating a new kelas
     */
    public function create()
    {
        $data = $this->kelasService->getDataForCreate();
        
        return view('kelas.create', $data);
    }

    /**
     * Store a newly created kelas
     * 
     * REFACTORED from 83 lines to 15 lines
     * ALL logic moved to KelasService
     */
    public function store(CreateKelasRequest $request)
    {
        $kelasData = KelasData::from($request->validated());
        
        $result = $this->kelasService->createKelas($kelasData);
        
        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas berhasil dibuat: ' . $result['nama_kelas']);
    }

    /**
     * Show the form for editing the specified kelas
     */
    public function edit(Kelas $kelas)
    {
        $data = $this->kelasService->getDataForEdit($kelas);
        
        return view('kelas.edit', $data);
    }

    /**
     * Display the specified kelas
     */
    public function show(Kelas $kelas)
    {
        $kelas = $this->kelasService->getKelas($kelas->id);
        
        return view('kelas.show', compact('kelas'));
    }

    /**
     * Update the specified kelas
     * 
     * REFACTORED from 53 lines to 13 lines
     * ALL logic moved to KelasService
     */
    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $kelasData = KelasData::from($request->validated());
        
        $this->kelasService->updateKelas($kelas, $kelasData);
        
        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified kelas
     * 
     * REFACTORED: Simple delegation
     */
    public function destroy(Kelas $kelas)
    {
        $this->kelasService->deleteKelas($kelas);
        
        return redirect()
            ->route('kelas.index')
            ->with('success', 'Kelas dihapus.');
    }
    
    /**
     * Index view for monitoring (Kepala Sekolah & Waka Kesiswaan)
     * 
     * CLEAN ARCHITECTURE: Uses Service Layer
     */
    public function indexForMonitoring()
    {
        $kelasList = $this->kelasService->getAllForMonitoring();
        
        return view('kepala_sekolah.kelas.index', compact('kelasList'));
    }
    
    /**
     * Show view for monitoring (Kepala Sekolah & Waka Kesiswaan)
     * 
     * CLEAN ARCHITECTURE: Delegates to Services
     * PERFORMANCE OPTIMIZED: Minimal model hydration
     */
    public function showForMonitoring(Kelas $kelas)
    {
        $statsService = app(KelasStatisticsService::class);
        
        // Get kelas with optimized relationships
        $kelas->load(['jurusan', 'waliKelas']);
        
        // Get statistics via Service
        $statistics = $statsService->getKelasStatistics($kelas);
        
        // Get siswa list with points (for display)
        $siswaList = $statsService->getSiswaWithPoints($kelas->id);
        
        // Extract statistics
        $totalSiswa = $statistics['total_siswa'];
        $totalPelanggaran = $statistics['total_pelanggaran'];
        $siswaPerluPembinaan = $statistics['siswa_perlu_pembinaan'];
        $avgPoin = $statistics['avg_poin'];
        
        return view('kepala_sekolah.kelas.show', compact(
            'kelas',
            'totalSiswa',
            'totalPelanggaran',
            'siswaPerluPembinaan',
            'avgPoin',
            'siswaList'
        ));
    }
}
