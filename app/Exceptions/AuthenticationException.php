<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    protected int $statusCode = 401;

    protected string $errorCode = 'UNAUTHORIZED';

    public function __construct(string $message = 'Authentication failed')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
