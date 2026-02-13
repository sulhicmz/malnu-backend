<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;

/**
 * Error Monitoring Service for tracking and analyzing application errors.
 *
 * Provides:
 * - Error frequency tracking
 * - Error categorization and metrics
 * - Alert threshold management
 * - Error trend analysis
 */
class ErrorMonitoringService
{
    private LoggingService $loggingService;

    /**
     * Error tracking storage (in-memory for current request).
     *
     * @var array<string, array>
     */
    private array $errorTracking = [];

    /**
     * Alert thresholds configuration.
     *
     * @var array<string, int>
     */
    private array $alertThresholds = [
        'critical' => 1,
        'error' => 5,
        'warning' => 10,
    ];

    /**
     * Critical error types that should trigger immediate alerts.
     *
     * @var array<class-string<Throwable>>
     */
    private array $criticalErrorTypes = [
        'DatabaseException',
        'AuthenticationException',
    ];

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Track an error occurrence.
     */
    public function trackError(Throwable $exception, array $context = []): void
    {
        $errorKey = $this->getErrorKey($exception);
        $errorType = $this->getErrorType($exception);

        // Initialize tracking for this error type
        if (! isset($this->errorTracking[$errorKey])) {
            $this->errorTracking[$errorKey] = [
                'count' => 0,
                'first_occurrence' => time(),
                'last_occurrence' => time(),
                'type' => $errorType,
                'message' => $exception->getMessage(),
                'class' => get_class($exception),
            ];
        }

        $this->errorTracking[$errorKey]['count']++;
        $this->errorTracking[$errorKey]['last_occurrence'] = time();

        // Log the error
        $this->logErrorTracking($errorKey, $this->errorTracking[$errorKey], $context);

        // Check if alert threshold is exceeded
        $this->checkAlertThreshold($errorKey, $this->errorTracking[$errorKey]);
    }

    /**
     * Get error metrics summary.
     */
    public function getErrorMetrics(): array
    {
        $totalErrors = 0;
        $errorsByType = [];
        $mostFrequentErrors = [];

        foreach ($this->errorTracking as $errorKey => $data) {
            $totalErrors += $data['count'];
            $type = $data['type'];

            if (! isset($errorsByType[$type])) {
                $errorsByType[$type] = 0;
            }
            $errorsByType[$type] += $data['count'];

            $mostFrequentErrors[$errorKey] = $data['count'];
        }

        // Sort by frequency
        arsort($mostFrequentErrors);

        return [
            'total_errors' => $totalErrors,
            'unique_errors' => count($this->errorTracking),
            'errors_by_type' => $errorsByType,
            'most_frequent' => array_slice($mostFrequentErrors, 0, 5, true),
            'tracking_since' => $this->getTrackingStartTime(),
        ];
    }

    /**
     * Get detailed error report.
     */
    public function getErrorReport(?string $errorKey = null): array
    {
        if ($errorKey !== null) {
            return $this->errorTracking[$errorKey] ?? [];
        }

        return $this->errorTracking;
    }

    /**
     * Reset error tracking.
     */
    public function resetTracking(): void
    {
        $this->errorTracking = [];
    }

    /**
     * Set alert threshold for an error type.
     */
    public function setAlertThreshold(string $errorType, int $threshold): void
    {
        $this->alertThresholds[$errorType] = $threshold;
    }

    /**
     * Check if an error type is critical.
     */
    public function isCriticalError(Throwable $exception): bool
    {
        $exceptionClass = get_class($exception);
        $shortClassName = substr($exceptionClass, strrpos($exceptionClass, '\\') + 1);

        return in_array($shortClassName, $this->criticalErrorTypes, true);
    }

    /**
     * Generate a unique key for an error.
     */
    private function getErrorKey(Throwable $exception): string
    {
        $class = get_class($exception);
        $file = $exception->getFile();
        $line = $exception->getLine();

        return md5("{$class}:{$file}:{$line}");
    }

    /**
     * Get error type classification.
     */
    private function getErrorType(Throwable $exception): string
    {
        if ($this->isCriticalError($exception)) {
            return 'critical';
        }

        $statusCode = 500;
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        }

        if ($statusCode >= 500) {
            return 'error';
        }
        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Log error tracking information.
     */
    private function logErrorTracking(string $errorKey, array $trackingData, array $context): void
    {
        $logContext = array_merge($context, [
            'error_key' => $errorKey,
            'error_count' => $trackingData['count'],
            'error_type' => $trackingData['type'],
            'first_occurrence' => date('c', $trackingData['first_occurrence']),
            'last_occurrence' => date('c', $trackingData['last_occurrence']),
        ]);

        if ($trackingData['count'] === 1) {
            $this->loggingService->info('New error type detected', $logContext);
        } else {
            $this->loggingService->warning(
                "Error occurred {$trackingData['count']} times",
                $logContext
            );
        }
    }

    /**
     * Check if alert threshold is exceeded.
     */
    private function checkAlertThreshold(string $errorKey, array $trackingData): void
    {
        $errorType = $trackingData['type'];
        $threshold = $this->alertThresholds[$errorType] ?? 10;

        if ($trackingData['count'] >= $threshold) {
            $this->triggerAlert($errorKey, $trackingData);
        }
    }

    /**
     * Trigger an alert for excessive errors.
     */
    private function triggerAlert(string $errorKey, array $trackingData): void
    {
        $alertContext = [
            'error_key' => $errorKey,
            'error_count' => $trackingData['count'],
            'error_type' => $trackingData['type'],
            'error_class' => $trackingData['class'],
            'error_message' => $trackingData['message'],
            'threshold' => $this->alertThresholds[$trackingData['type']] ?? 10,
            'alert_type' => 'error_threshold_exceeded',
        ];

        $this->loggingService->critical('Error threshold exceeded - Alert triggered', $alertContext);
    }

    /**
     * Get tracking start time.
     */
    private function getTrackingStartTime(): ?string
    {
        if (empty($this->errorTracking)) {
            return null;
        }

        $firstTimes = array_column($this->errorTracking, 'first_occurrence');
        $earliest = min($firstTimes);

        return date('c', $earliest);
    }
}
