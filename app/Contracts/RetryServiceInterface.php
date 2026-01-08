<?php

declare(strict_types=1);

namespace App\Contracts;

interface RetryServiceInterface
{
    public function call(callable $callback, ?callable $onRetry = null);

    public function callWithFallback(callable $callback, callable $fallback, ?callable $onRetry = null);

    public function addRetryableException(string $exceptionClass): self;

    public function setMaxAttempts(int $maxAttempts): self;

    public function setBaseDelay(int $baseDelay): self;

    public function setExponentialFactor(float $factor): self;

    public function setMaxDelay(int $maxDelay): self;
}
