<?php

declare(strict_types=1);

namespace App\Traits;

use Hypervel\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Get cached model data with a specific key
     */
    public static function getCached(string $key, int $minutes = 60, callable $callback = null)
    {
        $cacheKey = static::getCachePrefix() . $key;
        
        if (is_callable($callback)) {
            return Cache::remember($cacheKey, $minutes * 60, $callback);
        }
        
        return Cache::get($cacheKey);
    }

    /**
     * Cache model data with a specific key
     */
    public static function setCached(string $key, $data, int $minutes = 60)
    {
        $cacheKey = static::getCachePrefix() . $key;
        Cache::put($cacheKey, $data, $minutes * 60);
    }

    /**
     * Forget/remove cached data with a specific key
     */
    public static function forgetCached(string $key)
    {
        $cacheKey = static::getCachePrefix() . $key;
        Cache::forget($cacheKey);
    }

    /**
     * Get all cached keys for this model
     */
    public static function getCacheKeys(): array
    {
        $prefix = static::getCachePrefix();
        // Note: This is a simplified approach. In production, you might want to use Redis KEYS command
        // or maintain a list of cache keys for this model
        return [];
    }

    /**
     * Clear all cached data for this model
     */
    public static function clearCache()
    {
        // In a real implementation, you would use Redis KEYS with pattern matching
        // or maintain a list of cache keys to clear them efficiently
        $keys = static::getCacheKeys();
        foreach ($keys as $key) {
            static::forgetCached($key);
        }
    }

    /**
     * Get cache prefix for this model
     */
    protected static function getCachePrefix(): string
    {
        return 'model_' . strtolower(str_replace('\\', '_', static::class)) . '_';
    }

    /**
     * Scope to enable caching for queries
     */
    public function scopeWithCache($query, string $key, int $minutes = 60)
    {
        $cacheKey = static::getCachePrefix() . $key;
        return Cache::remember($cacheKey, $minutes * 60, function () use ($query) {
            return $query->get();
        });
    }
}