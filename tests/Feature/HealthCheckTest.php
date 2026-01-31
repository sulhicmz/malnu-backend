<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Health Check Endpoint Tests
 *
 * @covers \App\Http\Controllers\Api\HealthCheckController
 */
class HealthCheckTest extends TestCase
{
    /**
     * Test overall health endpoint returns healthy status.
     */
    public function testHealthEndpointReturnsHealthyStatus(): void
    {
        $response = $this->get('/health');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('checks', $data);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('redis', $data['checks']);
        $this->assertArrayHasKey('system', $data['checks']);
    }

    /**
     * Test database health check endpoint.
     */
    public function testDatabaseHealthCheck(): void
    {
        $response = $this->get('/health/database');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('latency_ms', $data);
    }

    /**
     * Test Redis health check endpoint.
     */
    public function testRedisHealthCheck(): void
    {
        $response = $this->get('/health/redis');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('latency_ms', $data);
    }

    /**
     * Test system health check endpoint.
     */
    public function testSystemHealthCheck(): void
    {
        $response = $this->get('/health/system');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('memory_usage', $data);
        $this->assertArrayHasKey('load_average', $data);
    }

    /**
     * Test health check returns 503 when unhealthy component exists.
     */
    public function testHealthEndpointReturns503WhenUnhealthy(): void
    {
        $this->markTestSkipped('This test requires mocking unhealthy state');

        $response = $this->get('/health');

        $this->assertEquals(503, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals('unhealthy', $data['status']);
    }
}
