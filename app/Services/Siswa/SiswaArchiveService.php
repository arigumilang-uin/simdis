<?php

namespace App\Services\Siswa;

use App\Models\Siswa;
use App\Models\User;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Siswa Archive Service
 * 
 * RESPONSIBILITY: Handle soft-deleted siswa management
 * - View deleted siswa
 * - Restore deleted siswa
 * - Permanent delete
 * - Cleanup orphaned data
 * 
 * CLEAN ARCHITECTURE: Single Responsibility Principle
 * Split from SiswaService for better maintainability.
 * 
 * @package App\Services\Siswa
 */
class SiswaArchiveService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get deleted siswa with filters and pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    /**
     * Apply filters to query builder.
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['alasan_keluar'])) {
            $query->where('alasan_keluar', $filters['alasan_keluar']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->where('kelas_id', $filters['kelas_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['konsentrasi_id'])) {
            $query->whereHas('kelas', function ($q) use ($filters) {
                $q->where('konsentrasi_id', $filters['konsentrasi_id']);
            });
        }

        if (!empty($filters['tingkat'])) {
            $query->whereHas('kelas', function ($q) use ($filters) {
                $q->where('tingkat', $filters['tingkat']);
            });
        }

        // Jurusan filter (biasanya via Kaprodi scope, tapi bisa juga dropdown jika diperlukan)
        if (!empty($filters['jurusan_id'])) {
             $query->whereHas('kelas', function ($q) use ($filters) {
                $q->where('jurusan_id', $filters['jurusan_id']);
            });
        }

        return $query;
    }

    /**
     * Get deleted siswa with filters and pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getDeletedSiswa(array $filters = []): LengthAwarePaginator
    {
        $query = Siswa::onlyTrashed()
            ->with(['kelas.jurusan', 'waliMurid'])
            ->orderBy('deleted_at', 'desc');

        $this->applyFilters($query, $filters);

        return $query->paginate(20);
    }

    /**
     * Get all deleted siswa IDs based on filter criteria.
     * 
     * @param array $filters
     * @return array
     */
    public function getDeletedIdsByFilter(array $filters): array
    {
        $query = Siswa::onlyTrashed();
        $this->applyFilters($query, $filters);
        
        return $query->pluck('id')->toArray();
    }

    /**
     * Restore soft deleted siswa and all related data.
     * Also handle wali murid - reconnect or create if needed.
     *
     * @param int $siswaId
     * @return Siswa
     * @throws BusinessValidationException
     */
    public function restoreSiswa(int $siswaId): Siswa
    {
        $siswa = Siswa::onlyTrashed()->findOrFail($siswaId);

        DB::beginTransaction();
        try {
            // Clear alasan keluar
            $siswa->alasan_keluar = null;
            $siswa->keterangan_keluar = null;
            
            // Restore the siswa
            $siswa->restore();
            $siswa->save();

            // Restore related riwayat pelanggaran
            \App\Models\RiwayatPelanggaran::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->restore();

            // Restore related tindak lanjut
            \App\Models\TindakLanjut::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->restore();

            // Check and restore wali murid if needed
            if ($siswa->wali_murid_id) {
                $wali = User::withTrashed()->find($siswa->wali_murid_id);
                if ($wali && $wali->trashed()) {
                    $wali->restore();
                }
            }

            DB::commit();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($siswa)
                ->withProperties([
                    'siswa_id' => $siswaId,
                    'nama_siswa' => $siswa->nama_siswa,
                ])
                ->log('Siswa di-restore dari deleted');

            return $siswa->fresh(['kelas', 'waliMurid']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException('Gagal restore siswa: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete siswa and all related data from database.
     * This operation CANNOT be undone.
     *
     * @param int $siswaId
     * @return bool
     * @throws BusinessValidationException
     */
    public function permanentDeleteSiswa(int $siswaId): bool
    {
        $siswa = Siswa::onlyTrashed()->findOrFail($siswaId);

        DB::beginTransaction();
        try {
            // Get related data for logging
            $siswaData = [
                'nama_siswa' => $siswa->nama_siswa,
                'nisn' => $siswa->nisn,
            ];

            // Force delete related data
            \App\Models\RiwayatPelanggaran::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->forceDelete();

            \App\Models\TindakLanjut::onlyTrashed()
                ->where('siswa_id', $siswaId)
                ->forceDelete();

            // Also delete any non-trashed related data (shouldn't exist, but safety)
            \App\Models\RiwayatPelanggaran::where('siswa_id', $siswaId)->forceDelete();
            \App\Models\TindakLanjut::where('siswa_id', $siswaId)->forceDelete();

            // Force delete siswa
            $siswa->forceDelete();

            DB::commit();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties($siswaData)
                ->log('Siswa dihapus PERMANENT dari database');

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException('Gagal permanent delete siswa: ' . $e->getMessage());
        }
    }

    /**
     * Bulk permanent delete by IDs.
     *
     * @param array $siswaIds
     * @return array ['success_count' => int, 'failed_count' => int]
     */
    public function bulkPermanentDelete(array $siswaIds): array
    {
        $successCount = 0;
        $failedCount = 0;

        foreach ($siswaIds as $id) {
            try {
                $this->permanentDeleteSiswa($id);
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                \Log::warning('Failed to permanent delete siswa', [
                    'siswa_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ];
    }
}
