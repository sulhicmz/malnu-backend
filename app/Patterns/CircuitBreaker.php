<?php

declare(strict_types=1);

namespace App\Patterns;

use Exception;
use Hyperf\Cache\Cache;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';

    private const STATE_OPEN = 'open';

    private const STATE_HALF_OPEN = 'half_open';

    private Cache $cache;

    private string $serviceName;

    private int $failureThreshold;

    private int $timeoutSeconds;

    private int $halfOpenAttempts;

    private string $keyPrefix = 'circuit_breaker:';

    public function __construct(
        Cache $cache,
        string $serviceName,
        int $failureThreshold = 5,
        int $timeoutSeconds = 60,
        int $halfOpenAttempts = 1
    ) {
        $this->cache = $cache;
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->halfOpenAttempts = $halfOpenAttempts;
    }

    public function call(callable $operation)
    {
        $state = $this->getState();

        if ($state === self::STATE_OPEN) {
            throw new CircuitBreakerOpenException(
                "Circuit breaker is OPEN for service: {$this->serviceName}"
            );
        }

        try {
            $result = $operation();
            $this->onSuccess();
            return $result;
        } catch (Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }

    public function getMetrics(): array
    {
        return [
            'service' => $this->serviceName,
            'state' => $this->getState(),
            'failures' => (int) $this->cache->get($this->getKey('failures'), 0),
            'failure_threshold' => $this->failureThreshold,
            'timeout_seconds' => $this->timeoutSeconds,
        ];
    }

    public function reset(): void
    {
        $this->cache->deleteMultiple([
            $this->getKey('state'),
            $this->getKey('failures'),
            $this->getKey('last_failure_time'),
            $this->getKey('half_open_attempts'),
        ]);
    }

    private function getState(): string
    {
        $stateKey = $this->getKey('state');
        $state = $this->cache->get($stateKey, self::STATE_CLOSED);

        if ($state === self::STATE_OPEN) {
            $lastFailureTimeKey = $this->getKey('last_failure_time');
            $lastFailureTime = $this->cache->get($lastFailureTimeKey);

            if ($lastFailureTime && (time() - $lastFailureTime) >= $this->timeoutSeconds) {
                $this->transitionTo(self::STATE_HALF_OPEN);
                return self::STATE_HALF_OPEN;
            }
        }

        return $state;
    }

    private function onSuccess(): void
    {
        $failuresKey = $this->getKey('failures');
        $this->cache->delete($failuresKey);

        $state = $this->getState();
        if ($state === self::STATE_HALF_OPEN) {
            $this->transitionTo(self::STATE_CLOSED);
        }
    }

    private function onFailure(): void
    {
        $failuresKey = $this->getKey('failures');
        $failures = (int) $this->cache->get($failuresKey, 0);
        $this->cache->set($failuresKey, $failures + 1, $this->timeoutSeconds * 2);

        if ($failures + 1 >= $this->failureThreshold) {
            $this->transitionTo(self::STATE_OPEN);
        }
    }

    private function transitionTo(string $state): void
    {
        $stateKey = $this->getKey('state');
        $this->cache->set($stateKey, $state, $this->timeoutSeconds * 2);

        if ($state === self::STATE_OPEN) {
            $lastFailureTimeKey = $this->getKey('last_failure_time');
            $this->cache->set($lastFailureTimeKey, time(), $this->timeoutSeconds * 2);
        }

        if ($state === self::STATE_HALF_OPEN) {
            $attemptsKey = $this->getKey('half_open_attempts');
            $this->cache->set($attemptsKey, 0, $this->timeoutSeconds * 2);
        }
    }

    private function getKey(string $suffix): string
    {
        return $this->keyPrefix . $this->serviceName . ':' . $suffix;
    }
}
