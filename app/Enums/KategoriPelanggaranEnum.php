<?php

namespace App\Enums;

/**
 * Kategori Pelanggaran Enum
 * 
 * PURPOSE: Type-safe constants for violation categories
 * USAGE: Validation, filtering, display logic
 * 
 * NOTE: Database table `kategori_pelanggaran` is source of truth
 * This enum is for application logic, not database constraints
 */
enum KategoriPelanggaranEnum: string
{
    case RINGAN = 'ringan';
    case SEDANG = 'sedang';
    case BERAT = 'berat';
    
    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::RINGAN => 'Pelanggaran Ringan',
            self::SEDANG => 'Pelanggaran Sedang',
            self::BERAT => 'Pelanggaran Berat',
        };
    }
    
    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Get all labels
     */
    public static function labels(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
    }
    
    /**
     * Get color for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::RINGAN => 'success',  // green
            self::SEDANG => 'warning',  // yellow
            self::BERAT => 'danger',    // red
        };
    }
    
    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::RINGAN => 'fa-info-circle',
            self::SEDANG => 'fa-exclamation-triangle',
            self::BERAT => 'fa-times-circle',
        };
    }
}
