<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RetryServiceInterface;
use Exception;
use PDOException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RetryService implements RetryServiceInterface
{
    private LoggerInterface $logger;

    private int $maxAttempts;

    private int $baseDelay;

    private float $exponentialFactor;

    private int $maxDelay;

    private array $retryableExceptions;

    public function __construct(
        ?int $maxAttempts = null,
        ?int $baseDelay = null,
        ?float $exponentialFactor = null,
        ?int $maxDelay = null,
        ?ContainerInterface $container = null
    ) {
        $this->maxAttempts = $maxAttempts ?? 3;
        $this->baseDelay = $baseDelay ?? 100;
        $this->exponentialFactor = $exponentialFactor ?? 2.0;
        $this->maxDelay = $maxDelay ?? 10000;

        if ($container !== null) {
            $this->logger = $container->get(LoggerInterface::class);
        } else {
            $this->logger = new \Psr\Log\NullLogger();
        }

        $this->retryableExceptions = [
            RuntimeException::class,
            PDOException::class,
            \Hyperf\Database\Exception\QueryException::class,
        ];
    }

    public function call(callable $callback, ?callable $onRetry = null)
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxAttempts; ++$attempt) {
            try {
                return $callback();
            } catch (Exception $e) {
                $lastException = $e;

                if (! $this->isRetryable($e) || $attempt >= $this->maxAttempts) {
                    throw $e;
                }

                $delay = $this->calculateDelay($attempt);

                $this->logger->warning('Retry attempt triggered', [
                    'attempt' => $attempt,
                    'max_attempts' => $this->maxAttempts,
                    'delay_ms' => $delay,
                    'exception' => $e->getMessage(),
                    'exception_class' => get_class($e),
                ]);

                if ($onRetry !== null) {
                    $onRetry($attempt, $e, $delay);
                }

                usleep($delay * 1000);
            }
        }

        throw $lastException;
    }

    public function callWithFallback(callable $callback, callable $fallback, ?callable $onRetry = null)
    {
        try {
            return $this->call($callback, $onRetry);
        } catch (Exception $e) {
            $this->logger->warning('All retry attempts failed, using fallback', [
                'exception' => $e->getMessage(),
            ]);

            return $fallback($e);
        }
    }

    public function addRetryableException(string $exceptionClass): self
    {
        if (! in_array($exceptionClass, $this->retryableExceptions)) {
            $this->retryableExceptions[] = $exceptionClass;
        }

        return $this;
    }

    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    public function setBaseDelay(int $baseDelay): self
    {
        $this->baseDelay = $baseDelay;
        return $this;
    }

    public function setExponentialFactor(float $factor): self
    {
        $this->exponentialFactor = $factor;
        return $this;
    }

    public function setMaxDelay(int $maxDelay): self
    {
        $this->maxDelay = $maxDelay;
        return $this;
    }

    protected function calculateDelay(int $attempt): int
    {
        $delay = $this->baseDelay * pow($this->exponentialFactor, $attempt - 1);
        $delay = (int) min($delay, $this->maxDelay);

        $jitter = (int) ($delay * 0.1);
        $delay += random_int(-$jitter, $jitter);

        return max($delay, 0);
    }

    protected function isRetryable(Exception $e): bool
    {
        foreach ($this->retryableExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return true;
            }
        }

        $message = $e->getMessage();

        $retryablePatterns = [
            'Connection refused',
            'Connection timed out',
            'Connection reset',
            'Host is down',
            'Network is unreachable',
            'Timeout expired',
            'Deadlock found',
            'Lock wait timeout',
            'SQLSTATE[HY000]',
            'SQLSTATE[08001]',
            'SQLSTATE[08004]',
            'SQLSTATE[08006]',
            'SQLSTATE[08007]',
            'SQLSTATE[08S01]',
        ];

        foreach ($retryablePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
