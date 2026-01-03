<?php

namespace App\Exceptions;

use Exception;

/**
 * Domain Exception Base Class
 * 
 * Abstract base class untuk semua custom domain exceptions.
 * Provides standardized interface untuk user messages dan logging context.
 */
abstract class DomainException extends Exception
{
    /**
     * HTTP status code untuk response.
     *
     * @var int
     */
    protected int $httpStatusCode = 500;

    /**
     * Get user-friendly message (aman untuk ditampilkan ke user).
     * Override di child class untuk custom message.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return $this->message ?: 'Terjadi kesalahan pada sistem.';
    }

    /**
     * Get logging context (detail untuk developer/debugging).
     *
     * @return array
     */
    public function getLogContext(): array
    {
        return [
            'exception' => get_class($this),
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ];
    }

    /**
     * Get HTTP status code.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Set HTTP status code.
     *
     * @param int $code
     * @return $this
     */
    public function setHttpStatusCode(int $code): self
    {
        $this->httpStatusCode = $code;
        return $this;
    }
}
