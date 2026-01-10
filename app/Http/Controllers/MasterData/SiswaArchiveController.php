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
        ];
        
        $deletedSiswa = $this->archiveService->getDeletedSiswa($filters);
        
        // Return partial view if requested
        if ($request->ajax() || $request->has('render_partial')) {
            return view('siswa._table_deleted', compact('deletedSiswa'));
        }

        $allKelas = $this->siswaService->getAllKelasForFilter();
        $alasanOptions = ['Alumni', 'Dikeluarkan', 'Pindah Sekolah', 'Lainnya'];
        
        return view('siswa.deleted', compact('deletedSiswa', 'allKelas', 'alasanOptions', 'filters'));
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
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'confirm_permanent' => 'required|accepted',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa untuk dihapus.',
            'confirm_permanent.accepted' => 'Anda harus confirm permanent delete.',
        ]);

        try {
            $result = $this->archiveService->bulkPermanentDelete($validated['siswa_ids']);
            
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
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
        ], [
            'siswa_ids.required' => 'Pilih minimal 1 siswa untuk di-restore.',
        ]);

        try {
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($validated['siswa_ids'] as $siswaId) {
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
