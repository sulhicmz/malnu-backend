<?php

declare(strict_types=1);

namespace App\Patterns;

use Exception;
use Psr\Log\LoggerInterface;

class RetryWithBackoff
{
    private int $maxAttempts;

    private int $initialDelayMs;

    private float $backoffMultiplier;

    private int $maxDelayMs;

    private LoggerInterface $logger;

    public function __construct(
        int $maxAttempts = 3,
        int $initialDelayMs = 100,
        float $backoffMultiplier = 2.0,
        int $maxDelayMs = 5000,
        ?LoggerInterface $logger = null
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->initialDelayMs = $initialDelayMs;
        $this->backoffMultiplier = $backoffMultiplier;
        $this->maxDelayMs = $maxDelayMs;
        $this->logger = $logger ?? \Hyperf\Support\make(LoggerInterface::class);
    }

    public function execute(callable $operation, array $retryableExceptions = [])
    {
        $lastException = null;
        $delayMs = $this->initialDelayMs;

        for ($attempt = 1; $attempt <= $this->maxAttempts; ++$attempt) {
            try {
                return $operation();
            } catch (Exception $e) {
                $lastException = $e;

                if (! $this->shouldRetry($e, $retryableExceptions)) {
                    $this->logger->warning('Retry attempt failed - non-retryable exception', [
                        'attempt' => $attempt,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }

                if ($attempt < $this->maxAttempts) {
                    $this->logger->warning('Retry attempt failed - will retry', [
                        'attempt' => $attempt,
                        'max_attempts' => $this->maxAttempts,
                        'delay_ms' => $delayMs,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                    ]);

                    $this->sleepMs($delayMs);
                    $delayMs = min((int) ($delayMs * $this->backoffMultiplier), $this->maxDelayMs);
                }
            }
        }

        $this->logger->error('All retry attempts failed', [
            'max_attempts' => $this->maxAttempts,
            'exception' => get_class($lastException),
            'message' => $lastException->getMessage(),
        ]);

        throw $lastException;
    }

    private function shouldRetry(Exception $e, array $retryableExceptions): bool
    {
        if (empty($retryableExceptions)) {
            return true;
        }

        foreach ($retryableExceptions as $retryableException) {
            if ($e instanceof $retryableException) {
                return true;
            }
        }

        return false;
    }

    private function sleepMs(int $milliseconds): void
    {
        $microseconds = $milliseconds * 1000;
        usleep($microseconds);
    }
}
