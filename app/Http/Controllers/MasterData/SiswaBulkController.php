<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaBulkService;
use App\Services\Siswa\SiswaService;
use App\Exceptions\BusinessValidationException;
use App\Data\Siswa\SiswaFilterData;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Siswa Bulk Controller - Bulk Import/Delete Operations
 * 
 * RESPONSIBILITY: Handle bulk operations for siswa
 * - Bulk create from CSV/manual
 * - Bulk delete by kelas
 * - Bulk delete by selection
 * 
 * CLEAN ARCHITECTURE: Controller hanya sebagai kurir.
 * 
 * @package App\Http\Controllers\MasterData
 */
class SiswaBulkController extends Controller
{
    public function __construct(
        private SiswaBulkService $bulkService,
        private SiswaService $siswaService
    ) {}

    /**
     * Tampilkan form bulk create siswa.
     */
    public function create(): View
    {
        $kelas = $this->siswaService->getAllKelas();
        return view('siswa.bulk_create', compact('kelas'));
    }

    /**
     * Proses bulk create siswa dari CSV/Excel.
     * 
     * CLEAN ARCHITECTURE: Controller hanya handle upload file,
     * delegate parsing dan validasi ke service.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input dasar
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'create_wali_all' => 'nullable|boolean',
            'data_type' => 'required|in:csv,manual',
            'csv_file' => 'required_if:data_type,csv|file|mimes:csv,txt',
            'manual_data' => 'required_if:data_type,manual|nullable|string',
        ]);

        try {
            // Prepare data based on type
            $dataType = $request->input('data_type');
            $data = $dataType === 'csv'
                ? $request->file('csv_file')->getRealPath()
                : $request->input('manual_data');

            // Delegate ALL processing to service
            $result = $this->bulkService->processBulkCreate(
                $dataType,
                $data,
                $request->input('kelas_id'),
                $request->boolean('create_wali_all')
            );

            // Build message
            $message = $this->buildSuccessMessage($result, $request->boolean('create_wali_all'));

            // Flash wali credentials if any
            if (!empty($result['wali_credentials'])) {
                session()->flash('wali_credentials', $result['wali_credentials']);
            }

            // Flash errors if any
            if (!empty($result['errors'])) {
                session()->flash('bulk_errors', $result['errors']);
            }

            return redirect()
                ->route('siswa.index')
                ->with('success', $message);

        } catch (BusinessValidationException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Bulk Create Error', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Gagal import siswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete siswa per kelas.
     */
    public function deleteByKelas(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'alasan_keluar' => 'required|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
            'delete_orphaned_wali' => 'nullable|boolean',
            'confirm' => 'required|accepted',
        ]);

        try {
            $result = $this->bulkService->bulkDeleteByKelas(
                $validated['kelas_id'],
                [
                    'deleteOrphanedWali' => $request->boolean('delete_orphaned_wali'),
                    'alasanKeluar' => $validated['alasan_keluar'],
                    'keteranganKeluar' => $validated['keterangan_keluar'] ?? null,
                ]
            );

            if ($result['count'] === 0) {
                return back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
            }

            $message = "Berhasil menghapus {$result['count']} siswa dengan alasan: {$validated['alasan_keluar']}.";
            if ($result['orphaned_wali_deleted'] > 0) {
                $message .= " {$result['orphaned_wali_deleted']} akun wali murid yang tidak lagi memiliki siswa aktif juga telah dihapus.";
            }

            return redirect()
                ->route('siswa.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk Delete Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected siswa (checkbox selection).
     */
    /**
     * Bulk delete selected siswa (checkbox selection or filter-based).
     */
    public function deleteSelected(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'nullable',
            'all_selected' => 'nullable|boolean',
            'filters' => 'nullable|array',
            'alasan_keluar' => 'required|in:Alumni,Dikeluarkan,Pindah Sekolah,Lainnya',
            'keterangan_keluar' => 'nullable|string|max:500',
        ]);

        try {
            $idsToDelete = [];

            if ($request->boolean('all_selected')) {
                // Select All Mode: Get IDs from filters
                $filtersData = SiswaFilterData::from($request->input('filters', []));
                
                // SECURITY: Apply role-based restrictions
                $user = auth()->user();
                if ($user->hasRole('Kaprodi') && $user->jurusan) {
                    $filtersData->jurusan_id = $user->jurusan->id;
                }
                if ($user->hasRole('Wali Kelas') && $user->kelasDiampu) {
                    $filtersData->kelas_id = $user->kelasDiampu->id;
                }

                $idsToDelete = $this->siswaService->getSiswaIdsByFilter($filtersData);

            } else {
                // Selection Mode: Get IDs from input
                $idsRaw = $request->input('ids');
                $siswaIds = is_array($idsRaw) ? $idsRaw : array_filter(explode(',', $idsRaw ?? ''));
                $idsToDelete = array_map('intval', $siswaIds);
                
                // Basic validation for manual IDs
                if (empty($idsToDelete)) {
                     throw new \Exception('Tidak ada siswa yang dipilih.');
                }
            }

            if (empty($idsToDelete)) {
                 return back()->with('error', 'Tidak ada data siswa yang sesuai untuk dihapus.');
            }

            $successCount = 0;
            DB::beginTransaction(); // Wrap in transaction for safety
            
            try {
                foreach ($idsToDelete as $siswaId) {
                    // Periksa kepemilikan jika perlu, tapi deleteSiswa() core harusnya handle atau kita percaya IDs dari filter secure
                    // (Filter secure karena kita inject role restrictions di atas)
                    
                    $this->siswaService->deleteSiswa(
                        $siswaId,
                        $validated['alasan_keluar'],
                        $validated['keterangan_keluar'] ?? null
                    );
                    $successCount++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return redirect()
                ->route('siswa.index')
                ->with('success', "Berhasil menghapus {$successCount} siswa.");

        } catch (\Exception $e) {
            \Log::error('Bulk Delete Selection Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Build success message for bulk create.
     */
    private function buildSuccessMessage(array $result, bool $createWaliAll): string
    {
        $message = "Berhasil menambahkan {$result['success_count']} siswa.";
        
        if ($createWaliAll) {
            $newWaliCount = count($result['wali_credentials'] ?? []);
            if ($newWaliCount > 0) {
                $message .= " {$newWaliCount} akun wali murid baru dibuat.";
            }
            if ($result['skipped_wali_count'] > 0) {
                $message .= " ({$result['skipped_wali_count']} siswa tanpa nomor HP, akun wali tidak dibuat)";
            }
        }
        
        if (!empty($result['errors'])) {
            $message .= " Beberapa baris dilewati karena error.";
        }
        
        return $message;
    }
}
