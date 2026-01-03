<?php

namespace App\Enums;

/**
 * Status Pembinaan Internal Enum
 * 
 * Represents the status workflow of internal coaching (pembinaan).
 */
enum StatusPembinaan: string
{
    case PERLU_PEMBINAAN = 'Perlu Pembinaan';
    case SEDANG_DIBINA = 'Sedang Dibina';
    case SELESAI = 'Selesai';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get the color badge class for UI display.
     */
    public function color(): string
    {
        return match($this) {
            self::PERLU_PEMBINAAN => 'warning',
            self::SEDANG_DIBINA => 'info',
            self::SELESAI => 'success',
        };
    }

    /**
     * Get Tailwind CSS classes for badge styling.
     */
    public function badgeClasses(): string
    {
        return match($this) {
            self::PERLU_PEMBINAAN => 'bg-amber-100 text-amber-700 border-amber-200',
            self::SEDANG_DIBINA => 'bg-blue-100 text-blue-700 border-blue-200',
            self::SELESAI => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        };
    }

    /**
     * Check if this status represents an active case.
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::PERLU_PEMBINAAN,
            self::SEDANG_DIBINA,
        ]);
    }

    /**
     * Check if this status is completed.
     */
    public function isCompleted(): bool
    {
        return $this === self::SELESAI;
    }

    /**
     * Get all status values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get active status values.
     */
    public static function activeStatuses(): array
    {
        return [
            self::PERLU_PEMBINAAN,
            self::SEDANG_DIBINA,
        ];
    }
}
