<?php

declare(strict_types=1);

namespace App\Patterns;

use Exception;
use Throwable;

class CircuitBreakerOpenException extends Exception
{
    public function __construct(string $message = 'Circuit breaker is OPEN', int $code = 503, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
