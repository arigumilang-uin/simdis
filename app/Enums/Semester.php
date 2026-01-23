<?php

namespace App\Enums;

/**
 * Semester Enum
 * 
 * Semester akademik: Ganjil (1) atau Genap (2)
 */
enum Semester: string
{
    case Ganjil = 'Ganjil';
    case Genap = 'Genap';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::Ganjil => 'Semester Ganjil',
            self::Genap => 'Semester Genap',
        };
    }

    /**
     * Get short label
     */
    public function shortLabel(): string
    {
        return match($this) {
            self::Ganjil => 'Ganjil',
            self::Genap => 'Genap',
        };
    }

    /**
     * Get semester number (1 or 2)
     */
    public function number(): int
    {
        return match($this) {
            self::Ganjil => 1,
            self::Genap => 2,
        };
    }

    /**
     * Get current semester from active PeriodeSemester
     * Falls back to date-based calculation if no active period
     */
    public static function current(): self
    {
        $periode = current_periode();
        if ($periode) {
            return $periode->semester;
        }
        
        // Fallback: calculate from current month
        $month = (int) date('n');
        return ($month >= 7) ? self::Ganjil : self::Genap;
    }

    /**
     * Get current academic year from active PeriodeSemester
     * Falls back to date-based calculation if no active period
     */
    public static function currentTahunAjaran(): string
    {
        $periode = current_periode();
        if ($periode) {
            return $periode->tahun_ajaran;
        }
        
        // Fallback: calculate from current date
        $year = (int) date('Y');
        $month = (int) date('n');
        
        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    /**
     * Get current period start and end dates from active PeriodeSemester
     * Returns array with 'start' and 'end' keys
     */
    public static function currentPeriodDates(): array
    {
        $periode = current_periode();
        if ($periode && $periode->tanggal_mulai && $periode->tanggal_selesai) {
            return [
                'start' => $periode->tanggal_mulai->format('Y-m-d'),
                'end' => $periode->tanggal_selesai->format('Y-m-d'),
            ];
        }
        
        // Fallback: use current academic semester dates
        $year = (int) date('Y');
        $month = (int) date('n');
        
        if ($month >= 7) {
            // Semester Ganjil (July - December)
            return [
                'start' => $year . '-07-01',
                'end' => $year . '-12-31',
            ];
        } else {
            // Semester Genap (January - June)
            return [
                'start' => $year . '-01-01',
                'end' => $year . '-06-30',
            ];
        }
    }

    /**
     * Get all values for dropdown/select
     */
    public static function forSelect(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
