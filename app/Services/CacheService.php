<?php

declare(strict_types=1);

namespace App\Services;

use Psr\SimpleCache\CacheInterface;
use Throwable;

class CacheService
{
    private CacheInterface $cache;

    public function __construct()
    {
        $this->cache = \Hyperf\Context\ApplicationContext::getContainer()
            ->get(CacheInterface::class);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $ttl);

        return $value;
    }

    public function forget(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function forgetByPrefix(string $prefix): void
    {
        $keys = $this->cacheKeys($prefix);

        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    public function generateKey(string $prefix, array $params): string
    {
        ksort($params);

        $hash = md5(json_encode($params));

        return $prefix . ':' . $hash;
    }

    public function getTTL(string $type): int
    {
        return config("cache.ttl.{$type}") ?? match ($type) {
            'short' => 60,
            'medium' => 300,
            'long' => 3600,
            'day' => 86400,
            default => 300,
        };
    }

    private function cacheKeys(string $prefix): array
    {
        $pattern = $this->getCachePrefix() . $prefix . '*';

        return $this->cacheKeysByPattern($pattern);
    }

    private function cacheKeysByPattern(string $pattern): array
    {
        try {
            $redis = $this->getRedisConnection();

            if (! $redis) {
                return [];
            }

            $keys = $redis->keys($pattern);

            return array_map(function ($key) {
                return str_replace($this->getCachePrefix(), '', $key);
            }, $keys);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function getRedisConnection(): mixed
    {
        try {
            return \Hyperf\Context\ApplicationContext::getContainer()
                ->get(\Hyperf\Redis\RedisFactory::class)
                ->get('default');
        } catch (Throwable $e) {
            return null;
        }
    }

    private function getCachePrefix(): string
    {
        return config('cache.prefix', 'malnu_cache:');
    }
}
