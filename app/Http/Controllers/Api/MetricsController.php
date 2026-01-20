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

class MetricsController extends Controller
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
        $metrics = [
            'generated_at' => date('c'),
            'requests' => $this->getRequestMetrics(),
            'errors' => $this->getErrorMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'system' => $this->getSystemMetrics(),
        ];

        return $this->json($metrics, 200);
    }

    protected function getRequestMetrics(): array
    {
        try {
            $redis = Redis::connection('default');
            $metrics = [
                'total_requests' => (int) $redis->get('metrics:requests:total'),
                'successful_requests' => (int) $redis->get('metrics:requests:successful'),
                'failed_requests' => (int) $redis->get('metrics:requests:failed'),
                'avg_response_time_ms' => (float) $redis->get('metrics:requests:avg_response_time'),
                'p95_response_time_ms' => (float) $redis->get('metrics:requests:p95_response_time'),
                'p99_response_time_ms' => (float) $redis->get('metrics:requests:p99_response_time'),
            ];

            return $metrics;
        } catch (\Throwable $e) {
            return [
                'error' => 'Unable to retrieve request metrics',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getErrorMetrics(): array
    {
        try {
            $redis = Redis::connection('default');
            $errorTypes = json_decode($redis->get('metrics:errors:types') ?? '{}', true);
            
            arsort($errorTypes);

            $metrics = [
                'total_errors' => (int) $redis->get('metrics:errors:total'),
                'error_rate_percent' => $this->calculateErrorRate(),
                'top_errors' => array_slice($errorTypes, 0, 10, true),
                'critical_errors' => (int) $redis->get('metrics:errors:critical'),
                'warning_errors' => (int) $redis->get('metrics:errors:warning'),
            ];

            return $metrics;
        } catch (\Throwable $e) {
            return [
                'error' => 'Unable to retrieve error metrics',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getDatabaseMetrics(): array
    {
        try {
            $connection = Db::connection();
            $pdo = $connection->getPdo();
            
            $metrics = [
                'connection_pool_size' => $connection->getConfig('pool.max_connections'),
                'active_connections' => $connection->getConfig('pool.min_connections'),
                'query_count' => (int) Redis::connection('default')->get('metrics:database:queries') ?? 0,
                'avg_query_time_ms' => (float) Redis::connection('default')->get('metrics:database:avg_query_time') ?? 0,
                'slow_queries_count' => (int) Redis::connection('default')->get('metrics:database:slow_queries') ?? 0,
            ];

            return $metrics;
        } catch (\Throwable $e) {
            return [
                'error' => 'Unable to retrieve database metrics',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getCacheMetrics(): array
    {
        try {
            $redis = Redis::connection('default');
            $info = $redis->info('stats');
            
            $metrics = [
                'hit_rate_percent' => $this->calculateCacheHitRate($info),
                'total_hits' => (int) ($info['keyspace_hits'] ?? 0),
                'total_misses' => (int) ($info['keyspace_misses'] ?? 0),
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'evictions' => (int) ($info['evicted_keys'] ?? 0),
                'connected_clients' => (int) ($info['connected_clients'] ?? 0),
            ];

            return $metrics;
        } catch (\Throwable $e) {
            return [
                'error' => 'Unable to retrieve cache metrics',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getSystemMetrics(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_limit_mb' => $this->parseMemoryLimit(ini_get('memory_limit')),
            'memory_usage_percent' => $this->calculateMemoryUsagePercent(),
            'load_average' => function_exists('sys_getloadavg') ? [
                '1min' => sys_getloadavg()[0],
                '5min' => sys_getloadavg()[1],
                '15min' => sys_getloadavg()[2],
            ] : null,
            'uptime_seconds' => $this->getUptime(),
        ];
    }

    protected function calculateErrorRate(): float
    {
        try {
            $redis = Redis::connection('default');
            $totalRequests = (int) $redis->get('metrics:requests:total') ?? 0;
            $totalErrors = (int) $redis->get('metrics:errors:total') ?? 0;

            if ($totalRequests === 0) {
                return 0.0;
            }

            return round(($totalErrors / $totalRequests) * 100, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    protected function calculateCacheHitRate(array $info): float
    {
        $hits = (int) ($info['keyspace_hits'] ?? 0);
        $misses = (int) ($info['keyspace_misses'] ?? 0);
        $total = $hits + $misses;

        if ($total === 0) {
            return 0.0;
        }

        return round(($hits / $total) * 100, 2);
    }

    protected function parseMemoryLimit(string $limit): int
    {
        $limit = strtoupper($limit);
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $value = (int) $limit;
        $unit = substr($limit, -1);
        
        if (is_numeric($unit)) {
            return $value;
        }
        
        switch ($unit) {
            case 'G':
                return $value * 1024;
            case 'M':
                return $value;
            case 'K':
                return $value / 1024;
            default:
                return $value;
        }
    }

    protected function calculateMemoryUsagePercent(): float
    {
        $usage = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        if ($limit === PHP_INT_MAX) {
            return 0.0;
        }
        
        return round(($usage / ($limit * 1024 * 1024)) * 100, 2);
    }

    protected function getUptime(): int
    {
        $startTime = defined('APP_START_TIME') ? APP_START_TIME : 0;
        
        if ($startTime === 0) {
            $redis = Redis::connection('default');
            $startTime = (int) $redis->get('app:start_time') ?? time();
            
            if (! defined('APP_START_TIME')) {
                $redis->setex('app:start_time', 86400, time());
            }
        }
        
        return time() - $startTime;
    }
}
