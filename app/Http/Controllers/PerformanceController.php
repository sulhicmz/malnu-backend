<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CacheMonitoringService;
use Hypervel\Http\JsonResponse;

class PerformanceController extends AbstractController
{
    private CacheMonitoringService $cacheMonitoring;

    public function __construct(CacheMonitoringService $cacheMonitoring)
    {
        $this->cacheMonitoring = $cacheMonitoring;
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(): JsonResponse
    {
        $stats = $this->cacheMonitoring->getCacheStats();
        
        return new JsonResponse([
            'data' => $stats,
            'message' => 'Cache statistics retrieved successfully'
        ]);
    }

    /**
     * Get cache health status
     */
    public function cacheHealth(): JsonResponse
    {
        $health = $this->cacheMonitoring->getHealthStatus();
        
        return new JsonResponse([
            'data' => $health,
            'message' => 'Cache health status retrieved successfully'
        ]);
    }

    /**
     * Get cache key statistics
     */
    public function cacheKeys(string $pattern = '*'): JsonResponse
    {
        $keyStats = $this->cacheMonitoring->getKeyStats($pattern);
        
        return new JsonResponse([
            'data' => $keyStats,
            'message' => 'Cache key statistics retrieved successfully'
        ]);
    }

    /**
     * Flush cache and reset statistics
     */
    public function flushCache(): JsonResponse
    {
        $result = $this->cacheMonitoring->flushAndReset();
        
        return new JsonResponse([
            'success' => $result,
            'message' => $result ? 'Cache flushed and statistics reset successfully' : 'Failed to flush cache'
        ]);
    }
}