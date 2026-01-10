<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\SimpleCache\CacheInterface;

class CacheService
{
    #[Inject]
    private CacheInterface $cache;

    #[Inject]
    private Redis $redis;

    private string $prefix = 'hypervel_cache:';
    private int $defaultTtl = 3600;

    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $fullKey = $this->getFullKey($key);
        $cacheTtl = $ttl ?? $this->defaultTtl;

        return $this->cache->get($fullKey, $callback, $cacheTtl);
    }

    public function rememberForever(string $key, callable $callback)
    {
        $fullKey = $this->getFullKey($key);
        return $this->cache->get($fullKey, $callback);
    }

    public function put(string $key, $value, int $ttl = null)
    {
        $fullKey = $this->getFullKey($key);
        $cacheTtl = $ttl ?? $this->defaultTtl;

        return $this->cache->set($fullKey, $value, $cacheTtl);
    }

    public function forget(string $key): bool
    {
        $fullKey = $this->getFullKey($key);
        return $this->cache->delete($fullKey);
    }

    public function forgetMultiple(array $keys): bool
    {
        $fullKeys = array_map([$this, 'getFullKey'], $keys);
        return $this->cache->deleteMultiple($fullKeys);
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function get(string $key)
    {
        $fullKey = $this->getFullKey($key);
        return $this->cache->get($fullKey);
    }

    public function has(string $key): bool
    {
        $fullKey = $this->getFullKey($key);
        return $this->cache->has($fullKey);
    }

    public function increment(string $key, int $value = 1): int
    {
        $fullKey = $this->getFullKey($key);
        return $this->redis->incr($fullKey, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        $fullKey = $this->getFullKey($key);
        return $this->redis->decr($fullKey, $value);
    }

    private function getFullKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
