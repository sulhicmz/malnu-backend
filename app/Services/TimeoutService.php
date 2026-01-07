<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TimeoutService
{
    private LoggerInterface $logger;

    private int $defaultTimeout;

    public function __construct(
        ?int $defaultTimeout = null,
        ?ContainerInterface $container = null
    ) {
        $this->defaultTimeout = $defaultTimeout ?? (int) env('EXTERNAL_SERVICE_TIMEOUT', 30);

        if ($container !== null) {
            $this->logger = $container->get(LoggerInterface::class);
        } else {
            $this->logger = new \Psr\Log\NullLogger();
        }
    }

    public function call(callable $callback, ?int $timeoutMs = null, ?callable $onTimeout = null)
    {
        $timeoutMs = $timeoutMs ?? ($this->defaultTimeout * 1000);
        $startTime = microtime(true);

        try {
            $result = $callback();
            $elapsed = (int) ((microtime(true) - $startTime) * 1000);

            if ($elapsed > $timeoutMs * 0.8) {
                $this->logger->warning('Operation nearing timeout', [
                    'elapsed_ms' => $elapsed,
                    'timeout_ms' => $timeoutMs,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $elapsed = (int) ((microtime(true) - $startTime) * 1000);

            if ($elapsed >= $timeoutMs) {
                $this->logger->error('Operation timed out', [
                    'elapsed_ms' => $elapsed,
                    'timeout_ms' => $timeoutMs,
                    'exception' => $e->getMessage(),
                ]);

                if ($onTimeout !== null) {
                    return $onTimeout($elapsed, $e);
                }

                throw new Exception('Operation timed out after ' . $elapsed . 'ms', 0, $e);
            }

            throw $e;
        }
    }

    public function callWithFallback(callable $callback, callable $fallback, ?int $timeoutMs = null)
    {
        try {
            return $this->call($callback, $timeoutMs, function ($elapsed, $exception) use ($fallback) {
                $this->logger->info('Timeout occurred, using fallback', [
                    'elapsed_ms' => $elapsed,
                ]);
                return $fallback($exception);
            });
        } catch (Exception $e) {
            $this->logger->warning('Operation failed, using fallback', [
                'exception' => $e->getMessage(),
            ]);
            return $fallback($e);
        }
    }

    public function setTimeout(int $timeoutSeconds): self
    {
        $this->defaultTimeout = $timeoutSeconds;
        return $this;
    }
}
