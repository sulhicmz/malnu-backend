<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Services\ErrorTrackingService;
use Psr\Container\ContainerInterface;

class MonitoringController extends BaseController
{
    private ErrorTrackingService $errorTrackingService;

    public function __construct(ContainerInterface $container, ErrorTrackingService $errorTrackingService)
    {
        parent::__construct(
            $container->get(\Hyperf\HttpServer\Contract\RequestInterface::class),
            $container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class),
            $container
        );
        $this->errorTrackingService = $errorTrackingService;
    }

    public function metrics()
    {
        $metrics = [
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'redis' => $this->getRedisMetrics(),
            'timestamp' => date('c'),
        ];

        return $this->response->json($metrics);
    }

    public function errors()
    {
        $limit = (int) $this->request->input('limit', 50);

        if ($limit < 1 || $limit > 500) {
            $limit = 50;
        }

        $errors = $this->errorTrackingService->getRecentErrors($limit);
        $stats = $this->errorTrackingService->getErrorStats();

        return $this->response->json([
            'errors' => $errors,
            'stats' => $stats,
            'limit' => $limit,
            'count' => count($errors),
            'timestamp' => date('c'),
        ]);
    }

    public function errorStats()
    {
        $stats = $this->errorTrackingService->getErrorStats();

        return $this->response->json($stats);
    }

    private function getSystemMetrics(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $peakMemory = memory_get_peak_usage(true);

        return [
            'memory_usage_bytes' => $memoryUsage,
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'memory_peak_bytes' => $peakMemory,
            'memory_peak_mb' => round($peakMemory / 1024 / 1024, 2),
            'memory_limit' => $memoryLimit,
            'memory_usage_percent' => $this->calculateMemoryPercent($memoryUsage, $memoryLimit),
        ];
    }

    private function getDatabaseMetrics(): array
    {
        $startTime = microtime(true);
        $status = true;
        $message = 'OK';

        try {
            \Hyperf\DbConnection\Db::select('SELECT 1');
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ];
    }

    private function getRedisMetrics(): array
    {
        $startTime = microtime(true);
        $status = true;
        $message = 'OK';
        $info = [];

        try {
            $redis = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
            $redis->ping();
            $info = $redis->info();
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'connected_clients' => $info['connected_clients'] ?? 0,
            'used_memory' => $info['used_memory_human'] ?? 'N/A',
        ];
    }

    private function calculateMemoryPercent(int $usage, string $limit): float
    {
        if ($limit === '-1') {
            return 0.0;
        }

        $limitBytes = $this->convertToBytes($limit);
        if ($limitBytes === 0) {
            return 0.0;
        }

        return round(($usage / $limitBytes) * 100, 2);
    }

    private function convertToBytes(string $value): int
    {
        $value = strtoupper(trim($value));
        $units = ['B' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824];

        $unit = substr($value, -1);
        $number = (int) substr($value, 0, -1);

        return $number * ($units[$unit] ?? 1);
    }
}