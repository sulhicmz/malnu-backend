<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    protected int $statusCode = 500;

    protected string $errorCode = 'DATABASE_ERROR';

    public function __construct(string $message = 'Database error occurred')
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
