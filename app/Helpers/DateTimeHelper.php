<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * DateTimeHelper
 * 
 * Centralized datetime formatting untuk konsistensi timezone display
 * di seluruh aplikasi.
 * 
 * Usage:
 * - formatDateTime($timestamp) -> "07 Des 2025 14:30:45 WIB"
 * - formatDate($timestamp) -> "07 Des 2025"
 * - formatTime($timestamp) -> "14:30:45 WIB"
 * - formatRelative($timestamp) -> "5 minutes ago"
 */
class DateTimeHelper
{
    /**
     * Default timezone untuk aplikasi
     */
    const TIMEZONE = 'Asia/Jakarta';
    const TIMEZONE_LABEL = 'WIB';

    /**
     * Format full datetime dengan timezone
     * 
     * @param mixed $timestamp
     * @param string $format
     * @return string
     */
    public static function formatDateTime($timestamp, string $format = 'd M Y H:i:s'): string
    {
        if (!$timestamp) {
            return '-';
        }

        return self::toLocalTimezone($timestamp)->format($format) . ' ' . self::TIMEZONE_LABEL;
    }

    /**
     * Format date only
     * 
     * @param mixed $timestamp
     * @return string
     */
    public static function formatDate($timestamp): string
    {
        if (!$timestamp) {
            return '-';
        }

        return self::toLocalTimezone($timestamp)->format('d M Y');
    }

    /**
     * Format time only dengan timezone label
     * 
     * @param mixed $timestamp
     * @return string
     */
    public static function formatTime($timestamp): string
    {
        if (!$timestamp) {
            return '-';
        }

        return self::toLocalTimezone($timestamp)->format('H:i:s') . ' ' . self::TIMEZONE_LABEL;
    }

    /**
     * Format relative time (e.g., "5 minutes ago")
     * 
     * @param mixed $timestamp
     * @return string
     */
    public static function formatRelative($timestamp): string
    {
        if (!$timestamp) {
            return 'Belum pernah';
        }

        return self::toLocalTimezone($timestamp)->diffForHumans();
    }

    /**
     * Format untuk export CSV/Excel
     * 
     * @param mixed $timestamp
     * @return string
     */
    public static function formatForExport($timestamp): string
    {
        if (!$timestamp) {
            return '-';
        }

        return self::toLocalTimezone($timestamp)->format('Y-m-d H:i:s') . ' ' . self::TIMEZONE_LABEL;
    }

    /**
     * Convert timestamp ke local timezone
     * 
     * @param mixed $timestamp
     * @return Carbon
     */
    private static function toLocalTimezone($timestamp): Carbon
    {
        if ($timestamp instanceof Carbon) {
            return $timestamp->timezone(self::TIMEZONE);
        }

        return Carbon::parse($timestamp)->timezone(self::TIMEZONE);
    }
}
