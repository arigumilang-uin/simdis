<?php

namespace App\Repositories\Contracts;

use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Riwayat Pelanggaran Repository Interface
 * 
 * Defines methods for accessing and managing riwayat pelanggaran (violation history) data.
 * Extends base repository with domain-specific operations.
 */
interface RiwayatPelanggaranRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find riwayat pelanggaran by siswa.
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findBySiswa(int $siswaId): Collection;

    /**
     * Find riwayat pelanggaran by jenis pelanggaran.
     *
     * @param int $jenisPelanggaranId
     * @return Collection
     */
    public function findByJenisPelanggaran(int $jenisPelanggaranId): Collection;

    /**
     * Find riwayat pelanggaran by guru pencatat.
     *
     * @param int $guruId
     * @return Collection
     */
    public function findByGuruPencatat(int $guruId): Collection;

    /**
     * Find riwayat pelanggaran within date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function findByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Count riwayat pelanggaran by siswa and jenis pelanggaran.
     * Useful for frequency rules calculation.
     *
     * @param int $siswaId
     * @param int $jenisPelanggaranId
     * @return int
     */
    public function countBySiswaAndJenis(int $siswaId, int $jenisPelanggaranId): int;

    /**
     * Get total poin pelanggaran for a siswa.
     * 
     * Uses JOIN + DB aggregation for optimal performance.
     *
     * @param int $siswaId
     * @return int
     */
    public function getTotalPoinBySiswa(int $siswaId): int;

    /**
     * Filter and paginate riwayat pelanggaran based on filter criteria.
     * 
     * CRITICAL: Accepts RiwayatPelanggaranFilterData (DTO), NOT Request object.
     *
     * @param RiwayatPelanggaranFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator;

    /**
     * Get recent violations for a siswa.
     *
     * @param int $siswaId
     * @param int $limit
     * @return Collection
     */
    public function getRecentBySiswa(int $siswaId, int $limit = 10): Collection;
}
