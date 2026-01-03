<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Jenis Pelanggaran Repository Interface
 * 
 * Defines methods for accessing and managing jenis pelanggaran (violation types) data.
 * Extends base repository with domain-specific operations.
 */
interface JenisPelanggaranRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find jenis pelanggaran by kategori.
     *
     * @param int $kategoriId
     * @return Collection
     */
    public function findByKategori(int $kategoriId): Collection;

    /**
     * Get all active jenis pelanggaran.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get jenis pelanggaran by filter category.
     * Filter categories: atribut, absensi, kerapian, ibadah, berat
     *
     * @param string $filterCategory
     * @return Collection
     */
    public function getByFilterCategory(string $filterCategory): Collection;

    /**
     * Search jenis pelanggaran by keyword.
     * Search in nama_pelanggaran and keywords fields.
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchByKeyword(string $keyword): Collection;

    /**
     * Get jenis pelanggaran that have frequency rules.
     *
     * @return Collection
     */
    public function getWithFrequencyRules(): Collection;

    /**
     * Get jenis pelanggaran with poin within range.
     *
     * @param int $minPoin
     * @param int $maxPoin
     * @return Collection
     */
    public function getByPoinRange(int $minPoin, int $maxPoin): Collection;
}
