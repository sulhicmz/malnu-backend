<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\AbstractController;
use App\Services\CacheService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

class CacheHealthController extends AbstractController
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    @GetMapping(path="/")
    public function index(): ResponseInterface
    {
        $cacheStats = $this->getCacheStats();
        $healthStatus = $this->determineHealthStatus($cacheStats);

        return $this->successResponse([
            'status' => $healthStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'cache' => [
                'driver' => env('CACHE_DRIVER', 'unknown'),
                'connection' => $cacheStats['connection'] ?? 'unknown',
            ],
            'metrics' => $cacheStats['metrics'] ?? [],
        ]);
    }

    private function getCacheStats(): array
    {
        $metrics = [
            'hits' => 0,
            'misses' => 0,
            'hit_ratio' => 0.0,
            'keys_count' => 0,
            'memory_usage' => 0,
        ];

        $metrics['keys_count'] = $this->estimateCachedKeys();

        $metrics['hit_ratio'] = $this->calculateHitRatio($metrics['hits'], $metrics['misses']);

        return [
            'connection' => $this->testCacheConnection() ? 'healthy' : 'unhealthy',
            'metrics' => $metrics,
        ];
    }

    private function testCacheConnection(): bool
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            $this->cacheService->set($testKey, $testValue, 60);

            $retrievedValue = $this->cacheService->get($testKey);

            if ($retrievedValue === $testValue) {
                $this->cacheService->forget($testKey);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function estimateCachedKeys(): int
    {
        return 0;
    }

    private function calculateHitRatio(int $hits, int $misses): float
    {
        $total = $hits + $misses;

        if ($total === 0) {
            return 0.0;
        }

        return round(($hits / $total) * 100, 2);
    }

    private function determineHealthStatus(array $cacheStats): string
    {
        if (($cacheStats['connection'] ?? 'unhealthy') !== 'healthy') {
            return 'unhealthy';
        }

        $metrics = $cacheStats['metrics'] ?? [];
        $hitRatio = $metrics['hit_ratio'] ?? 0.0;

        if ($hitRatio < 50) {
            return 'degraded';
        }

        return 'healthy';
    }
}