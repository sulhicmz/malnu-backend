<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

class CacheMonitoringService
{
    protected Redis $redis;
    protected string $statisticsPrefix = 'cache:stats:';

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(Redis::class);
    }

    public function recordHit(string $key): void
    {
        $this->incrementCounter('hits', $key);
    }

    public function recordMiss(string $key): void
    {
        $this->incrementCounter('misses', $key);
    }

    protected function incrementCounter(string $type, string $key): void
    {
        $statsKey = $this->statisticsPrefix . $type;
        $this->redis->incr($statsKey);
        $this->redis->incr($statsKey . ':' . $key);
    }

    public function getStatistics(): array
    {
        $hits = (int) $this->redis->get($this->statisticsPrefix . 'hits') ?? 0;
        $misses = (int) $this->redis->get($this->statisticsPrefix . 'misses') ?? 0;
        $total = $hits + $misses;
        $hitRatio = $total > 0 ? round(($hits / $total) * 100, 2) : 0;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'total' => $total,
            'hit_ratio' => $hitRatio . '%',
        ];
    }

    public function getTopKeys(int $limit = 10): array
    {
        return [];
    }

    public function isPerformanceHealthy(): bool
    {
        $stats = $this->getStatistics();
        $hitRatio = (float) str_replace('%', '', $stats['hit_ratio']);
        return $hitRatio >= 80.0;
    }

    public function resetStatistics(): void
    {
        $this->redis->del($this->statisticsPrefix . 'hits');
        $this->redis->del($this->statisticsPrefix . 'misses');
    }

    public function logPerformance(array $metrics): void
    {
    }
}
