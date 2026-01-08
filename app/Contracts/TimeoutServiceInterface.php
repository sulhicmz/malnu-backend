<?php

declare(strict_types=1);

namespace App\Contracts;

interface TimeoutServiceInterface
{
    public function call(callable $callback, ?int $timeoutMs = null, ?callable $onTimeout = null);

    public function callWithFallback(callable $callback, callable $fallback, ?int $timeoutMs = null);

    public function setTimeout(int $timeoutSeconds): self;
}
