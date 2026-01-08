<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\RedisFactory;
use App\Services\MonitoringService;

/**
 * Monitoring System Tests
 * 
 * Tests for health checks, monitoring service, and error tracking.
 * 
 * @internal
 * @covers \App\Services\MonitoringService
 * @covers \App\Http\Controllers\Monitoring\HealthController
 */
class MonitoringTest extends TestCase
{
    private MonitoringService $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitoringService = $this->container->get(MonitoringService::class);
    }

    /**
     * Test basic health check endpoint returns 200 when system is healthy
     */
    public function testBasicHealthCheckReturns200WhenHealthy(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'timestamp',
                    'checks',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                ],
            ]);
    }

    /**
     * Test basic health check includes all required components
     */
    public function testBasicHealthCheckIncludesAllComponents(): void
    {
        $response = $this->get('/health');

        $data = $response->json('data');

        $this->assertArrayHasKey('app', $data['checks']);
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('redis', $data['checks']);

        $this->assertEquals('ok', $data['checks']['app']['status']);
    }

    /**
     * Test detailed health check includes additional system information
     */
    public function testDetailedHealthCheckIncludesSystemInformation(): void
    {
        $response = $this->get('/health/detailed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'timestamp',
                    'checks',
                    'system',
                    'performance',
                    'uptime',
                ],
            ]);

        $data = $response->json('data');

        $this->assertArrayHasKey('memory', $data['system']);
        $this->assertArrayHasKey('php_version', $data['system']);
        $this->assertArrayHasKey('uptime', $data['uptime']);
    }

    /**
     * Test metrics endpoint returns performance metrics
     */
    public function testMetricsEndpointReturnsPerformanceMetrics(): void
    {
        $response = $this->get('/monitoring/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'total_requests',
                        'successful_requests',
                        'failed_requests',
                        'total_response_time',
                        'average_response_time',
                        'error_rate',
                        'success_rate',
                    ],
                    'thresholds',
                    'status',
                ],
            ]);
    }

    /**
     * Test metrics includes configured thresholds
     */
    public function testMetricsIncludesConfiguredThresholds(): void
    {
        $response = $this->get('/monitoring/metrics');

        $thresholds = $response->json('data.thresholds');

        $this->assertArrayHasKey('max_response_time', $thresholds);
        $this->assertArrayHasKey('max_error_rate', $thresholds);
        $this->assertArrayHasKey('min_success_rate', $thresholds);
        $this->assertArrayHasKey('slow_query_threshold', $thresholds);

        $this->assertEquals(200, $thresholds['max_response_time']);
        $this->assertEquals(1, $thresholds['max_error_rate']);
        $this->assertEquals(99, $thresholds['min_success_rate']);
    }

    /**
     * Test errors endpoint returns recent errors
     */
    public function testErrorsEndpointReturnsRecentErrors(): void
    {
        $response = $this->get('/monitoring/errors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'errors',
                    'summary',
                ],
            ]);

        $this->assertIsArray($response->json('data.errors'));
        $this->assertIsInt($response->json('data.total'));
    }

    /**
     * Test monitoring service tracks requests correctly
     */
    public function testMonitoringServiceTracksRequests(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 150,
            'method' => 'GET',
            'path' => '/test',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals(1, $metrics['metrics']['total_requests']);
        $this->assertEquals(1, $metrics['metrics']['successful_requests']);
        $this->assertEquals(0, $metrics['metrics']['failed_requests']);
        $this->assertEquals(150, $metrics['metrics']['total_response_time']);
    }

    /**
     * Test monitoring service tracks failed requests
     */
    public function testMonitoringServiceTracksFailedRequests(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 500,
            'response_time' => 300,
            'error' => 'Internal Server Error',
            'method' => 'GET',
            'path' => '/test',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals(1, $metrics['metrics']['total_requests']);
        $this->assertEquals(0, $metrics['metrics']['successful_requests']);
        $this->assertEquals(1, $metrics['metrics']['failed_requests']);
    }

    /**
     * Test monitoring service calculates average response time
     */
    public function testMonitoringServiceCalculatesAverageResponseTime(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 100,
            'method' => 'GET',
            'path' => '/test1',
        ]);

        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 200,
            'method' => 'GET',
            'path' => '/test2',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals(150, $metrics['metrics']['average_response_time']);
    }

    /**
     * Test monitoring service tracks slow requests
     */
    public function testMonitoringServiceTracksSlowRequests(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 300,
            'method' => 'GET',
            'path' => '/slow',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals(1, $metrics['metrics']['slow_requests']);
    }

    /**
     * Test monitoring service tracks errors
     */
    public function testMonitoringServiceTracksErrors(): void
    {
        $this->monitoringService->trackError('Test error message', [
            'context' => 'test',
        ]);

        $errors = $this->monitoringService->getRecentErrors();

        $this->assertEquals(1, $errors['total']);
        $this->assertEquals('Test error message', $errors['errors'][0]['message']);
    }

    /**
     * Test monitoring service classifies database errors correctly
     */
    public function testMonitoringServiceClassifiesDatabaseErrors(): void
    {
        $this->monitoringService->trackError('SQL connection failed');

        $errors = $this->monitoringService->getRecentErrors();

        $this->assertEquals('database', $errors['errors'][0]['type']);
    }

    /**
     * Test monitoring service classifies network errors correctly
     */
    public function testMonitoringServiceClassifiesNetworkErrors(): void
    {
        $this->monitoringService->trackError('Connection timeout');

        $errors = $this->monitoringService->getRecentErrors();

        $this->assertEquals('network', $errors['errors'][0]['type']);
    }

    /**
     * Test monitoring service calculates error rate correctly
     */
    public function testMonitoringServiceCalculatesErrorRate(): void
    {
        for ($i = 0; $i < 9; $i++) {
            $this->monitoringService->trackRequest([
                'status' => 200,
                'response_time' => 100,
                'method' => 'GET',
                'path' => '/test',
            ]);
        }

        $this->monitoringService->trackRequest([
            'status' => 500,
            'response_time' => 100,
            'error' => 'Error',
            'method' => 'GET',
            'path' => '/test',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals(10.0, $metrics['metrics']['error_rate']);
    }

    /**
     * Test health check returns unhealthy when database fails
     */
    public function testHealthCheckReturnsUnhealthyWhenDatabaseFails(): void
    {
        $this->expectException(\Exception::class);

        try {
            Db::select('SELECT 1');
        } catch (\Exception $e) {
            $health = $this->monitoringService->getBasicHealth();

            $this->assertEquals('unhealthy', $health['status']);
            $this->assertEquals('error', $health['checks']['database']['status']);
            throw $e;
        }
    }

    /**
     * Test detailed health check includes memory information
     */
    public function testDetailedHealthCheckIncludesMemoryInformation(): void
    {
        $health = $this->monitoringService->getDetailedHealth();

        $this->assertArrayHasKey('memory', $health['system']);
        $this->assertArrayHasKey('current', $health['system']['memory']);
        $this->assertArrayHasKey('peak', $health['system']['memory']);
        $this->assertArrayHasKey('limit', $health['system']['memory']);

        $this->assertIsInt($health['system']['memory']['current']);
        $this->assertIsInt($health['system']['memory']['peak']);
    }

    /**
     * Test detailed health check includes uptime information
     */
    public function testDetailedHealthCheckIncludesUptimeInformation(): void
    {
        $health = $this->monitoringService->getDetailedHealth();

        $this->assertArrayHasKey('uptime', $health);
        $this->assertArrayHasKey('seconds', $health['uptime']);
        $this->assertArrayHasKey('human_readable', $health['uptime']);

        $this->assertIsInt($health['uptime']['seconds']);
        $this->assertIsString($health['uptime']['human_readable']);
    }

    /**
     * Test metrics status evaluates to healthy when metrics are good
     */
    public function testMetricsStatusHealthyWhenMetricsGood(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 100,
            'method' => 'GET',
            'path' => '/test',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals('healthy', $metrics['status']);
    }

    /**
     * Test metrics status evaluates to degraded when response time is slow
     */
    public function testMetricsStatusDegradedWhenResponseTimeSlow(): void
    {
        $this->monitoringService->trackRequest([
            'status' => 200,
            'response_time' => 300,
            'method' => 'GET',
            'path' => '/test',
        ]);

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals('degraded', $metrics['status']);
    }

    /**
     * Test metrics status evaluates to critical when error rate is high
     */
    public function testMetricsStatusCriticalWhenErrorRateHigh(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->monitoringService->trackRequest([
                'status' => 500,
                'response_time' => 100,
                'error' => 'Error',
                'method' => 'GET',
                'path' => '/test',
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->monitoringService->trackRequest([
                'status' => 200,
                'response_time' => 100,
                'method' => 'GET',
                'path' => '/test',
            ]);
        }

        $metrics = $this->monitoringService->getMetrics();

        $this->assertEquals('critical', $metrics['status']);
    }

    /**
     * Test error summary groups errors by type
     */
    public function testErrorSummaryGroupsErrorsByType(): void
    {
        $this->monitoringService->trackError('SQL connection failed');
        $this->monitoringService->trackError('SQL query failed');
        $this->monitoringService->trackError('Connection timeout');

        $errors = $this->monitoringService->getRecentErrors();

        $this->assertArrayHasKey('by_type', $errors['summary']);
        $this->assertArrayHasKey('database', $errors['summary']['by_type']);
        $this->assertArrayHasKey('network', $errors['summary']['by_type']);
        $this->assertEquals(2, $errors['summary']['by_type']['database']);
        $this->assertEquals(1, $errors['summary']['by_type']['network']);
    }
}
