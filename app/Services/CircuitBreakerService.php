<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Psr\Log\LoggerInterface;

class CircuitBreakerService
{
    private array $states = [];

    private array $config;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = config('circuit-breaker', [
            'failure_threshold' => 5,
            'recovery_timeout' => 60,
            'half_open_attempts' => 1,
        ]);
    }

    public function call(string $service, callable $operation, ?callable $fallback = null)
    {
        $state = $this->getState($service);

        if ($state['status'] === 'OPEN') {
            $this->logger->warning('Circuit breaker is OPEN', [
                'service' => $service,
                'opens_at' => $state['opens_at'],
                'recovery_timeout' => $this->config['recovery_timeout'],
            ]);

            if ($fallback !== null) {
                return $fallback($service);
            }

            throw new Exception("Circuit breaker is OPEN for service: {$service}");
        }

        if ($state['status'] === 'HALF_OPEN') {
            try {
                $result = $operation();
                $this->recordSuccess($service);
                return $result;
            } catch (Exception $e) {
                $this->recordFailure($service);
                throw $e;
            }
        }

        try {
            $result = $operation();
            $this->recordSuccess($service);
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($service);

            throw $e;
        }
    }

    public function getState(string $service): array
    {
        if (! isset($this->states[$service])) {
            $this->states[$service] = [
                'status' => 'CLOSED',
                'failures' => 0,
                'half_open_attempts' => 0,
                'opens_at' => null,
            ];
        }

        $state = $this->states[$service];

        if ($state['status'] === 'OPEN') {
            $now = time();

            if ($now - ($state['opens_at'] ?? 0) >= $this->config['recovery_timeout']) {
                $state['status'] = 'HALF_OPEN';
                $state['half_open_attempts'] = 0;
                $this->states[$service] = $state;

                $this->logger->info('Circuit breaker moved to HALF_OPEN', [
                    'service' => $service,
                ]);
            }
        }

        return $this->states[$service];
    }

    public function reset(string $service): void
    {
        unset($this->states[$service]);

        $this->logger->info('Circuit breaker reset', [
            'service' => $service,
        ]);
    }

    public function getStatus(string $service): string
    {
        return $this->getState($service)['status'];
    }

    private function recordSuccess(string $service): void
    {
        $state = $this->getState($service);

        if ($state['status'] === 'HALF_OPEN') {
            ++$state['half_open_attempts'];

            if ($state['half_open_attempts'] >= $this->config['half_open_attempts']) {
                $state['status'] = 'CLOSED';
                $state['failures'] = 0;
                $state['half_open_attempts'] = 0;
                $state['opens_at'] = null;

                $this->logger->info('Circuit breaker moved to CLOSED', [
                    'service' => $service,
                ]);
            }
        } else {
            $state['failures'] = 0;
        }

        $this->states[$service] = $state;
    }

    private function recordFailure(string $service): void
    {
        $state = $this->getState($service);

        if ($state['status'] === 'HALF_OPEN') {
            $state['status'] = 'OPEN';
            $state['opens_at'] = time();

            $this->logger->warning('Circuit breaker moved to OPEN', [
                'service' => $service,
                'failures' => $state['failures'],
            ]);
        } else {
            ++$state['failures'];

            if ($state['failures'] >= $this->config['failure_threshold']) {
                $state['status'] = 'OPEN';
                $state['opens_at'] = time();

                $this->logger->error('Circuit breaker opened', [
                    'service' => $service,
                    'failures' => $state['failures'],
                    'threshold' => $this->config['failure_threshold'],
                ]);
            }
        }

        $this->states[$service] = $state;
    }
}
