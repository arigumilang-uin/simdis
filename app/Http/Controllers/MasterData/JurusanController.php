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
        
        // Get user list for kaprodi dropdown - all users EXCEPT Wali Murid
        $kaprodiList = \App\Models\User::whereHas('role', function ($q) {
            $q->where('nama_role', '!=', 'Wali Murid');
        })->orderBy('username')->get();
        
        return view('jurusan.index', compact('jurusanList', 'kaprodiList'));
    }

    /**
     * Show the form for creating a new jurusan
     * DEPRECATED: Form is now inside slide-over drawer on index page
     * Redirect to index for backwards compatibility
     */
    public function create()
    {
        return redirect()->route('jurusan.index');
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
     * DEPRECATED: Form is now inside slide-over drawer on index page
     * Redirect to index for backwards compatibility
     */
    public function edit(Jurusan $jurusan)
    {
        return redirect()->route('jurusan.index')->with('edit_id', $jurusan->id);
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
     * Remove the specified jurusan (soft delete)
     * 
     * REFACTORED from 40 lines to 17 lines
     * ALL logic moved to JurusanService
     */
    public function destroy(Jurusan $jurusan)
    {
        // Soft delete with cascade to kelas and konsentrasi
        $jurusan->kelas()->delete();
        $jurusan->konsentrasi()->delete();
        $jurusan->delete();
        
        return redirect()
            ->route('jurusan.index')
            ->with('success', 'Jurusan berhasil diarsipkan beserta kelas dan konsentrasi.');
    }

    /**
     * Display archived jurusan
     */
    public function trash()
    {
        $jurusanList = Jurusan::onlyTrashed()
            ->withCount(['kelas' => fn($q) => $q->withTrashed()])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('jurusan.trash', compact('jurusanList'));
    }

    /**
     * Restore soft deleted jurusan
     */
    public function restore(int $id)
    {
        $jurusan = Jurusan::onlyTrashed()->findOrFail($id);
        
        // Restore jurusan and cascade
        $jurusan->restore();
        $jurusan->kelas()->onlyTrashed()->restore();
        $jurusan->konsentrasi()->onlyTrashed()->restore();

        return redirect()
            ->route('jurusan.trash')
            ->with('success', "Jurusan '{$jurusan->nama_jurusan}' berhasil dipulihkan.");
    }

    /**
     * Permanently delete jurusan
     */
    public function forceDelete(int $id)
    {
        $jurusan = Jurusan::onlyTrashed()->findOrFail($id);
        $nama = $jurusan->nama_jurusan;
        
        // Check if has siswa (even via trashed kelas)
        $hasSiswa = \App\Models\Siswa::withTrashed()
            ->whereIn('kelas_id', $jurusan->kelas()->withTrashed()->pluck('id'))
            ->exists();
            
        if ($hasSiswa) {
            return redirect()
                ->route('jurusan.trash')
                ->with('error', 'Tidak dapat menghapus permanen jurusan yang memiliki data siswa.');
        }
        
        // Force delete kelas first
        $jurusan->kelas()->withTrashed()->forceDelete();
        $jurusan->konsentrasi()->withTrashed()->forceDelete();
        $jurusan->forceDelete();

        return redirect()
            ->route('jurusan.trash')
            ->with('success', "Jurusan '{$nama}' berhasil dihapus secara permanen.");
    }

    /**
     * Bulk restore jurusan
     */
    public function bulkRestore(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        
        $count = 0;
        foreach ($request->ids as $id) {
            $jurusan = Jurusan::onlyTrashed()->find($id);
            if ($jurusan) {
                $jurusan->restore();
                $jurusan->kelas()->onlyTrashed()->restore();
                $jurusan->konsentrasi()->onlyTrashed()->restore();
                $count++;
            }
        }
        
        return redirect()
            ->route('jurusan.trash')
            ->with('success', "{$count} jurusan berhasil dipulihkan beserta kelas dan konsentrasi.");
    }

    /**
     * Bulk force delete jurusan
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        
        $count = 0;
        $skipped = 0;
        
        foreach ($request->ids as $id) {
            $jurusan = Jurusan::onlyTrashed()->find($id);
            if ($jurusan) {
                // Check if has siswa
                $hasSiswa = \App\Models\Siswa::withTrashed()
                    ->whereIn('kelas_id', $jurusan->kelas()->withTrashed()->pluck('id'))
                    ->exists();
                    
                if ($hasSiswa) {
                    $skipped++;
                    continue;
                }
                
                $jurusan->kelas()->withTrashed()->forceDelete();
                $jurusan->konsentrasi()->withTrashed()->forceDelete();
                $jurusan->forceDelete();
                $count++;
            }
        }
        
        $message = "{$count} jurusan berhasil dihapus permanen.";
        if ($skipped > 0) {
            $message .= " {$skipped} jurusan dilewati karena masih memiliki data siswa.";
        }
        
        return redirect()
            ->route('jurusan.trash')
            ->with($count > 0 ? 'success' : 'warning', $message);
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
