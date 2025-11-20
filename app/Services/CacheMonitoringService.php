<?php

declare(strict_types=1);

namespace App\Services;

use Hypervel\Cache\CacheManager;
use Hypervel\Support\Facades\Cache;
use Psr\Log\LoggerInterface;

class CacheMonitoringService
{
    private CacheManager $cache;
    private LoggerInterface $logger;
    
    private static int $hits = 0;
    private static int $misses = 0;

    public function __construct(CacheManager $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $total = self::$hits + self::$misses;
        $hitRate = $total > 0 ? (self::$hits / $total) * 100 : 0;
        
        return [
            'hits' => self::$hits,
            'misses' => self::$misses,
            'total' => $total,
            'hit_rate' => round($hitRate, 2),
            'driver' => config('cache.default'),
        ];
    }

    /**
     * Record a cache hit
     */
    public function recordHit(): void
    {
        self::$hits++;
    }

    /**
     * Record a cache miss
     */
    public function recordMiss(): void
    {
        self::$misses++;
    }

    /**
     * Get cache key statistics
     */
    public function getKeyStats(string $pattern = '*'): array
    {
        // This is a simplified implementation
        // In a real application, you might want to use Redis-specific commands
        // to get more detailed statistics
        
        $stats = $this->getCacheStats();
        
        return [
            'pattern' => $pattern,
            'stats' => $stats,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Flush cache and reset statistics
     */
    public function flushAndReset(): bool
    {
        $result = Cache::flush();
        self::$hits = 0;
        self::$misses = 0;
        
        $this->logger->info('Cache flushed and statistics reset');
        
        return $result;
    }

    /**
     * Get cache health status
     */
    public function getHealthStatus(): array
    {
        $stats = $this->getCacheStats();
        
        $status = 'healthy';
        if ($stats['hit_rate'] < 80) {
            $status = 'warning';
        } elseif ($stats['hit_rate'] < 50) {
            $status = 'critical';
        }
        
        return [
            'status' => $status,
            'hit_rate' => $stats['hit_rate'],
            'health_check' => true,
            'timestamp' => now()->toISOString(),
        ];
    }
}