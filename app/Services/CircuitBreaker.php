<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CircuitBreakerInterface;
use App\Enums\ErrorCode;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CircuitBreaker implements CircuitBreakerInterface
{
    public const STATE_CLOSED = 'closed';

    public const STATE_OPEN = 'open';

    public const STATE_HALF_OPEN = 'half_open';

    private LoggerInterface $logger;

    private CacheService $cache;

    private string $service;

    private int $failureThreshold;

    private int $recoveryTimeout;

    private int $successThreshold;

    private string $state;

    private array $failureCount;

    private array $successCount;

    private array $lastFailureTime;

    public function __construct(
        string $service,
        ?int $failureThreshold = null,
        ?int $recoveryTimeout = null,
        ?int $successThreshold = null,
        ?ContainerInterface $container = null
    ) {
        $this->cache = new CacheService();

        if ($container !== null) {
            $this->logger = $container->get(LoggerInterface::class);
        } else {
            $this->logger = new \Psr\Log\NullLogger();
        }

        $this->service = $service;
        $this->failureThreshold = $failureThreshold ?? (int) env('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 5);
        $this->recoveryTimeout = $recoveryTimeout ?? (int) env('CIRCUIT_BREAKER_RECOVERY_TIMEOUT', 60);
        $this->successThreshold = $successThreshold ?? (int) env('CIRCUIT_BREAKER_SUCCESS_THRESHOLD', 2);

        $this->loadState();
    }

    public function call(callable $callback, ?callable $fallback = null)
    {
        if (! $this->allowRequest()) {
            $this->logger->warning('Circuit breaker is OPEN for service', [
                'service' => $this->service,
                'state' => $this->state,
            ]);

            if ($fallback !== null) {
                return $fallback();
            }

            throw new Exception(
                "Service {$this->service} is unavailable due to circuit breaker",
                ErrorCode::getStatusCode(ErrorCode::SERVICE_UNAVAILABLE)
            );
        }

        try {
            $result = $callback();
            $this->onSuccess();
            return $result;
        } catch (Exception $e) {
            $this->onFailure();

            if ($fallback !== null) {
                $this->logger->warning('Circuit breaker: Fallback triggered', [
                    'service' => $this->service,
                    'exception' => $e->getMessage(),
                ]);
                return $fallback();
            }

            throw $e;
        }
    }

    public function reset(): void
    {
        $this->state = self::STATE_CLOSED;
        $this->failureCount[$this->service] = 0;
        $this->successCount[$this->service] = 0;
        $this->lastFailureTime[$this->service] = 0;
        $this->saveState();
    }

    public function getState(): string
    {
        $this->loadState();
        return $this->state;
    }

    public function getFailureCount(): int
    {
        $this->loadState();
        return $this->failureCount[$this->service] ?? 0;
    }

    public function getSuccessCount(): int
    {
        $this->loadState();
        return $this->successCount[$this->service] ?? 0;
    }

    public function getLastFailureTime(): int
    {
        $this->loadState();
        return $this->lastFailureTime[$this->service] ?? 0;
    }

    protected function allowRequest(): bool
    {
        $this->loadState();

        if ($this->state === self::STATE_CLOSED) {
            return true;
        }

        if ($this->state === self::STATE_OPEN) {
            $timeSinceLastFailure = time() - ($this->lastFailureTime[$this->service] ?? 0);

            if ($timeSinceLastFailure >= $this->recoveryTimeout) {
                $this->transitionTo(self::STATE_HALF_OPEN);
                return true;
            }

            return false;
        }

        if ($this->state === self::STATE_HALF_OPEN) {
            return true;
        }

        return false;
    }

    protected function onSuccess(): void
    {
        $this->loadState();

        $this->successCount[$this->service] = ($this->successCount[$this->service] ?? 0) + 1;

        if ($this->state === self::STATE_HALF_OPEN) {
            if ($this->successCount[$this->service] >= $this->successThreshold) {
                $this->transitionTo(self::STATE_CLOSED);
            }
        }

        $this->saveState();
    }

    protected function onFailure(): void
    {
        $this->loadState();

        $this->failureCount[$this->service] = ($this->failureCount[$this->service] ?? 0) + 1;
        $this->lastFailureTime[$this->service] = time();
        $this->successCount[$this->service] = 0;

        $this->logger->warning('Circuit breaker: Service failure detected', [
            'service' => $this->service,
            'failure_count' => $this->failureCount[$this->service],
            'threshold' => $this->failureThreshold,
            'state' => $this->state,
        ]);

        if ($this->state === self::STATE_CLOSED
            || ($this->state === self::STATE_HALF_OPEN
             && $this->failureCount[$this->service] >= $this->failureThreshold)) {
            $this->transitionTo(self::STATE_OPEN);
        }

        $this->saveState();
    }

    protected function transitionTo(string $newState): void
    {
        $oldState = $this->state;
        $this->state = $newState;

        $this->logger->info('Circuit breaker: State transition', [
            'service' => $this->service,
            'old_state' => $oldState,
            'new_state' => $newState,
        ]);

        $this->saveState();
    }

    protected function loadState(): void
    {
        $stateKey = $this->getStateKey();
        $cachedState = $this->cache->get($stateKey);

        if ($cachedState !== null) {
            $data = json_decode($cachedState, true);
            $this->state = $data['state'] ?? self::STATE_CLOSED;
            $this->failureCount[$this->service] = $data['failure_count'] ?? 0;
            $this->successCount[$this->service] = $data['success_count'] ?? 0;
            $this->lastFailureTime[$this->service] = $data['last_failure_time'] ?? 0;
        } else {
            $this->state = self::STATE_CLOSED;
            $this->failureCount[$this->service] = 0;
            $this->successCount[$this->service] = 0;
            $this->lastFailureTime[$this->service] = 0;
        }
    }

    protected function saveState(): void
    {
        $stateKey = $this->getStateKey();

        $data = [
            'state' => $this->state,
            'failure_count' => $this->failureCount[$this->service] ?? 0,
            'success_count' => $this->successCount[$this->service] ?? 0,
            'last_failure_time' => $this->lastFailureTime[$this->service] ?? 0,
        ];

        $ttl = $this->recoveryTimeout * 2;
        $this->cache->set($stateKey, json_encode($data), $ttl);
    }

    protected function getStateKey(): string
    {
        return "circuit_breaker:{$this->service}";
    }
}
