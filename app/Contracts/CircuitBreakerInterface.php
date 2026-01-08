<?php

declare(strict_types=1);

namespace App\Contracts;

interface CircuitBreakerInterface
{
    public const STATE_CLOSED = 'closed';

    public const STATE_OPEN = 'open';

    public const STATE_HALF_OPEN = 'half_open';

    public function call(callable $callback, ?callable $fallback = null);

    public function reset(): void;

    public function getState(): string;

    public function getFailureCount(): int;

    public function getSuccessCount(): int;

    public function getLastFailureTime(): int;
}
