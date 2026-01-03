<?php

namespace App\Data\Pelanggaran;

use Spatie\LaravelData\Data;
use App\Enums\TingkatPelanggaran;

/**
 * Riwayat Pelanggaran Filter Data Transfer Object
 * 
 * Represents filter/search criteria for querying violation history records.
 * Used to pass filter parameters from controllers to repositories.
 */
class RiwayatPelanggaranFilterData extends Data
{
    public function __construct(
        public ?int $siswa_id = null,
        public ?int $jenis_pelanggaran_id = null,
        public ?int $guru_pencatat_user_id = null,
        public ?int $kelas_id = null,
        public ?int $jurusan_id = null,
        public ?TingkatPelanggaran $tingkat = null,
        public ?string $tanggal_dari = null,
        public ?string $tanggal_sampai = null,
        public ?string $search = null,
        public int $perPage = 20,
        public string $sortBy = 'tanggal_kejadian',
        public string $sortDirection = 'desc',
    ) {}

    /**
     * Check if any filters are applied.
     *
     * @return bool
     */
    public function hasFilters(): bool
    {
        return $this->siswa_id !== null
            || $this->jenis_pelanggaran_id !== null
            || $this->guru_pencatat_user_id !== null
            || $this->kelas_id !== null
            || $this->jurusan_id !== null
            || $this->tingkat !== null
            || $this->tanggal_dari !== null
            || $this->tanggal_sampai !== null
            || $this->search !== null;
    }

    /**
     * Check if date range filter is applied.
     *
     * @return bool
     */
    public function hasDateRange(): bool
    {
        return $this->tanggal_dari !== null || $this->tanggal_sampai !== null;
    }

    /**
     * Get the sort direction (ensure it's valid).
     *
     * @return string
     */
    public function getSortDirection(): string
    {
        return in_array(strtolower($this->sortDirection), ['asc', 'desc']) 
            ? strtolower($this->sortDirection) 
            : 'desc';
    }
}
