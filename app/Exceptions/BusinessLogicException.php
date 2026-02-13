<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class BusinessLogicException extends Exception
{
    protected int $statusCode = 400;

    protected string $errorCode = 'BUSINESS_LOGIC_ERROR';

    public function __construct(string $message = 'Business logic error')
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
