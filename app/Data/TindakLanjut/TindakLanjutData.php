<?php

namespace App\Data\TindakLanjut;

use Spatie\LaravelData\Data;
use App\Enums\StatusTindakLanjut;

/**
 * Tindak Lanjut Data Transfer Object
 * 
 * Represents follow-up case data with type-safe properties.
 * Column names are mapped from the real database schema (tindak_lanjut table).
 */
class TindakLanjutData extends Data
{
    public function __construct(
        public ?int $id,
        public int $siswa_id,
        public string $pemicu,
        public string $sanksi_deskripsi,
        public ?string $denda_deskripsi = null,
        public StatusTindakLanjut $status = StatusTindakLanjut::BARU,
        public ?string $tanggal_tindak_lanjut = null,
        public ?int $penyetuju_user_id = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        
        // Relationships (optional, for responses)
        public ?object $siswa = null,
        public ?object $penyetuju = null,
        public ?object $suratPanggilan = null,
    ) {}

    /**
     * Validation rules for creating/updating tindak lanjut.
     *
     * @return array<string, array<string>>
     */
    public static function rules(): array
    {
        return [
            'siswa_id' => ['required', 'integer', 'exists:siswa,id'],
            'pemicu' => ['required', 'string', 'max:255'],
            'sanksi_deskripsi' => ['required', 'string', 'max:255'],
            'denda_deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:' . implode(',', StatusTindakLanjut::values())],
            'tanggal_tindak_lanjut' => ['nullable', 'date'],
            'penyetuju_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get rules for approving/rejecting a tindak lanjut.
     *
     * @return array<string, array<string>>
     */
    public static function approvalRules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Disetujui,Ditolak'],
            'penyetuju_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get rules for completing a tindak lanjut.
     *
     * @return array<string, array<string>>
     */
    public static function completionRules(): array
    {
        return [
            'status' => ['required', 'string', 'in:Selesai'],
            'tanggal_tindak_lanjut' => ['required', 'date'],
        ];
    }

    /**
     * Check if this tindak lanjut is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if this tindak lanjut is pending approval.
     *
     * @return bool
     */
    public function isPendingApproval(): bool
    {
        return $this->status->isPendingApproval();
    }

    /**
     * Check if this tindak lanjut is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }
}
