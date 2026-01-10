<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Api\BaseController;
use App\Services\CacheService;
use App\Services\EmailService;
use Exception;
use Psr\Container\ContainerInterface;

class HealthController extends BaseController
{
    private CacheService $cacheService;

    private EmailService $emailService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->cacheService = $container->get(CacheService::class);
        $this->emailService = $container->get(EmailService::class);
    }

    public function healthCheck()
    {
        $checks = [
            'cache' => $this->checkCache(),
            'email' => $this->checkEmail(),
            'memory' => $this->checkMemory(),
            'disk' => $this->checkDisk(),
        ];

        $overallStatus = $this->calculateOverallStatus($checks);

        return $this->successResponse([
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'checks' => $checks,
        ], 'Health check completed');
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $this->cacheService->set($testKey, 'test', 60);
            $value = $this->cacheService->get($testKey);
            $this->cacheService->forget($testKey);

            if ($value === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache service is operational',
                    'metrics' => $this->cacheService->getMetrics(),
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Cache write/read failed',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache service error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkEmail(): array
    {
        try {
            return $this->emailService->getHealthStatus();
        } catch (Exception $e) {
            return [
                'service' => 'email',
                'status' => 'unhealthy',
                'message' => 'Email service error: ' . $e->getMessage(),
            ];
        }
    }

    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsagePercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

        $status = 'healthy';
        if ($memoryUsagePercent > 80) {
            $status = 'degraded';
        }
        if ($memoryUsagePercent > 95) {
            $status = 'unhealthy';
        }

        return [
            'status' => $status,
            'usage_bytes' => $memoryUsage,
            'limit_bytes' => $memoryLimit,
            'usage_percent' => round($memoryUsagePercent, 2),
        ];
    }

    private function checkDisk(): array
    {
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');

        if ($freeSpace === false || $totalSpace === false) {
            return [
                'status' => 'unknown',
                'message' => 'Unable to determine disk space',
            ];
        }

        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;

        $status = 'healthy';
        if ($usagePercent > 80) {
            $status = 'degraded';
        }
        if ($usagePercent > 95) {
            $status = 'unhealthy';
        }

        return [
            'status' => $status,
            'free_bytes' => $freeSpace,
            'total_bytes' => $totalSpace,
            'used_bytes' => $usedSpace,
            'usage_percent' => round($usagePercent, 2),
        ];
    }

    private function calculateOverallStatus(array $checks): string
    {
        $statuses = array_map(fn ($check) => $check['status'], $checks);

        if (in_array('unhealthy', $statuses)) {
            return 'unhealthy';
        }

        if (in_array('degraded', $statuses)) {
            return 'degraded';
        }

        return 'healthy';
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));

        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return $value;
        }
    }
}
