<?php

namespace App\Exceptions;

/**
 * Unauthorized Action Exception
 * 
 * Thrown saat user tidak punya akses untuk action tertentu.
 * Complement untuk Policy authorization.
 */
class UnauthorizedException extends DomainException
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected int $httpStatusCode = 403;

    /**
     * Action yang gagal.
     *
     * @var string
     */
    protected string $action;

    /**
     * Resource yang diakses.
     *
     * @var string
     */
    protected string $resource;

    /**
     * Create unauthorized exception.
     *
     * @param string $action
     * @param string $resource
     * @param string $message
     */
    public function __construct(string $action, string $resource = '', string $message = '')
    {
        $this->action = $action;
        $this->resource = $resource;

        $message = $message ?: "Anda tidak memiliki akses untuk {$action} {$resource}.";
        parent::__construct($message);
    }

    /**
     * Get user-friendly message.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return 'Akses ditolak. Anda tidak memiliki hak untuk melakukan tindakan ini.';
    }

    /**
     * Get logging context.
     *
     * @return array
     */
    public function getLogContext(): array
    {
        return array_merge(parent::getLogContext(), [
            'action' => $this->action,
            'resource' => $this->resource,
            'user_id' => auth()->id(),
        ]);
    }
}
