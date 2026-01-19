<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Hyperf\Contract\LoggerInterface;

class HealthController extends Controller
{
    protected Db $db;

    protected Redis $redis;

    protected LoggerInterface $logger;

    public function __construct(Db $db, Redis $redis, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function index(): ResponseInterface
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'checks' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
            ],
        ];

        $overallStatus = $this->getOverallHealth($health['checks']);

        $health['status'] = $overallStatus;

        $statusCode = $overallStatus === 'healthy' ? 200 : 503;

        return $this->response->json($health)->withStatus($statusCode);
    }

    public function detailed(): ResponseInterface
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'checks' => [
                'database' => array_merge($this->checkDatabase(), $this->getDatabaseDetails()),
                'redis' => array_merge($this->checkRedis(), $this->getRedisDetails()),
                'system' => $this->getSystemDetails(),
            ],
        ];

        $overallStatus = $this->getOverallHealth($health['checks']);

        $health['status'] = $overallStatus;

        $statusCode = $overallStatus === 'healthy' ? 200 : 503;

        return $this->response->json($health)->withStatus($statusCode);
    }

    public function metrics(): ResponseInterface
    {
        $metrics = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'uptime' => $this->getUptime(),
            'performance' => [
                'memory_usage' => $this->getMemoryUsage(),
                'memory_limit' => ini_get('memory_limit'),
                'peak_memory' => memory_get_peak_usage(true),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'os' => PHP_OS_FAMILY . ' ' . PHP_OS,
            ],
        ];

        return $this->response->json($metrics);
    }

    protected function checkDatabase(): array
    {
        try {
            $connection = $this->db->getConnection();
            $connection->getPdo();

            return [
                'status' => 'up',
                'response_time_ms' => $this->measureDatabaseQuery(),
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Database health check failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkRedis(): array
    {
        try {
            $startTime = microtime(true);
            $this->redis->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'up',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Redis health check failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getDatabaseDetails(): array
    {
        try {
            $connection = $this->db->getConnection();
            $pdo = $connection->getPdo();

            return [
                'driver' => $connection->getConfig('driver'),
                'database' => $connection->getConfig('database'),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
            ];
        } catch (\Throwable $e) {
            return [
                'driver' => 'unknown',
                'database' => 'unknown',
                'version' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getRedisDetails(): array
    {
        try {
            $info = $this->redis->info('server');

            return [
                'host' => config('redis.default.host', 'localhost'),
                'port' => config('redis.default.port', 6379),
                'version' => $info['redis_version'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
            ];
        } catch (\Throwable $e) {
            return [
                'host' => config('redis.default.host', 'localhost'),
                'port' => config('redis.default.port', 6379),
                'version' => 'unknown',
                'connected_clients' => 0,
                'used_memory' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getSystemDetails(): array
    {
        $load = sys_getloadavg();

        return [
            'load_average' => [
                '1_min' => $load[0] ?? 0,
                '5_min' => $load[1] ?? 0,
                '15_min' => $load[2] ?? 0,
            ],
            'disk_usage' => $this->getDiskUsage(),
        ];
    }

    protected function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('cat /proc/uptime | cut -d. -f1');
            if ($uptime) {
                return trim($uptime);
            }
        }

        return 'unknown';
    }

    protected function getMemoryUsage(): string
    {
        $memory = memory_get_usage(true);
        $memoryMB = round($memory / 1024 / 1024, 2);

        return $memoryMB . ' MB';
    }

    protected function getDiskUsage(): array
    {
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');

        if ($diskTotal === false || $diskFree === false) {
            return [
                'total' => 'unknown',
                'free' => 'unknown',
                'used_percent' => 'unknown',
            ];
        }

        $usedPercent = 100 - round(($diskFree / $diskTotal) * 100, 2);

        return [
            'total' => round($diskTotal / 1024 / 1024 / 1024, 2) . ' GB',
            'free' => round($diskFree / 1024 / 1024 / 1024, 2) . ' GB',
            'used_percent' => $usedPercent,
        ];
    }

    protected function measureDatabaseQuery(): float
    {
        try {
            $startTime = microtime(true);
            $this->db->select('SELECT 1');
            $duration = (microtime(true) - $startTime) * 1000;

            return round($duration, 2);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getOverallHealth(array $checks): string
    {
        $statuses = array_column($checks, 'status');
        $hasDown = in_array('down', $statuses);

        return $hasDown ? 'unhealthy' : 'healthy';
    }
}
