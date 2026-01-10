<?php

declare(strict_types=1);

namespace App\Services;

class PerformanceMonitoringService
{
    private array $metrics = [
        'request_count' => 0,
        'total_response_time' => 0,
        'slow_requests' => 0,
        'avg_response_time' => 0,
    ];

    private array $slowThresholds = [
        'default' => 200,
        'database_query' => 100,
    ];

    public function __construct()
    {
        $this->slowThresholds['default'] = (int)($_ENV['PERFORMANCE_SLOW_THRESHOLD'] ?? 200);
        $this->slowThresholds['database_query'] = (int)($_ENV['PERFORMANCE_DB_SLOW_THRESHOLD'] ?? 100);
    }

    public function recordRequest(float $responseTime, bool $isSlow = false): void
    {
        $this->metrics['request_count']++;
        $this->metrics['total_response_time'] += $responseTime;

        if ($isSlow || $responseTime > $this->slowThresholds['default']) {
            $this->metrics['slow_requests']++;
        }

        $this->updateAverage();
    }

    public function recordDatabaseQuery(float $queryTime, bool $isSlow = false): void
    {
        if ($isSlow || $queryTime > $this->slowThresholds['database_query']) {
            $this->recordSlowQuery($queryTime);
        }
    }

    private function recordSlowQuery(float $queryTime): void
    {
        if (!isset($this->metrics['slow_queries'])) {
            $this->metrics['slow_queries'] = [];
        }

        $this->metrics['slow_queries'][] = [
            'time' => $queryTime,
            'timestamp' => time(),
        ];

        if (count($this->metrics['slow_queries']) > 100) {
            array_splice($this->metrics['slow_queries'], 0, count($this->metrics['slow_queries']) - 100);
        }
    }

    public function getMetrics(): array
    {
        $this->updateAverage();

        return [
            'request_count' => $this->metrics['request_count'],
            'total_response_time' => $this->metrics['total_response_time'],
            'avg_response_time' => $this->metrics['avg_response_time'],
            'slow_requests' => $this->metrics['slow_requests'],
            'slow_rate' => $this->calculateSlowRate(),
            'slow_queries' => $this->metrics['slow_queries'] ?? [],
        ];
    }

    public function resetMetrics(): void
    {
        $this->metrics = [
            'request_count' => 0,
            'total_response_time' => 0,
            'slow_requests' => 0,
            'avg_response_time' => 0,
            'slow_queries' => [],
        ];
    }

    private function updateAverage(): void
    {
        if ($this->metrics['request_count'] > 0) {
            $this->metrics['avg_response_time'] = round(
                $this->metrics['total_response_time'] / $this->metrics['request_count'],
                2
            );
        }
    }

    private function calculateSlowRate(): float
    {
        if ($this->metrics['request_count'] === 0) {
            return 0.0;
        }

        return round(
            ($this->metrics['slow_requests'] / $this->metrics['request_count']) * 100,
            2
        );
    }

    public function isSlow(float $responseTime, string $category = 'default'): bool
    {
        $threshold = $this->slowThresholds[$category] ?? $this->slowThresholds['default'];
        return $responseTime > $threshold;
    }

    public function getPerformanceStatus(): string
    {
        $metrics = $this->getMetrics();
        $slowRate = $metrics['slow_rate'];
        $avgResponseTime = $metrics['avg_response_time'];

        if ($slowRate < 5 && $avgResponseTime < 100) {
            return 'excellent';
        }

        if ($slowRate < 10 && $avgResponseTime < 200) {
            return 'good';
        }

        if ($slowRate < 20 && $avgResponseTime < 300) {
            return 'degraded';
        }

        return 'critical';
    }

    public function getRecommendations(): array
    {
        $metrics = $this->getMetrics();
        $recommendations = [];

        if ($metrics['slow_rate'] > 20) {
            $recommendations[] = 'High slow request rate detected. Consider adding caching for frequently accessed data.';
        }

        if ($metrics['avg_response_time'] > 300) {
            $recommendations[] = 'High average response time. Review database queries and optimize slow queries.';
        }

        if (count($metrics['slow_queries']) > 10) {
            $recommendations[] = 'Multiple slow database queries detected. Add indexes to frequently queried fields.';
        }

        if ($metrics['slow_requests'] > 0) {
            $recommendations[] = sprintf(
                '%d slow requests detected. Use X-Slow-Request header to identify slow endpoints.',
                $metrics['slow_requests']
            );
        }

        return $recommendations;
    }

    public function logMetrics(string $message = ''): void
    {
        $metrics = $this->getMetrics();

        $logMessage = sprintf(
            "[PERFORMANCE] %s Requests: %d | Avg: %.2fms | Slow: %d (%.2f%%) | Status: %s",
            $message ? $message . ' ' : '',
            $metrics['request_count'],
            $metrics['avg_response_time'],
            $metrics['slow_requests'],
            $metrics['slow_rate'],
            $this->getPerformanceStatus()
        );

        error_log($logMessage);
    }
}
