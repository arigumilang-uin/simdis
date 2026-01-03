<?php

/**
 * Global Helper Functions
 * 
 * File ini berisi helper functions yang bisa digunakan di seluruh aplikasi
 * tanpa perlu import namespace.
 */

use App\Helpers\DateTimeHelper;

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime dengan timezone WIB
     * 
     * @param mixed $timestamp
     * @param string $format
     * @return string
     */
    function formatDateTime($timestamp, string $format = 'd M Y H:i:s'): string
    {
        return DateTimeHelper::formatDateTime($timestamp, $format);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date only
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatDate($timestamp): string
    {
        return DateTimeHelper::formatDate($timestamp);
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format time only dengan WIB
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatTime($timestamp): string
    {
        return DateTimeHelper::formatTime($timestamp);
    }
}

if (!function_exists('formatRelative')) {
    /**
     * Format relative time (e.g., "5 minutes ago")
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatRelative($timestamp): string
    {
        return DateTimeHelper::formatRelative($timestamp);
    }
}

if (!function_exists('formatForExport')) {
    /**
     * Format untuk export CSV/Excel
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatForExport($timestamp): string
    {
        return DateTimeHelper::formatForExport($timestamp);
    }
}

// ============================================================================
// SCHOOL CONFIGURATION HELPERS
// ============================================================================

if (!function_exists('school_name')) {
    /**
     * Get school name
     * 
     * @param string $type 'short' (default), 'full', or 'abbr'
     * @return string
     */
    function school_name(string $type = 'short'): string
    {
        return match($type) {
            'full' => config('school.nama_lengkap'),
            'abbr' => config('school.singkatan'),
            default => config('school.nama'),
        };
    }
}

if (!function_exists('school_year')) {
    /**
     * Get current academic year
     * 
     * @return string
     */
    function school_year(): string
    {
        return config('school.tahun_ajaran');
    }
}

if (!function_exists('school_config')) {
    /**
     * Get any school configuration
     * 
     * @param string $key Config key (e.g., 'alamat', 'kabupaten')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    function school_config(string $key, $default = null)
    {
        return config("school.{$key}", $default);
    }
}

if (!function_exists('sistem_info')) {
    /**
     * Get system information
     * 
     * @param string $key 'nama', 'nama_lengkap', or 'versi'
     * @return string
     */
    function sistem_info(string $key = 'nama'): string
    {
        return config("school.sistem.{$key}", '');
    }
}

