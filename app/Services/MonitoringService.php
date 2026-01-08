<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\DbConnection\Db;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class MonitoringService
{
    protected RedisFactory $redisFactory;
    protected LoggerInterface $logger;
    protected ContainerInterface $container;

    private array $errors = [];
    private array $metrics = [];
    private int $slowQueryThreshold = 1000;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->redisFactory = $container->get(RedisFactory::class);
        $this->logger = $container->get(LoggerInterface::class);
        $this->initializeMetrics();
    }

    private function initializeMetrics(): void
    {
        $this->metrics = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0,
            'slow_requests' => 0,
            'database_queries' => 0,
            'slow_database_queries' => 0,
        ];
    }

    public function getBasicHealth(): array
    {
        $status = 'healthy';
        $checks = [];

        $checks['app'] = $this->checkAppHealth();
        $checks['database'] = $this->checkDatabaseHealth();
        $checks['redis'] = $this->checkRedisHealth();

        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                $status = 'unhealthy';
            }
        }

        return [
            'status' => $status,
            'timestamp' => date('c'),
            'checks' => $checks,
        ];
    }

    public function getDetailedHealth(): array
    {
        $basic = $this->getBasicHealth();
        $basic['system'] = $this->getSystemHealth();
        $basic['performance'] = $this->getPerformanceMetrics();
        $basic['uptime'] = $this->getUptime();

        return $basic;
    }

    public function getMetrics(): array
    {
        $metrics = $this->metrics;
        $metrics['average_response_time'] = $metrics['total_requests'] > 0
            ? round($metrics['total_response_time'] / $metrics['total_requests'], 2)
            : 0;
        $metrics['error_rate'] = $metrics['total_requests'] > 0
            ? round(($metrics['failed_requests'] / $metrics['total_requests']) * 100, 2)
            : 0;
        $metrics['success_rate'] = $metrics['total_requests'] > 0
            ? round(($metrics['successful_requests'] / $metrics['total_requests']) * 100, 2)
            : 0;

        return [
            'metrics' => $metrics,
            'thresholds' => [
                'max_response_time' => 200,
                'max_error_rate' => 1,
                'min_success_rate' => 99,
                'slow_query_threshold' => $this->slowQueryThreshold,
            ],
            'status' => $this->evaluateMetrics($metrics),
        ];
    }

    public function getRecentErrors(): array
    {
        $redis = $this->redisFactory->get();
        $errorKey = 'monitoring:errors:recent';
        $errors = $redis->lRange($errorKey, 0, 99);

        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[] = json_decode($error, true);
        }

        return [
            'total' => count($formattedErrors),
            'errors' => $formattedErrors,
            'summary' => $this->getErrorSummary($formattedErrors),
        ];
    }

    public function trackRequest(array $data): void
    {
        $this->metrics['total_requests']++;
        $this->metrics['total_response_time'] += $data['response_time'] ?? 0;

        if ($data['status'] >= 500) {
            $this->metrics['failed_requests']++;
            if (isset($data['error'])) {
                $this->trackError($data['error'], $data);
            }
        } else {
            $this->metrics['successful_requests']++;
        }

        if (($data['response_time'] ?? 0) > 200) {
            $this->metrics['slow_requests']++;
        }
    }

    public function trackError(string $error, array $context = []): void
    {
        $errorData = [
            'message' => $error,
            'timestamp' => date('c'),
            'context' => $context,
            'type' => $this->classifyError($error),
        ];

        $this->errors[] = $errorData;

        $redis = $this->redisFactory->get();
        $redis->lPush('monitoring:errors:recent', json_encode($errorData));
        $redis->lTrim('monitoring:errors:recent', 0, 999);
        $redis->expire('monitoring:errors:recent', 86400);

        $this->logger->error($error, $context);
    }

    public function trackDatabaseQuery(int $duration): void
    {
        $this->metrics['database_queries']++;
        if ($duration > $this->slowQueryThreshold) {
            $this->metrics['slow_database_queries']++;
            $this->logger->warning('Slow database query detected', [
                'duration_ms' => $duration,
                'threshold_ms' => $this->slowQueryThreshold,
            ]);
        }
    }

    private function checkAppHealth(): array
    {
        return [
            'status' => 'ok',
            'message' => 'Application is running',
        ];
    }

    private function checkDatabaseHealth(): array
    {
        try {
            Db::select('SELECT 1');
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkRedisHealth(): array
    {
        try {
            $redis = $this->redisFactory->get();
            $redis->ping();
            return [
                'status' => 'ok',
                'message' => 'Redis connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function getSystemHealth(): array
    {
        return [
            'memory' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'php_version' => PHP_VERSION,
            'server_time' => date('c'),
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time_ms' => $this->metrics['total_requests'] > 0
                ? round($this->metrics['total_response_time'] / $this->metrics['total_requests'], 2)
                : 0,
            'slow_requests_count' => $this->metrics['slow_requests'],
            'slow_database_queries' => $this->metrics['slow_database_queries'],
        ];
    }

    private function getUptime(): array
    {
        $uptime = time() - $this->getStartTime();

        return [
            'seconds' => $uptime,
            'human_readable' => $this->formatUptime($uptime),
        ];
    }

    private function getStartTime(): int
    {
        $redis = $this->redisFactory->get();
        $startTime = $redis->get('monitoring:started_at');

        if (!$startTime) {
            $startTime = time();
            $redis->set('monitoring:started_at', $startTime);
        }

        return (int) $startTime;
    }

    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dd %dh %dm %ds', $days, $hours, $minutes, $secs);
    }

    private function evaluateMetrics(array $metrics): string
    {
        if ($metrics['error_rate'] > 1) {
            return 'critical';
        }

        if ($metrics['average_response_time'] > 200) {
            return 'degraded';
        }

        return 'healthy';
    }

    private function classifyError(string $error): string
    {
        if (str_contains($error, 'SQL') || str_contains($error, 'database')) {
            return 'database';
        }

        if (str_contains($error, 'Connection') || str_contains($error, 'timeout')) {
            return 'network';
        }

        if (str_contains($error, 'Authentication') || str_contains($error, 'Unauthorized')) {
            return 'authentication';
        }

        if (str_contains($error, 'validation') || str_contains($error, 'Invalid')) {
            return 'validation';
        }

        return 'general';
    }

    private function getErrorSummary(array $errors): array
    {
        $summary = [
            'by_type' => [],
            'by_message' => [],
        ];

        foreach ($errors as $error) {
            $type = $error['type'];
            $message = $error['message'];

            if (!isset($summary['by_type'][$type])) {
                $summary['by_type'][$type] = 0;
            }
            $summary['by_type'][$type]++;

            $shortMessage = substr($message, 0, 100);
            if (!isset($summary['by_message'][$shortMessage])) {
                $summary['by_message'][$shortMessage] = 0;
            }
            $summary['by_message'][$shortMessage]++;
        }

        return $summary;
    }
}
