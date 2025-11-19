<?php

declare(strict_types=1);

namespace App\Services;

use Hypervel\Cache\Facades\Cache;

class CacheService
{
    /**
     * Get data from cache with fallback to callback
     */
    public static function getWithFallback(string $key, \Closure $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get data from cache with fallback and tags
     */
    public static function getWithFallbackAndTags(string $key, array $tags, \Closure $callback, int $ttl = 3600): mixed
    {
        // Note: Hyperf may not support cache tags in the same way as Laravel
        // This implementation provides a fallback approach
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Store data in cache
     */
    public static function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Store data in cache permanently
     */
    public static function forever(string $key, mixed $value): bool
    {
        return Cache::forever($key, $value);
    }

    /**
     * Check if cache key exists
     */
    public static function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Get data from cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    /**
     * Remove data from cache
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Clear all cache
     */
    public static function clear(): bool
    {
        return Cache::flush();
    }

    /**
     * Get cache statistics (if available)
     */
    public static function getStats(): array
    {
        // This is a simplified implementation
        // In a real application, you might connect to Redis directly to get stats
        return [
            'hit_count' => 0,
            'miss_count' => 0,
            'hit_rate' => 0,
        ];
    }
}