<?php

namespace App\Data\Siswa;

use Spatie\LaravelData\Data;

/**
 * Siswa Filter Data Transfer Object
 * 
 * Represents filter/search criteria for querying siswa records.
 * Used to pass filter parameters from controllers to repositories.
 */
class SiswaFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?int $kelas_id = null,
        public ?int $jurusan_id = null,
        public ?int $wali_murid_user_id = null,
        public bool $with_violations = false,
        public bool $with_active_cases = false,
        public int $perPage = 20,
        public string $sortBy = 'nama_siswa',
        public string $sortDirection = 'asc',
    ) {}

    /**
     * Check if any filters are applied.
     *
     * @return bool
     */
    public function hasFilters(): bool
    {
        return $this->search !== null
            || $this->kelas_id !== null
            || $this->jurusan_id !== null
            || $this->wali_murid_user_id !== null
            || $this->with_violations
            || $this->with_active_cases;
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
            : 'asc';
    }
}
