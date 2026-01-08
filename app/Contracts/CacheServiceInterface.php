<?php

declare(strict_types=1);

namespace App\Contracts;

interface CacheServiceInterface
{
    public function getWithFallback(string $key, \Closure $callback, int $ttl = 3600): mixed;

    public function put(string $key, mixed $value, int $ttl = 3600): bool;

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function forget(string $key): bool;

    public function generateKey(string $pattern, array $params = []): string;

    public function getPrefix(): string;

    public function getStats(): array;
}
