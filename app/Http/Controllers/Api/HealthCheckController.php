<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponseInterface;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class HealthCheckController extends Controller
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected HyperfResponseInterface $response;

    public function __construct()
    {
        parent::__construct($this->request, $this->response);
    }

    public function index(): PsrResponseInterface
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => $this->getAppVersion(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
                'system' => $this->checkSystem(),
            ],
        ];

        $allHealthy = true;

        foreach ($checks['checks'] as $check) {
            if ($check['status'] !== 'ok') {
                $allHealthy = false;
                $checks['status'] = 'unhealthy';
                break;
            }
        }

        $statusCode = $allHealthy ? 200 : 503;

        return $this->json($checks, $statusCode);
    }

    public function database(): PsrResponseInterface
    {
        $check = $this->checkDatabase();

        $statusCode = $check['status'] === 'ok' ? 200 : 503;

        return $this->json($check, $statusCode);
    }

    public function redis(): PsrResponseInterface
    {
        $check = $this->checkRedis();

        $statusCode = $check['status'] === 'ok' ? 200 : 503;

        return $this->json($check, $statusCode);
    }

    public function system(): PsrResponseInterface
    {
        $check = $this->checkSystem();

        $statusCode = $check['status'] === 'ok' ? 200 : 503;

        return $this->json($check, $statusCode);
    }

    protected function checkDatabase(): array
    {
        $startTime = microtime(true);

        try {
            $connection = Db::connection();

            $connection->getPdo()->query('SELECT 1');

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'latency_ms' => $duration,
                'connection' => $connection->getDatabaseName(),
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
                'latency_ms' => $duration,
            ];
        }
    }

    protected function checkRedis(): array
    {
        $startTime = microtime(true);

        try {
            $redis = Redis::connection('default');

            $redis->ping();

            $info = $redis->info();
            $usedMemory = $info['used_memory_human'] ?? 'unknown';
            $connectedClients = $info['connected_clients'] ?? 0;

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'ok',
                'message' => 'Redis connection successful',
                'latency_ms' => $duration,
                'used_memory' => $usedMemory,
                'connected_clients' => $connectedClients,
            ];
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'error',
                'message' => 'Redis connection failed',
                'error' => $e->getMessage(),
                'latency_ms' => $duration,
            ];
        }
    }

    protected function checkSystem(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $diskFree = function_exists('disk_free_space') ? disk_free_space('/') : 0;
        $diskTotal = function_exists('disk_total_space') ? disk_total_space('/') : 0;

        $status = 'ok';
        $issues = [];

        if ($diskFree > 0 && $diskTotal > 0) {
            $diskUsagePercent = (($diskTotal - $diskFree) / $diskTotal) * 100;

            if ($diskUsagePercent > 90) {
                $status = 'warning';
                $issues[] = "Disk usage critical: {$diskUsagePercent}%";
            } elseif ($diskUsagePercent > 80) {
                $status = 'warning';
                $issues[] = "Disk usage high: {$diskUsagePercent}%";
            }
        }

        if ($loadAverage[0] > 10) {
            $status = 'warning';
            $issues[] = "High system load: {$loadAverage[0]}";
        }

        return [
            'status' => $status,
            'message' => empty($issues) ? 'System resources healthy' : implode(', ', $issues),
            'memory_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => $memoryLimit,
            'load_average' => [
                '1min' => $loadAverage[0] ?? 0,
                '5min' => $loadAverage[1] ?? 0,
                '15min' => $loadAverage[2] ?? 0,
            ],
            'disk' => $diskFree > 0 && $diskTotal > 0 ? [
                'free' => $this->formatBytes((int) $diskFree),
                'total' => $this->formatBytes((int) $diskTotal),
                'usage_percent' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2),
            ] : null,
        ];
    }

    protected function getAppVersion(): string
    {
        $composerPath = BASE_PATH.'/composer.json';

        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);

            return $composer['version'] ?? 'unknown';
        }

        return 'unknown';
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
