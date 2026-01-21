<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\Test\HttpTestCase;
use Hyperf\Di\Annotation\Inject;

class HealthTest extends HttpTestCase
{
    public function testHealthEndpointReturns200WhenHealthy()
    {
        $response = $this->get('/health');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('redis', $data['checks']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testHealthDetailedReturnsSystemInformation()
    {
        $response = $this->get('/health/detailed');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('redis', $data['checks']);
        $this->assertArrayHasKey('system', $data['checks']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testMetricsReturnsPerformanceData()
    {
        $response = $this->get('/health/metrics');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('healthy', $data['status']);
        $this->assertArrayHasKey('uptime', $data);
        $this->assertArrayHasKey('performance', $data);
        $this->assertArrayHasKey('system', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function testHealthEndpointReturns503WhenDatabaseFails()
    {
        $this->markTestSkipped('Requires database mocking to simulate failure');
    }

    public function testHealthEndpointReturns503WhenRedisFails()
    {
        $this->markTestSkipped('Requires Redis mocking to simulate failure');
    }
}
