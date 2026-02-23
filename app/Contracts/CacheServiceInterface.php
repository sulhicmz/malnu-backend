<?php

declare(strict_types=1);

namespace App\Contracts;

interface CacheServiceInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function remember(string $key, int $ttl, callable $callback): mixed;

    public function forget(string $key): bool;

    public function flush(): bool;

    public function forgetByPrefix(string $prefix): void;

    public function generateKey(string $prefix, array $params): string;

    public function getTTL(string $type): int;
}
