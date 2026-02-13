<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected int $statusCode = 422;

    protected string $errorCode = 'VALIDATION_ERROR';

    protected ?array $errors = null;

    public function __construct(string $message = 'Validation failed', ?array $errors = null)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
