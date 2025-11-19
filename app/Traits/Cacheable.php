<?php

declare(strict_types=1);

namespace App\Traits;

use Hypervel\Cache\Facades\Cache;

trait Cacheable
{
    /**
     * Get model with caching
     */
    public function scopeWithCaching($query, string $cacheKey, int $ttl = 3600)
    {
        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $query->get();
        });
    }

    /**
     * Find model by ID with caching
     */
    public static function findWithCache($id, int $ttl = 3600)
    {
        $cacheKey = static::class . ".{$id}";
        
        return Cache::remember($cacheKey, $ttl, function () use ($id) {
            return static::find($id);
        });
    }

    /**
     * Clear model cache
     */
    public function clearModelCache(): void
    {
        $cacheKey = static::class . ".{$this->id}";
        Cache::forget($cacheKey);
    }
}