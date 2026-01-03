<?php

namespace App\Enums;

/**
 * Status Tindak Lanjut Enum
 * 
 * Represents the status workflow of follow-up cases (tindak_lanjut).
 * Values are mapped from the actual database ENUM column in tindak_lanjut table.
 * 
 * Backed enums are automatically stringable via ->value property
 */
enum StatusTindakLanjut: string
{
    case BARU = 'Baru';
    case MENUNGGU_PERSETUJUAN = 'Menunggu Persetujuan';
    case DISETUJUI = 'Disetujui';
    case DITOLAK = 'Ditolak';
    case DITANGANI = 'Ditangani';
    case SELESAI = 'Selesai';

    /**
     * Get the human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get the color badge class for UI display.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::BARU => 'info',
            self::MENUNGGU_PERSETUJUAN => 'warning',
            self::DISETUJUI => 'success',
            self::DITOLAK => 'danger',
            self::DITANGANI => 'primary',
            self::SELESAI => 'secondary',
        };
    }

    /**
     * Check if this status represents an active case.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::BARU,
            self::MENUNGGU_PERSETUJUAN,
            self::DISETUJUI,
            self::DITANGANI,
        ]);
    }

    /**
     * Check if this status is pending approval.
     *
     * @return bool
     */
    public function isPendingApproval(): bool
    {
        return $this === self::MENUNGGU_PERSETUJUAN;
    }

    /**
     * Check if this status is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this === self::SELESAI;
    }

    /**
     * Check if this status is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this === self::DITOLAK;
    }

    /**
     * Get all status values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status options for select dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::BARU->value => self::BARU->label(),
            self::MENUNGGU_PERSETUJUAN->value => self::MENUNGGU_PERSETUJUAN->label(),
            self::DISETUJUI->value => self::DISETUJUI->label(),
            self::DITOLAK->value => self::DITOLAK->label(),
            self::DITANGANI->value => self::DITANGANI->label(),
            self::SELESAI->value => self::SELESAI->label(),
        ];
    }

    /**
     * Get active status values (not completed or rejected).
     *
     * @return array<self>
     */
    public static function activeStatuses(): array
    {
        return [
            self::BARU,
            self::MENUNGGU_PERSETUJUAN,
            self::DISETUJUI,
            self::DITANGANI,
        ];
    }
}
