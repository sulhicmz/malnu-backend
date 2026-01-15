<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class ResilientHttpClientService
{
    private Client $client;

    private CircuitBreakerService $circuitBreaker;

    private RetryService $retryService;

    private array $config;

    private string $service;

    public function __construct(
        Client $client,
        CircuitBreakerService $circuitBreaker,
        RetryService $retryService,
        string $service = 'http'
    ) {
        $this->client = $client;
        $this->circuitBreaker = $circuitBreaker;
        $this->retryService = $retryService;
        $this->service = $service;
        $this->config = config('resilient_http', [
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    public function get(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('POST', $uri, $options);
    }

    public function put(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $options);
    }

    public function patch(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $uri, $options);
    }

    public function delete(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, $options);
    }

    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = $this->applyTimeouts($options);

        return $this->retryService->executeWithCircuitBreaker(
            $this->service,
            function () use ($method, $uri, $options) {
                return $this->client->request($method, $uri, $options);
            },
            $this->circuitBreaker,
            $this->getFallback($method, $uri),
            $this->getRetryOptions()
        );
    }

    public function requestAsync(string $method, string $uri, array $options = []): PromiseInterface
    {
        $options = $this->applyTimeouts($options);

        return $this->client->requestAsync($method, $uri, $options);
    }

    public function setService(string $service): void
    {
        $this->service = $service;
    }

    public function getHealthStatus(): array
    {
        return [
            'service' => $this->service,
            'circuit_breaker_status' => $this->circuitBreaker->getStatus($this->service),
            'timeout' => $this->config['timeout'],
            'connect_timeout' => $this->config['connect_timeout'],
        ];
    }

    private function applyTimeouts(array $options): array
    {
        if (! isset($options['timeout'])) {
            $options['timeout'] = $this->config['timeout'];
        }

        if (! isset($options['connect_timeout'])) {
            $options['connect_timeout'] = $this->config['connect_timeout'];
        }

        return $options;
    }

    private function getFallback(string $method, string $uri): callable
    {
        return function () use ($method, $uri) {
            throw new Exception(
                "Service unavailable due to circuit breaker. Failed to {$method} {$uri}"
            );
        };
    }

    private function getRetryOptions(): array
    {
        return [
            'max_attempts' => 3,
            'initial_delay' => 500,
            'max_delay' => 10000,
            'multiplier' => 2,
            'jitter' => true,
            'retry_on' => [
                ConnectException::class,
                ServerException::class,
                TransferException::class,
            ],
            'operation_name' => $this->service . '_http_request',
        ];
    }
}
