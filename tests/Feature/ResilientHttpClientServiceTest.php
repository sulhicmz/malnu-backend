<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CircuitBreakerService;
use App\Services\ResilientHttpClientService;
use App\Services\RetryService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ResilientHttpClientServiceTest extends TestCase
{
    private ResilientHttpClientService $clientService;

    private CircuitBreakerService $circuitBreaker;

    private RetryService $retryService;

    private string $service = 'test-http';

    protected function setUp(): void
    {
        parent::setUp();

        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->circuitBreaker = new CircuitBreakerService($loggerMock);
        $this->retryService = new RetryService($loggerMock);

        $clientMock = $this->createMock(Client::class);
        $this->clientService = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );
    }

    public function testGetReturnsResponseOnSuccess()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(200, [], 'OK');
        $clientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com/data', $this->callback(function ($options) {
                return isset($options['timeout']) && isset($options['connect_timeout']);
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $response = $service->get('https://api.example.com/data');

        $this->assertSame($expectedResponse, $response);
    }

    public function testPostSendsRequestWithBody()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(201, [], '{"id": 1}');
        $clientMock->expects($this->once())
            ->method('request')
            ->with('POST', 'https://api.example.com/create', $this->callback(function ($options) {
                return isset($options['json']) && $options['json']['name'] === 'test';
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $response = $service->post('https://api.example.com/create', [
            'json' => ['name' => 'test'],
        ]);

        $this->assertSame($expectedResponse, $response);
    }

    public function testRequestAppliesTimeouts()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(200);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com', $this->callback(function ($options) {
                return $options['timeout'] === 30 && $options['connect_timeout'] === 10;
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $service->get('https://api.example.com');
    }

    public function testRequestUsesCustomTimeouts()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(200);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com', $this->callback(function ($options) {
                return $options['timeout'] === 60 && $options['connect_timeout'] === 20;
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $service->get('https://api.example.com', [
            'timeout' => 60,
            'connect_timeout' => 20,
        ]);
    }

    public function testGetHealthStatusReturnsStatus()
    {
        $status = $this->clientService->getHealthStatus();

        $this->assertEquals('test-http', $status['service']);
        $this->assertIsString($status['circuit_breaker_status']);
        $this->assertIsInt($status['timeout']);
        $this->assertIsInt($status['connect_timeout']);
    }

    public function testSetServiceUpdatesServiceName()
    {
        $this->clientService->setService('new-service');

        $status = $this->clientService->getHealthStatus();
        $this->assertEquals('new-service', $status['service']);
    }

    public function testDeleteSendsDeleteRequest()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(204);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.example.com/resource/1')
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $service->delete('https://api.example.com/resource/1');
    }

    public function testPutSendsPutRequest()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(200);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('PUT', 'https://api.example.com/resource/1', $this->callback(function ($options) {
                return isset($options['json']) && $options['json']['name'] === 'updated';
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $service->put('https://api.example.com/resource/1', [
            'json' => ['name' => 'updated'],
        ]);
    }

    public function testPatchSendsPatchRequest()
    {
        $clientMock = $this->createMock(Client::class);
        $expectedResponse = new Response(200);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('PATCH', 'https://api.example.com/resource/1', $this->callback(function ($options) {
                return isset($options['json']) && $options['json']['status'] === 'active';
            }))
            ->willReturn($expectedResponse);

        $service = new ResilientHttpClientService(
            $clientMock,
            $this->circuitBreaker,
            $this->retryService,
            $this->service
        );

        $service->patch('https://api.example.com/resource/1', [
            'json' => ['status' => 'active'],
        ]);
    }
}
