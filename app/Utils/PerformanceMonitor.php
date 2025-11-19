<?php

declare(strict_types=1);

namespace App\Utils;

use Hypervel\Cache\Facades\Cache;
use Hyperf\Utils\Arr;

class PerformanceMonitor
{
    private static array $queryLog = [];
    private static array $cacheStats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
    ];
    private static float $startTime;

    public static function startTimer(): void
    {
        self::$startTime = microtime(true);
    }

    public static function getExecutionTime(): float
    {
        if (!isset(self::$startTime)) {
            return 0;
        }
        
        return microtime(true) - self::$startTime;
    }

    public static function logQuery(string $query, float $executionTime, array $bindings = []): void
    {
        self::$queryLog[] = [
            'query' => $query,
            'execution_time' => $executionTime,
            'bindings' => $bindings,
            'timestamp' => microtime(true),
        ];
    }

    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    public static function getSlowQueries(float $threshold = 0.1): array
    {
        return array_filter(self::$queryLog, function ($log) use ($threshold) {
            return $log['execution_time'] > $threshold;
        });
    }

    public static function incrementCacheHit(): void
    {
        self::$cacheStats['hits']++;
    }

    public static function incrementCacheMiss(): void
    {
        self::$cacheStats['misses']++;
    }

    public static function incrementCacheSet(): void
    {
        self::$cacheStats['sets']++;
    }

    public static function getCacheStats(): array
    {
        return self::$cacheStats;
    }

    public static function getCacheHitRatio(): float
    {
        $total = self::$cacheStats['hits'] + self::$cacheStats['misses'];
        
        if ($total === 0) {
            return 0;
        }
        
        return (float) self::$cacheStats['hits'] / $total;
    }

    public static function reset(): void
    {
        self::$queryLog = [];
        self::$cacheStats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
        ];
        self::$startTime = null;
    }

    public static function getPerformanceReport(): array
    {
        return [
            'execution_time' => self::getExecutionTime(),
            'total_queries' => count(self::$queryLog),
            'slow_queries' => count(self::getSlowQueries()),
            'cache_hit_ratio' => self::getCacheHitRatio(),
            'cache_stats' => self::$cacheStats,
            'query_log' => self::$queryLog,
        ];
    }
}