<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Config\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class MonitoringService
{
    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    protected array $alertHistory = [];

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(LoggerFactory::class)->get('monitoring');
    }

    public function checkAlerts(): void
    {
        if (! $this->config->get('monitoring.alerting.enabled', false)) {
            return;
        }

        $this->checkErrorRate();
        $this->checkResponseTime();
        $this->checkDiskUsage();
        $this->checkMemoryUsage();
    }

    protected function checkErrorRate(): void
    {
        try {
            $errorRate = $this->getErrorRate();
            $threshold = $this->config->get('monitoring.alerting.error_rate_threshold', 5);

            if ($errorRate > $threshold) {
                $this->sendAlert('high_error_rate', [
                    'error_rate' => $errorRate,
                    'threshold' => $threshold,
                    'severity' => $errorRate > $threshold * 2 ? 'critical' : 'warning',
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to check error rate', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function checkResponseTime(): void
    {
        try {
            $avgResponseTime = $this->getAverageResponseTime();
            $threshold = $this->config->get('monitoring.alerting.response_time_threshold', 1000);

            if ($avgResponseTime > $threshold) {
                $this->sendAlert('high_response_time', [
                    'avg_response_time_ms' => $avgResponseTime,
                    'threshold' => $threshold,
                    'severity' => $avgResponseTime > $threshold * 2 ? 'critical' : 'warning',
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to check response time', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function checkDiskUsage(): void
    {
        try {
            $diskUsage = $this->getDiskUsage();
            $threshold = $this->config->get('monitoring.alerting.disk_usage_threshold', 90);

            if ($diskUsage > $threshold) {
                $this->sendAlert('high_disk_usage', [
                    'disk_usage_percent' => $diskUsage,
                    'threshold' => $threshold,
                    'severity' => $diskUsage > 95 ? 'critical' : 'warning',
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to check disk usage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function checkMemoryUsage(): void
    {
        try {
            $memoryUsage = $this->getMemoryUsage();
            $threshold = $this->config->get('monitoring.alerting.memory_usage_threshold', 85);

            if ($memoryUsage > $threshold) {
                $this->sendAlert('high_memory_usage', [
                    'memory_usage_percent' => $memoryUsage,
                    'threshold' => $threshold,
                    'severity' => $memoryUsage > 95 ? 'critical' : 'warning',
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to check memory usage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendAlert(string $alertType, array $data): void
    {
        $alertKey = $alertType.':'.md5(json_encode($data));

        if (isset($this->alertHistory[$alertKey])) {
            $lastAlert = $this->alertHistory[$alertKey];

            if (time() - $lastAlert['time'] < 300) {
                return;
            }
        }

        $severity = $data['severity'] ?? 'info';
        $message = $this->formatAlertMessage($alertType, $data);

        $this->logger->log($severity === 'critical' ? 'critical' : 'warning', $message, [
            'alert_type' => $alertType,
            'data' => $data,
        ]);

        $this->sendEmailAlert($alertType, $message, $data);
        $this->sendSlackAlert($alertType, $message, $data);

        $this->alertHistory[$alertKey] = [
            'time' => time(),
            'data' => $data,
        ];
    }

    protected function formatAlertMessage(string $alertType, array $data): string
    {
        $messages = [
            'high_error_rate' => "High error rate detected: {$data['error_rate']}% (threshold: {$data['threshold']}%)",
            'high_response_time' => "High response time detected: {$data['avg_response_time_ms']}ms (threshold: {$data['threshold']}ms)",
            'high_disk_usage' => "High disk usage detected: {$data['disk_usage_percent']}% (threshold: {$data['threshold']}%)",
            'high_memory_usage' => "High memory usage detected: {$data['memory_usage_percent']}% (threshold: {$data['threshold']}%)",
        ];

        return $messages[$alertType] ?? "Monitoring alert: {$alertType}";
    }

    protected function sendEmailAlert(string $alertType, string $message, array $data): void
    {
        $email = $this->config->get('monitoring.alerting.channels.email');

        if (! $email) {
            return;
        }

        $this->logger->info('Email alert would be sent', [
            'email' => $email,
            'alert' => $message,
        ]);
    }

    protected function sendSlackAlert(string $alertType, string $message, array $data): void
    {
        $webhook = $this->config->get('monitoring.alerting.channels.slack');

        if (! $webhook) {
            return;
        }

        $this->logger->info('Slack alert would be sent', [
            'webhook' => $webhook,
            'alert' => $message,
        ]);
    }

    protected function getErrorRate(): float
    {
        return 0.0;
    }

    protected function getAverageResponseTime(): float
    {
        return 0.0;
    }

    protected function getDiskUsage(): float
    {
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');

        if ($diskFree === false || $diskTotal === false || $diskTotal === 0) {
            return 0.0;
        }

        return round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
    }

    protected function getMemoryUsage(): float
    {
        $usage = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));

        if ($limit === PHP_INT_MAX) {
            return 0.0;
        }

        return round(($usage / ($limit * 1024 * 1024)) * 100, 2);
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
}
