<?php

namespace App\Repositories\Contracts;

use App\Data\TindakLanjut\TindakLanjutFilterData;
use App\Enums\StatusTindakLanjut;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Tindak Lanjut Repository Interface
 * 
 * Defines methods for accessing and managing tindak lanjut (follow-up) data.
 * Extends base repository with domain-specific operations.
 */
interface TindakLanjutRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find tindak lanjut by siswa.
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findBySiswa(int $siswaId): Collection;

    /**
     * Find active tindak lanjut for a siswa.
     * Active statuses: Baru, Menunggu Persetujuan, Disetujui, Ditangani
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findActiveBySiswa(int $siswaId): Collection;

    /**
     * Find tindak lanjut by status.
     *
     * @param StatusTindakLanjut $status
     * @return Collection
     */
    public function findByStatus(StatusTindakLanjut $status): Collection;

    /**
     * Find tindak lanjut pending approval.
     *
     * @return Collection
     */
    public function findPendingApproval(): Collection;

    /**
     * Find tindak lanjut by penyetuju.
     *
     * @param int $penyetujuId
     * @return Collection
     */
    public function findByPenyetuju(int $penyetujuId): Collection;

    /**
     * Filter and paginate tindak lanjut based on filter criteria.
     *
     * @param TindakLanjutFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(TindakLanjutFilterData $filters): LengthAwarePaginator;

    /**
     * Count active cases for a siswa.
     *
     * @param int $siswaId
     * @return int
     */
    public function countActiveCasesBySiswa(int $siswaId): int;
}
