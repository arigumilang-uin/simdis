<?php

namespace App\Enums;

/**
 * Tingkat Pelanggaran Enum
 * 
 * Represents the severity levels of violations based on KategoriPelanggaran.
 * Values are mapped from the actual database values in kategori_pelanggaran table.
 * 
 * Backed enums are automatically stringable via ->value property
 */
enum TingkatPelanggaran: string
{
    case RINGAN = 'RINGAN';
    case SEDANG = 'SEDANG';
    case BERAT = 'BERAT';

    /**
     * Get the human-readable label for the tingkat.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::RINGAN => 'Ringan',
            self::SEDANG => 'Sedang',
            self::BERAT => 'Berat',
        };
    }

    /**
     * Get the suggested point range for this tingkat.
     *
     * @return array{int, int}
     */
    public function poinRange(): array
    {
        return match($this) {
            self::RINGAN => [1, 25],
            self::SEDANG => [26, 75],
            self::BERAT => [76, 100],
        };
    }

    /**
     * Get the color badge class for UI display.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::RINGAN => 'success',
            self::SEDANG => 'warning',
            self::BERAT => 'danger',
        };
    }

    /**
     * Get all tingkat values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all tingkat options for select dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::RINGAN->value => self::RINGAN->label(),
            self::SEDANG->value => self::SEDANG->label(),
            self::BERAT->value => self::BERAT->label(),
        ];
    }
}
