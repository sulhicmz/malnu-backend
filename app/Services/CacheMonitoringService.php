<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Log\LoggerInterface;

class CacheMonitoringService
{
    #[Inject]
    private Redis $redis;

    #[Inject]
    private LoggerInterface $logger;

    private string $prefix = 'cache_monitor:';

    /**
     * Record a cache hit
     */
    public function recordHit(string $key): void
    {
        $this->redis->incr($this->prefix . 'hits');
        $this->redis->incr($this->prefix . 'key:' . md5($key) . ':hits');
    }

    /**
     * Record a cache miss
     */
    public function recordMiss(string $key): void
    {
        $this->redis->incr($this->prefix . 'misses');
        $this->redis->incr($this->prefix . 'key:' . md5($key) . ':misses');
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        $hits = (int) $this->redis->get($this->prefix . 'hits') ?: 0;
        $misses = (int) $this->redis->get($this->prefix . 'misses') ?: 0;
        $total = $hits + $misses;
        $hitRatio = $total > 0 ? round(($hits / $total) * 100, 2) : 0;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'total' => $total,
            'hit_ratio' => $hitRatio,
            'target_hit_ratio' => 80,
        ];
    }

    /**
     * Reset cache statistics
     */
    public function resetStatistics(): void
    {
        $this->redis->del($this->prefix . 'hits');
        $this->redis->del($this->prefix . 'misses');
    }

    /**
     * Get top accessed cache keys
     */
    public function getTopKeys(int $limit = 10): array
    {
        $pattern = $this->prefix . 'key:*';
        $keys = $this->redis->keys($pattern);
        $stats = [];

        foreach ($keys as $key) {
            $hits = (int) $this->redis->get($key . ':hits') ?: 0;
            $misses = (int) $this->redis->get($key . ':misses') ?: 0;
            $stats[substr($key, strlen($this->prefix) + 4)] = [
                'hits' => $hits,
                'misses' => $misses,
                'total' => $hits + $misses,
            ];
        }

        uasort($stats, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        return array_slice($stats, 0, $limit, true);
    }

    /**
     * Check if cache performance is healthy
     */
    public function isPerformanceHealthy(): bool
    {
        $stats = $this->getStatistics();
        return $stats['hit_ratio'] >= 80;
    }

    /**
     * Log cache performance
     */
    public function logPerformance(): void
    {
        $stats = $this->getStatistics();
        $this->logger->info('Cache Performance Metrics', $stats);

        if (!$this->isPerformanceHealthy()) {
            $this->logger->warning('Cache performance below target', [
                'current_hit_ratio' => $stats['hit_ratio'],
                'target_hit_ratio' => $stats['target_hit_ratio'],
            ]);
        }
    }
}
