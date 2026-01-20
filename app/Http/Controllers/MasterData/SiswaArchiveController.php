<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaArchiveService;
use App\Services\Siswa\SiswaService;
use App\Exceptions\BusinessValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Siswa Archive Controller - Deleted Siswa Management
 * 
 * RESPONSIBILITY: Handle soft-deleted siswa
 * - View deleted siswa
 * - Restore siswa
 * - Permanent delete
 * 
 * CLEAN ARCHITECTURE: Controller hanya sebagai kurir.
 * 
 * @package App\Http\Controllers\MasterData
 */
class SiswaArchiveController extends Controller
{
    public function __construct(
        private SiswaArchiveService $archiveService,
        private SiswaService $siswaService
    ) {}

    /**
     * Show deleted siswa page.
     */
    public function index(Request $request): View
    {
        $filters = [
            'alasan_keluar' => $request->input('alasan_keluar'),
            'kelas_id' => $request->input('kelas_id'),
            'search' => $request->input('search'),
            'jurusan_id' => $request->input('jurusan_id'),
            'konsentrasi_id' => $request->input('konsentrasi_id'),
            'tingkat' => $request->input('tingkat'),
        ];
        
        // Apply role-based auto-filters (optional, but good for consistency)
        $user = auth()->user();
        if ($user->hasRole('Kaprodi') && $user->jurusan) {
            $filters['jurusan_id'] = $user->jurusan->id;
        }
        if ($user->hasRole('Wali Kelas') && $user->kelasDiampu) {
            $filters['kelas_id'] = $user->kelasDiampu->id;
        }

        $deletedSiswa = $this->archiveService->getDeletedSiswa($filters);
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('siswa._table_deleted', compact('deletedSiswa'));
        }

        $allKelas = $this->siswaService->getAllKelasForFilter();
        $allJurusan = $this->siswaService->getAllJurusanForFilter();
        $allKonsentrasi = $this->siswaService->getAllKonsentrasiForFilter();
        $alasanOptions = ['Alumni', 'Dikeluarkan', 'Pindah Sekolah', 'Lainnya'];
        
        return view('siswa.deleted', compact('deletedSiswa', 'allKelas', 'allJurusan', 'allKonsentrasi', 'alasanOptions', 'filters'));
    }
    
    /**
     * Restore deleted siswa.
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $this->archiveService->restoreSiswa($id);
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', 'Siswa berhasil di-restore beserta semua data terkait.');
                
        } catch (BusinessValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Restore siswa error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal restore siswa: ' . $e->getMessage());
        }
    }
    
    /**
     * Permanent delete single siswa.
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        try {
            request()->validate([
                'confirm_permanent' => 'required|accepted',
            ], [
                'confirm_permanent.accepted' => 'Anda harus confirm permanent delete.',
            ]);
            
            $this->archiveService->permanentDeleteSiswa($id);
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', 'Siswa berhasil dihapus PERMANENT dari database.');
                
        } catch (BusinessValidationException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Permanent delete error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk permanent delete.
     */
    public function bulkForceDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'siswa_ids' => 'nullable',
            'filters' => 'nullable|array',
            'all_selected' => 'nullable|boolean',
            'confirm_permanent' => 'required|accepted',
        ], [
            'confirm_permanent.accepted' => 'Anda harus confirm permanent delete.',
        ]);

        try {
            $idsToDelete = [];

            if ($request->boolean('all_selected')) {
                $filters = $request->input('filters', []);
                
                // Role Scope
                $user = auth()->user();
                if ($user->hasRole('Kaprodi') && $user->jurusan) $filters['jurusan_id'] = $user->jurusan->id;
                if ($user->hasRole('Wali Kelas') && $user->kelasDiampu) $filters['kelas_id'] = $user->kelasDiampu->id;

                $idsToDelete = $this->archiveService->getDeletedIdsByFilter($filters);
            } else {
                $idsRaw = $request->input('siswa_ids');
                $idsToDelete = is_array($idsRaw) ? array_map('intval', $idsRaw) : [];
            }

            if (empty($idsToDelete)) {
                return back()->with('error', 'Pilih minimal 1 siswa untuk dihapus.');
            }

            $result = $this->archiveService->bulkPermanentDelete($idsToDelete);
            
            $message = "Berhasil menghapus PERMANENT {$result['success_count']} siswa.";
            if ($result['failed_count'] > 0) {
                $message .= " {$result['failed_count']} siswa gagal dihapus.";
            }
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Bulk permanent delete error', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk restore deleted siswa.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'siswa_ids' => 'nullable',
            'filters' => 'nullable|array',
            'all_selected' => 'nullable|boolean',
        ]);

        try {
            $idsToRestore = [];

            if ($request->boolean('all_selected')) {
                $filters = $request->input('filters', []);
                
                // Role Scope
                $user = auth()->user();
                if ($user->hasRole('Kaprodi') && $user->jurusan) $filters['jurusan_id'] = $user->jurusan->id;
                if ($user->hasRole('Wali Kelas') && $user->kelasDiampu) $filters['kelas_id'] = $user->kelasDiampu->id;

                $idsToRestore = $this->archiveService->getDeletedIdsByFilter($filters);
            } else {
                $idsRaw = $request->input('siswa_ids');
                $idsToRestore = is_array($idsRaw) ? array_map('intval', $idsRaw) : [];
            }

            if (empty($idsToRestore)) {
                return back()->with('error', 'Pilih minimal 1 siswa untuk di-restore.');
            }

            $successCount = 0;
            $failedCount = 0;
            
            foreach ($idsToRestore as $siswaId) {
                try {
                    $this->archiveService->restoreSiswa($siswaId);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::warning('Failed to restore siswa', ['id' => $siswaId, 'error' => $e->getMessage()]);
                }
            }
            
            $message = "Berhasil restore {$successCount} siswa.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} siswa gagal di-restore.";
            }
            
            return redirect()
                ->route('siswa.deleted')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Bulk restore error', ['error' => $e->getMessage()]);
            
            return redirect()
                ->back()
                ->with('error', 'Gagal restore siswa: ' . $e->getMessage());
        }
    }
}
