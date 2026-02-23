<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Psr\Log\LoggerInterface;

class RetryService
{
    private LoggerInterface $logger;

    private array $config;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = config('retry', [
            'max_attempts' => 3,
            'initial_delay' => 1000,
            'max_delay' => 10000,
            'multiplier' => 2,
            'jitter' => true,
        ]);
    }

    public function execute(callable $operation, array $options = []): mixed
    {
        $maxAttempts = $options['max_attempts'] ?? $this->config['max_attempts'];
        $initialDelay = $options['initial_delay'] ?? $this->config['initial_delay'];
        $maxDelay = $options['max_delay'] ?? $this->config['max_delay'];
        $multiplier = $options['multiplier'] ?? $this->config['multiplier'];
        $jitter = $options['jitter'] ?? $this->config['jitter'];
        $retryOn = $options['retry_on'] ?? null;
        $operationName = $options['operation_name'] ?? 'unknown';

        $attempt = 0;
        $lastException = null;
        $delay = $initialDelay;

        while ($attempt < $maxAttempts) {
            ++$attempt;

            try {
                $result = $operation();

                if ($attempt > 1) {
                    $this->logger->info('Operation succeeded after retry', [
                        'operation' => $operationName,
                        'attempt' => $attempt,
                    ]);
                }

                return $result;
            } catch (Exception $e) {
                $lastException = $e;
                $shouldRetry = $retryOn === null || $this->shouldRetry($e, $retryOn);

                if (! $shouldRetry || $attempt >= $maxAttempts) {
                    $this->logger->error('Operation failed permanently', [
                        'operation' => $operationName,
                        'attempts' => $attempt,
                        'error' => $e->getMessage(),
                        'class' => get_class($e),
                    ]);

                    throw $e;
                }

                $this->logger->warning('Operation failed, retrying', [
                    'operation' => $operationName,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delay,
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                ]);

                usleep($this->calculateDelay($delay, $jitter) * 1000);

                $delay = min($delay * $multiplier, $maxDelay);
            }
        }

        throw $lastException;
    }

    public function executeWithCircuitBreaker(
        string $service,
        callable $operation,
        CircuitBreakerService $circuitBreaker,
        ?callable $fallback = null,
        array $retryOptions = []
    ): mixed {
        return $circuitBreaker->call(
            $service,
            function () use ($operation, $retryOptions) {
                return $this->execute($operation, $retryOptions);
            },
            $fallback
        );
    }

    private function shouldRetry(Exception $exception, array $retryOn): bool
    {
        if (in_array('*', $retryOn, true)) {
            return true;
        }

        $exceptionClass = get_class($exception);

        foreach ($retryOn as $pattern) {
            if ($pattern === $exceptionClass) {
                return true;
            }

            if (is_subclass_of($exceptionClass, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function calculateDelay(int $delay, bool $jitter): int
    {
        if (! $jitter) {
            return $delay;
        }

        $jitterAmount = (int) ($delay * 0.1);
        $jitterValue = random_int(-$jitterAmount, $jitterAmount);

        return max(0, $delay + $jitterValue);
    }
}
