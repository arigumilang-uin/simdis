<?php

namespace App\Repositories\Contracts;

use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Siswa Repository Interface
 * 
 * Defines methods for accessing and managing siswa (student) data.
 * Extends base repository with domain-specific operations.
 */
interface SiswaRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a siswa by NISN.
     *
     * @param string $nisn
     * @return SiswaData|null
     */
    public function findByNisn(string $nisn): ?SiswaData;

    /**
     * Find all siswa in a specific kelas.
     *
     * @param int $kelasId
     * @return Collection
     */
    public function findByKelas(int $kelasId): Collection;

    /**
     * Find all siswa in a specific jurusan.
     *
     * @param int $jurusanId
     * @return Collection
     */
    public function findByJurusan(int $jurusanId): Collection;

    /**
     * Find all siswa by wali murid.
     *
     * @param int $waliMuridId
     * @return Collection
     */
    public function findByWaliMurid(int $waliMuridId): Collection;

    /**
     * Search siswa by name or NISN.
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchByName(string $keyword): Collection;

    /**
     * Get siswa with violation history.
     *
     * @return Collection
     */
    public function withViolations(): Collection;

    /**
     * Get siswa with active tindak lanjut cases.
     *
     * @return Collection
     */
    public function withActiveCases(): Collection;

    /**
     * Filter and paginate siswa based on filter criteria.
     *
     * @param SiswaFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(SiswaFilterData $filters): LengthAwarePaginator;

    /**
     * Check if NISN already exists.
     *
     * @param string $nisn
     * @param int|null $excludeSiswaId
     * @return bool
     */
    public function nisnExists(string $nisn, ?int $excludeSiswaId = null): bool;

    /**
     * Get total count of siswa by kelas.
     *
     * @param int $kelasId
     * @return int
     */
    public function countByKelas(int $kelasId): int;

    /**
     * Get total count of siswa by jurusan.
     *
     * @param int $jurusanId
     * @return int
     */
    public function countByJurusan(int $jurusanId): int;

    /**
     * Get all siswa IDs based on filter criteria.
     *
     * @param SiswaFilterData $filters
     * @return array
     */
    public function getIdsByFilter(SiswaFilterData $filters): array;
}
