<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;

class ErrorLoggingService
{
    private LoggerInterface $logger;
    private array $errorContext = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logError(string $message, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'error';
        $this->logger->error($message, $context);
    }

    public function logWarning(string $message, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'warning';
        $this->logger->warning($message, $context);
    }

    public function logInfo(string $message, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'info';
        $this->logger->info($message, $context);
    }

    public function logDebug(string $message, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'debug';
        $this->logger->debug($message, $context);
    }

    public function logSecurityEvent(string $event, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'security';
        $context['event'] = $event;

        $this->logger->warning("[SECURITY] {$event}", $context);
    }

    public function logPerformance(string $endpoint, float $duration, int $statusCode, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'performance';
        $context['endpoint'] = $endpoint;
        $context['duration_ms'] = round($duration * 1000, 2);
        $context['status_code'] = $statusCode;

        $isSlow = $duration > (float) env('PERFORMANCE_SLOW_THRESHOLD', 2.0);

        if ($isSlow) {
            $this->logger->warning("[PERFORMANCE] Slow request detected: {$endpoint}", $context);
        } else {
            $this->logger->info("[PERFORMANCE] {$endpoint}", $context);
        }
    }

    public function logAudit(string $action, array $context = []): void
    {
        $context['timestamp'] = date('c');
        $context['type'] = 'audit';
        $context['action'] = $action;

        $this->logger->info("[AUDIT] {$action}", $context);
    }

    public function setErrorContext(array $context): void
    {
        $this->errorContext = array_merge($this->errorContext, $context);
    }

    public function getErrorContext(): array
    {
        return $this->errorContext;
    }

    public function clearErrorContext(): void
    {
        $this->errorContext = [];
    }
}
