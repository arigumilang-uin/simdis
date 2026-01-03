<?php

namespace App\Data\TindakLanjut;

use Spatie\LaravelData\Data;
use App\Enums\StatusTindakLanjut;

/**
 * Tindak Lanjut Filter Data Transfer Object
 * 
 * Represents filter/search criteria for querying tindak lanjut records.
 * Used to pass filter parameters from controllers to repositories.
 */
class TindakLanjutFilterData extends Data
{
    public function __construct(
        public ?int $siswa_id = null,
        public ?int $kelas_id = null,
        public ?int $jurusan_id = null,
        public ?StatusTindakLanjut $status = null,
        public ?int $penyetuju_user_id = null,
        public ?string $tanggal_dari = null,
        public ?string $tanggal_sampai = null,
        public bool $pending_approval_only = false,
        public bool $active_only = false,
        public bool $completed_only = false,
        public int $perPage = 20,
        public string $sortBy = 'created_at',
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
            || $this->kelas_id !== null
            || $this->jurusan_id !== null
            || $this->status !== null
            || $this->penyetuju_user_id !== null
            || $this->tanggal_dari !== null
            || $this->tanggal_sampai !== null
            || $this->pending_approval_only
            || $this->active_only
            || $this->completed_only;
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
