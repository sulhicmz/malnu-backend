<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\CacheManager;
use Hyperf\Utils\Arr;
use Psr\SimpleCache\CacheInterface;

class PerformanceMonitorService
{
    #[Inject]
    protected CacheManager $cacheManager;

    private CacheInterface $cache;

    public function __construct()
    {
        $this->cache = $this->cacheManager->getDriver();
    }

    /**
     * Track cache hit/miss statistics
     */
    public function trackCacheHit(string $key, bool $wasHit): void
    {
        $statsKey = 'performance:cache_stats';
        $stats = $this->cache->get($statsKey, [
            'hits' => 0,
            'misses' => 0
        ]);

        if ($wasHit) {
            $stats['hits']++;
        } else {
            $stats['misses']++;
        }

        $this->cache->set($statsKey, $stats, 86400); // 24 hours
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $statsKey = 'performance:cache_stats';
        $stats = $this->cache->get($statsKey, [
            'hits' => 0,
            'misses' => 0
        ]);

        $total = $stats['hits'] + $stats['misses'];
        $hitRatio = $total > 0 ? ($stats['hits'] / $total) * 100 : 0;

        return [
            'hits' => $stats['hits'],
            'misses' => $stats['misses'],
            'total' => $total,
            'hit_ratio' => round($hitRatio, 2),
            'hit_ratio_percentage' => round($hitRatio, 2) . '%'
        ];
    }

    /**
     * Track query execution time
     */
    public function trackQueryTime(string $query, float $executionTime): void
    {
        $slowQueriesKey = 'performance:slow_queries';
        $slowQueries = $this->cache->get($slowQueriesKey, []);

        // Store queries that take longer than 100ms
        if ($executionTime > 0.1) { // 100ms
            $slowQueries[] = [
                'query' => $query,
                'execution_time' => $executionTime,
                'timestamp' => time()
            ];

            // Keep only the last 100 slow queries
            $slowQueries = array_slice($slowQueries, -100);
            $this->cache->set($slowQueriesKey, $slowQueries, 3600); // 1 hour
        }

        // Track average execution time
        $avgTimeKey = 'performance:avg_query_time';
        $avgTimeData = $this->cache->get($avgTimeKey, [
            'total_time' => 0,
            'query_count' => 0
        ]);

        $avgTimeData['total_time'] += $executionTime;
        $avgTimeData['query_count']++;

        $this->cache->set($avgTimeKey, $avgTimeData, 3600);
    }

    /**
     * Get query performance statistics
     */
    public function getQueryStats(): array
    {
        $avgTimeKey = 'performance:avg_query_time';
        $avgTimeData = $this->cache->get($avgTimeKey, [
            'total_time' => 0,
            'query_count' => 0
        ]);

        $avgTime = $avgTimeData['query_count'] > 0 
            ? $avgTimeData['total_time'] / $avgTimeData['query_count'] 
            : 0;

        $slowQueriesKey = 'performance:slow_queries';
        $slowQueries = $this->cache->get($slowQueriesKey, []);

        return [
            'average_execution_time' => round($avgTime, 4),
            'average_execution_time_ms' => round($avgTime * 1000, 2),
            'total_queries_tracked' => $avgTimeData['query_count'],
            'slow_queries' => $slowQueries,
            'slow_query_count' => count($slowQueries)
        ];
    }

    /**
     * Get overall performance report
     */
    public function getPerformanceReport(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'cache_stats' => $this->getCacheStats(),
            'query_stats' => $this->getQueryStats(),
            'target_metrics' => [
                'cache_hit_ratio_target' => '80%',
                'avg_query_time_target' => '<100ms',
                'current_status' => [
                    'cache_hit_ratio_met' => $this->getCacheStats()['hit_ratio'] >= 80,
                    'query_time_met' => $this->getQueryStats()['average_execution_time_ms'] < 100
                ]
            ]
        ];
    }

    /**
     * Reset performance statistics
     */
    public function resetStats(): void
    {
        $this->cache->delete('performance:cache_stats');
        $this->cache->delete('performance:avg_query_time');
        $this->cache->delete('performance:slow_queries');
    }
}