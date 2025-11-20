<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hyperf\Utils\Context;
use Hyperf\DbConnection\Db;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PerformanceMonitoringMiddleware
{
    public function process(ServerRequestInterface $request, Closure $handler): ResponseInterface
    {
        // Start measuring execution time
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Track initial query count
        $initialQueryCount = $this->getQueryCount();
        
        // Process the request
        $response = $handler($request);
        
        // Calculate performance metrics
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage(true) - $startMemory;
        $finalQueryCount = $this->getQueryCount();
        $queryCount = $finalQueryCount - $initialQueryCount;
        
        // Log performance metrics if they exceed thresholds
        if ($executionTime > 0.5 || $queryCount > 10) { // 500ms or more than 10 queries
            $this->logPerformanceMetrics([
                'url' => $request->getUri()->__toString(),
                'method' => $request->getMethod(),
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage,
                'query_count' => $queryCount,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Add performance headers to response if in debug mode
        if (env('APP_DEBUG', false)) {
            $response = $response->withHeader('X-Execution-Time', number_format($executionTime * 1000, 2) . 'ms');
            $response = $response->withHeader('X-Query-Count', $queryCount);
            $response = $response->withHeader('X-Memory-Usage', $this->formatBytes($memoryUsage));
        }
        
        return $response;
    }
    
    /**
     * Get current query count from the database connection
     */
    private function getQueryCount(): int
    {
        // This is a simplified approach - in a real implementation, 
        // you would need to track queries through a query logger
        return 0;
    }
    
    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(array $metrics): void
    {
        $logMessage = sprintf(
            '[PERFORMANCE] URL: %s | Method: %s | Time: %sms | Queries: %d | Memory: %s | %s',
            $metrics['url'],
            $metrics['method'],
            number_format($metrics['execution_time'] * 1000, 2),
            $metrics['query_count'],
            $this->formatBytes($metrics['memory_usage']),
            $metrics['timestamp']
        );
        
        // Log to file or external service
        error_log($logMessage);
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}