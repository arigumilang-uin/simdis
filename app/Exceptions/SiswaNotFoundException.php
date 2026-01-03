<?php

namespace App\Exceptions;

/**
 * Siswa Not Found Exception
 * 
 * Thrown when siswa record tidak ditemukan.
 */
class SiswaNotFoundException extends DomainException
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected int $httpStatusCode = 404;

    /**
     * Create siswa not found exception.
     *
     * @param int|string $identifier NISN or ID
     * @param string $message
     */
    public function __construct($identifier, string $message = '')
    {
        $message = $message ?: "Siswa dengan identifier '{$identifier}' tidak ditemukan.";
        parent::__construct($message);
    }

    /**
     * Get user-friendly message.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return 'Data siswa tidak ditemukan. Silakan periksa kembali.';
    }
}
