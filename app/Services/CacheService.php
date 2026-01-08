<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Cache\Cache;
use Psr\Container\ContainerInterface;

class CacheService
{
    public const TTL_SHORT = self::CACHE_TTL_SHORT;

    public const TTL_MEDIUM = self::CACHE_TTL_MEDIUM;

    public const TTL_LONG = self::CACHE_TTL_LONG;

    public const TTL_VERY_LONG = self::CACHE_TTL_VERY_LONG;

    private const CACHE_TTL_SHORT = 300;

    private const CACHE_TTL_MEDIUM = 1800;

    private const CACHE_TTL_LONG = 3600;

    private const CACHE_TTL_VERY_LONG = 86400;

    private Cache $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->cache = $container->get(Cache::class);
    }

    public function remember(string $key, int $ttl, callable $callback)
    {
        return $this->cache->remember($key, $ttl, $callback);
    }

    public function rememberForever(string $key, callable $callback)
    {
        return $this->cache->remember($key, null, $callback);
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function set(string $key, $value, int $ttl = self::CACHE_TTL_MEDIUM): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function forgetByPattern(string $pattern): bool
    {
        $keys = $this->cache->getRedis()->keys($pattern);

        if (empty($keys)) {
            return true;
        }

        return $this->cache->deleteMultiple($keys);
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function rememberQuery(string $model, string $method, array $params = [], int $ttl = self::CACHE_TTL_MEDIUM, callable $callback)
    {
        $key = $this->generateQueryKey($model, $method, $params);
        return $this->remember($key, $ttl, $callback);
    }

    public function forgetQuery(string $model, string $method, array $params = []): bool
    {
        $pattern = $this->generateQueryPattern($model, $method);
        return $this->forgetByPattern($pattern);
    }

    public function rememberApiResponse(string $endpoint, array $params = [], int $ttl = self::CACHE_TTL_SHORT, callable $callback)
    {
        $key = $this->generateApiKey($endpoint, $params);
        return $this->remember($key, $ttl, $callback);
    }

    public function forgetApiResponse(string $endpoint, array $params = []): bool
    {
        $pattern = $this->generateApiPattern($endpoint);
        return $this->forgetByPattern($pattern);
    }

    public function rememberModel(string $model, string $id, int $ttl = self::CACHE_TTL_LONG, callable $callback)
    {
        $key = $this->generateModelKey($model, $id);
        return $this->remember($key, $ttl, $callback);
    }

    public function forgetModel(string $model, string $id): bool
    {
        $key = $this->generateModelKey($model, $id);
        return $this->forget($key);
    }

    public function warmUpStudentCache(): void
    {
        $students = \App\Models\SchoolManagement\Student::with(['class'])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($students as $student) {
            $this->set(
                $this->generateModelKey('Student', $student->id),
                $student->toArray(),
                self::CACHE_TTL_LONG
            );
        }
    }

    public function warmUpTeacherCache(): void
    {
        $teachers = \App\Models\SchoolManagement\Teacher::with(['subject', 'class'])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($teachers as $teacher) {
            $this->set(
                $this->generateModelKey('Teacher', $teacher->id),
                $teacher->toArray(),
                self::CACHE_TTL_LONG
            );
        }
    }

    public function warmUpCache(): void
    {
        $this->warmUpStudentCache();
        $this->warmUpTeacherCache();
    }

    public function getMetrics(): array
    {
        $redis = $this->cache->getRedis();
        $info = $redis->info('stats');

        return [
            'total_commands' => $info['total_commands_processed'] ?? 0,
            'total_keys' => $redis->dbSize(),
            'keyspace_hits' => $info['keyspace_hits'] ?? 0,
            'keyspace_misses' => $info['keyspace_misses'] ?? 0,
            'hit_rate' => isset($info['keyspace_hits']) && isset($info['keyspace_misses'])
                ? round($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100, 2)
                : 0,
        ];
    }

    public function invalidateModelCache(string $model): void
    {
        $pattern = "model:{$model}:*";
        $this->forgetByPattern($pattern);
    }

    public function generateModelKey(string $model, string $id): string
    {
        return "model:{$model}:{$id}";
    }

    private function generateQueryKey(string $model, string $method, array $params): string
    {
        $paramHash = md5(json_encode($params));
        return "query:{$model}:{$method}:{$paramHash}";
    }

    private function generateQueryPattern(string $model, string $method): string
    {
        return "query:{$model}:{$method}:*";
    }

    private function generateApiKey(string $endpoint, array $params): string
    {
        $paramHash = md5(json_encode($params));
        return "api:{$endpoint}:{$paramHash}";
    }

    private function generateApiPattern(string $endpoint): string
    {
        return "api:{$endpoint}:*";
    }
}
