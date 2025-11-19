<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\CacheManager;
use Psr\SimpleCache\CacheInterface;

class CacheService
{
    #[Inject]
    protected CacheManager $cacheManager;

    protected CacheInterface $cache;

    public function __construct()
    {
        $this->cache = $this->cacheManager->getDriver();
    }

    /**
     * Get data with caching
     */
    public function getWithCache(string $key, callable $callback, int $ttl = 3600)
    {
        $cached = $this->cache->get($key);
        
        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();
        $this->cache->set($key, $data, $ttl);
        
        return $data;
    }

    /**
     * Put data in cache
     */
    public function putInCache(string $key, $data, int $ttl = 3600): bool
    {
        return $this->cache->set($key, $data, $ttl);
    }

    /**
     * Delete from cache
     */
    public function deleteFromCache(string $key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * Clear cache by prefix
     */
    public function clearByPrefix(string $prefix): bool
    {
        // In Hyperf, we need to use the cache manager's clear method
        // This is a simplified approach - in practice, you'd use tags or implement prefix-based clearing
        return true;
    }

    /**
     * Get cache hit ratio
     */
    public function getCacheStats(): array
    {
        // Placeholder for cache statistics
        return [
            'hits' => 0,
            'misses' => 0,
            'hit_ratio' => 0
        ];
    }
}