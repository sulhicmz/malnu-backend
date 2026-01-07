<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Hypervel\Cache\Contracts\Repository;
use Psr\Container\ContainerInterface;

class CacheService
{
    private string $prefix = 'hypervel_cache:';

    private Repository $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->cache = $container->get(Repository::class);
    }

    public function getWithFallback(string $key, Closure $callback, int $ttl = 3600): mixed
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->remember($fullKey, $ttl, $callback);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->put($fullKey, $value, $ttl);
    }

    public function get(string $key): mixed
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->get($fullKey);
    }

    public function has(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->has($fullKey);
    }

    public function forget(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return $this->cache->forget($fullKey);
    }

    public function generateKey(string $pattern, array $params = []): string
    {
        $key = $pattern;
        foreach ($params as $k => $v) {
            $key = str_replace('{' . $k . '}', (string) $v, $key);
        }
        return $key;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getStats(): array
    {
        return [
            'prefix' => $this->prefix,
            'enabled' => $this->cache !== null,
        ];
    }
}
