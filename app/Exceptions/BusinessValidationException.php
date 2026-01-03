<?php

namespace App\Exceptions;

/**
 * Business Validation Exception
 * 
 * Thrown saat business logic validation gagal.
 * BUKAN untuk form validation (gunakan FormRequest untuk itu).
 * 
 * Contoh use case:
 * - NISN sudah digunakan siswa lain
 * - Siswa sudah punya kasus tindak lanjut aktif
 * - Pelanggaran tidak bisa dihapus (sudah lewat 3 hari)
 */
class BusinessValidationException extends DomainException
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected int $httpStatusCode = 422;

    /**
     * Validation errors detail.
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * Create business validation exception.
     *
     * @param string $message
     * @param array $errors
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get logging context with errors.
     *
     * @return array
     */
    public function getLogContext(): array
    {
        return array_merge(parent::getLogContext(), [
            'validation_errors' => $this->errors,
        ]);
    }
}
