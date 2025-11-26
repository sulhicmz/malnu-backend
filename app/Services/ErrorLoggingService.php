<?php

declare(strict_types=1);

namespace App\Services;

class ErrorLoggingService
{
    /**
     * Log a general error with context
     */
    public function logError(string $message, array $context = [], string $level = 'error'): void
    {
        $logEntry = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        
        // Write to error log file
        $this->writeToLog('error', $logEntry);
    }

    /**
     * Log an exception with detailed context
     */
    public function logException(\Throwable $exception, array $requestContext = []): void
    {
        // Determine if we're in debug mode to include more details
        $debugMode = $this->isDebugMode();
        
        $logEntry = [
            'type' => 'exception',
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // Only include trace in debug mode for security
        if ($debugMode) {
            $logEntry['trace'] = $exception->getTraceAsString();
        }

        $logEntry['request_context'] = $requestContext;

        $this->writeToLog('exception', $logEntry);
    }

    /**
     * Log a security-related event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $logEntry = [
            'type' => 'security',
            'event' => $event,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->writeToLog('security', $logEntry);
    }

    /**
     * Log an audit trail event
     */
    public function logAuditEvent(string $event, array $context = []): void
    {
        $logEntry = [
            'type' => 'audit',
            'event' => $event,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->writeToLog('audit', $logEntry);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $endpoint, float $executionTime, array $context = []): void
    {
        $logEntry = [
            'type' => 'performance',
            'endpoint' => $endpoint,
            'execution_time_ms' => $executionTime * 1000,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->writeToLog('performance', $logEntry);
    }

    /**
     * Write log entry to appropriate log file
     */
    private function writeToLog(string $logType, array $logEntry): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/' . $logType . '.log';
        
        // Format the log entry as JSON
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Write to log file
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if the application is in debug mode
     */
    private function isDebugMode(): bool
    {
        // Check environment variable for debug mode
        $debug = $_ENV['APP_DEBUG'] ?? $_ENV['app.debug'] ?? 'false';
        return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
    }
}