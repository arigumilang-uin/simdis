<?php

namespace App\Repositories;

use App\Models\TindakLanjut;
use App\Repositories\Contracts\TindakLanjutRepositoryInterface;
use App\Data\TindakLanjut\TindakLanjutFilterData;
use App\Enums\StatusTindakLanjut;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Tindak Lanjut Repository Implementation
 * 
 * Handles all data access operations for TindakLanjut entity.
 * Implements TindakLanjutRepositoryInterface and extends BaseRepository.
 */
class TindakLanjutRepository extends BaseRepository implements TindakLanjutRepositoryInterface
{
    /**
     * TindakLanjutRepository constructor.
     *
     * @param TindakLanjut $model
     */
    public function __construct(TindakLanjut $model)
    {
        parent::__construct($model);
    }

    /**
     * Find tindak lanjut by siswa.
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findBySiswa(int $siswaId): Collection
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->with(['siswa.kelas', 'penyetuju', 'suratPanggilan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find active tindak lanjut for a siswa.
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findActiveBySiswa(int $siswaId): Collection
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->whereIn('status', StatusTindakLanjut::activeStatuses())
            ->with(['siswa.kelas', 'penyetuju', 'suratPanggilan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find tindak lanjut by status.
     *
     * @param StatusTindakLanjut $status
     * @return Collection
     */
    public function findByStatus(StatusTindakLanjut $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->with(['siswa.kelas', 'penyetuju', 'suratPanggilan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find tindak lanjut pending approval.
     *
     * @return Collection
     */
    public function findPendingApproval(): Collection
    {
        return $this->findByStatus(StatusTindakLanjut::MENUNGGU_PERSETUJUAN);
    }

    /**
     * Find tindak lanjut by penyetuju.
     *
     * @param int $penyetujuId
     * @return Collection
     */
    public function findByPenyetuju(int $penyetujuId): Collection
    {
        return $this->model
            ->where('penyetuju_user_id', $penyetujuId)
            ->with(['siswa.kelas', 'penyetuju', 'suratPanggilan'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Filter and paginate tindak lanjut based on filter criteria.
     *
     * @param TindakLanjutFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(TindakLanjutFilterData $filters): LengthAwarePaginator
    {
        // Start building query dengan eager loading
        $query = $this->model
            ->newQuery()
            ->with(['siswa.kelas.jurusan', 'penyetuju', 'suratPanggilan']);

        // Apply filter by siswa
        if ($filters->siswa_id) {
            $query->where('siswa_id', $filters->siswa_id);
        }

        // Apply filter by kelas (via siswa relationship)
        if ($filters->kelas_id) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters->kelas_id);
            });
        }

        // Apply filter by jurusan
        if ($filters->jurusan_id) {
            $query->whereHas('siswa.kelas', function ($q) use ($filters) {
                $q->where('jurusan_id', $filters->jurusan_id);
            });
        }

        // Apply filter by status enum
        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        // Apply filter by penyetuju
        if ($filters->penyetuju_user_id) {
            $query->where('penyetuju_user_id', $filters->penyetuju_user_id);
        }

        // Apply date range filter
        if ($filters->tanggal_dari) {
            $query->whereDate('tanggal_tindak_lanjut', '>=', $filters->tanggal_dari);
        }

        if ($filters->tanggal_sampai) {
            $query->whereDate('tanggal_tindak_lanjut', '<=', $filters->tanggal_sampai);
        }

        // Apply convenience filters
        if ($filters->pending_approval_only) {
            $query->where('status', StatusTindakLanjut::MENUNGGU_PERSETUJUAN);
        }

        if ($filters->active_only) {
            $query->whereIn('status', StatusTindakLanjut::activeStatuses());
        }

        if ($filters->completed_only) {
            $query->where('status', StatusTindakLanjut::SELESAI);
        }

        // Apply sorting
        $sortBy = $filters->sortBy ?: 'created_at';
        $sortDirection = $filters->getSortDirection();
        $query->orderBy($sortBy, $sortDirection);

        // Return paginated results
        return $query->paginate($filters->perPage);
    }

    /**
     * Count active cases for a siswa.
     *
     * @param int $siswaId
     * @return int
     */
    public function countActiveCasesBySiswa(int $siswaId): int
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->whereIn('status', StatusTindakLanjut::activeStatuses())
            ->count();
    }
}
