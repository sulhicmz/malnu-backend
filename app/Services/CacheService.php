<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine as Co;

class CacheService
{
    private \Redis $redis;

    private array $metrics = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    ];

    public function __construct()
    {
        $this->redis = new \Redis();
        
        $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
        $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
        $db = (int)($_ENV['REDIS_DB'] ?? 0);
        
        $this->redis->connect($host, $port);
        $this->redis->select($db);
    }

    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->get($key);

        if ($value !== null && $value !== false) {
            $this->metrics['hits']++;
            return $value;
        }

        $this->metrics['misses']++;
        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        if ($value === false || $value === null) {
            $this->metrics['misses']++;
            return null;
        }

        $this->metrics['hits']++;
        return $this->unserialize($value);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->metrics['sets']++;
        $serialized = $this->serialize($value);
        return $this->redis->setex($key, $ttl, $serialized);
    }

    public function forget(string $key): bool
    {
        $this->metrics['deletes']++;
        return $this->redis->del($key) > 0;
    }

    public function flush(): bool
    {
        return $this->redis->flushDB();
    }

    public function rememberByTag(string $key, string $tag, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->get($key);

        if ($value !== null && $value !== false) {
            return $value;
        }

        $value = $callback();
        $this->setWithTag($key, $tag, $value, $ttl);

        return $value;
    }

    public function setWithTag(string $key, string $tag, mixed $value, int $ttl = 3600): bool
    {
        $success = $this->set($key, $value, $ttl);
        
        if ($success) {
            $tagKey = "tags:{$tag}";
            $taggedKeys = $this->get($tagKey) ?? [];
            if (!in_array($key, $taggedKeys, true)) {
                $taggedKeys[] = $key;
            }
            $this->set($tagKey, $taggedKeys, 86400);
        }

        return $success;
    }

    public function forgetByTag(string $tag): bool
    {
        $tagKey = "tags:{$tag}";
        $taggedKeys = $this->get($tagKey) ?? [];

        foreach ($taggedKeys as $key) {
            $this->forget($key);
        }

        return $this->forget($tagKey);
    }

    public function getMetrics(): array
    {
        $total = $this->metrics['hits'] + $this->metrics['misses'];
        $hitRate = $total > 0 ? round(($this->metrics['hits'] / $total) * 100, 2) : 0;

        return [
            'hits' => $this->metrics['hits'],
            'misses' => $this->metrics['misses'],
            'hit_rate' => $hitRate,
            'sets' => $this->metrics['sets'],
            'deletes' => $this->metrics['deletes'],
        ];
    }

    public function resetMetrics(): void
    {
        $this->metrics = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
        ];
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function getMultiple(array $keys): array
    {
        $values = [];
        
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null) {
                $values[$key] = $value;
            }
        }
        
        return $values;
    }

    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        
        return $success;
    }

    public function deleteMultiple(array $keys): bool
    {
        $this->metrics['deletes'] += count($keys);
        return $this->redis->del(...$keys) > 0;
    }

    public function rememberAsync(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->get($key);

        if ($value !== null && $value !== false) {
            return $value;
        }

        $this->metrics['misses']++;
        
        Co::create(function () use ($key, $callback, $ttl) {
            $value = $callback();
            $this->set($key, $value, $ttl);
            return $value;
        });
    }

    private function serialize(mixed $value): string
    {
        return serialize($value);
    }

    private function unserialize(string $value): mixed
    {
        return unserialize($value);
    }

    public function increment(string $key, int $value = 1): int
    {
        return $this->redis->incrBy($key, $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->redis->decrBy($key, $value);
    }

    public function getTtl(string $key): int
    {
        return $this->redis->ttl($key);
    }

    public function expire(string $key, int $ttl): bool
    {
        return $this->redis->expire($key, $ttl);
    }
}
