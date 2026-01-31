<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Metrics Endpoint Tests
 *
 * @covers \App\Http\Controllers\Api\MetricsController
 */
class MetricsTest extends TestCase
{
    /**
     * Test metrics endpoint returns valid structure.
     */
    public function testMetricsEndpointReturnsValidStructure(): void
    {
        $response = $this->get('/metrics');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('generated_at', $data);
        $this->assertArrayHasKey('requests', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('database', $data);
        $this->assertArrayHasKey('cache', $data);
        $this->assertArrayHasKey('system', $data);
    }

    /**
     * Test metrics request section structure.
     */
    public function testMetricsRequestsSectionStructure(): void
    {
        $response = $this->get('/metrics');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('requests', $data);
        $this->assertArrayHasKey('total_requests', $data['requests']);
        $this->assertArrayHasKey('successful_requests', $data['requests']);
        $this->assertArrayHasKey('failed_requests', $data['requests']);
        $this->assertArrayHasKey('avg_response_time_ms', $data['requests']);
        $this->assertArrayHasKey('p95_response_time_ms', $data['requests']);
        $this->assertArrayHasKey('p99_response_time_ms', $data['requests']);
    }

    /**
     * Test metrics error section structure.
     */
    public function testMetricsErrorsSectionStructure(): void
    {
        $response = $this->get('/metrics');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('total_errors', $data['errors']);
        $this->assertArrayHasKey('error_rate_percent', $data['errors']);
        $this->assertArrayHasKey('top_errors', $data['errors']);
    }

    /**
     * Test metrics database section structure.
     */
    public function testMetricsDatabaseSectionStructure(): void
    {
        $response = $this->get('/metrics');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('database', $data);
        $this->assertArrayHasKey('query_count', $data['database']);
        $this->assertArrayHasKey('avg_query_time_ms', $data['database']);
        $this->assertArrayHasKey('slow_queries_count', $data['database']);
    }

    /**
     * Test metrics cache section structure.
     */
    public function testMetricsCacheSectionStructure(): void
    {
        $response = $this->get('/metrics');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('cache', $data);
        $this->assertArrayHasKey('hit_rate_percent', $data['cache']);
        $this->assertArrayHasKey('total_hits', $data['cache']);
        $this->assertArrayHasKey('total_misses', $data['cache']);
        $this->assertArrayHasKey('used_memory', $data['cache']);
    }

    /**
     * Test metrics system section structure.
     */
    public function testMetricsSystemSectionStructure(): void
    {
        $response = $this->get('/metrics');

        $data = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('system', $data);
        $this->assertArrayHasKey('memory_usage_mb', $data['system']);
        $this->assertArrayHasKey('memory_limit_mb', $data['system']);
        $this->assertArrayHasKey('memory_usage_percent', $data['system']);
        $this->assertArrayHasKey('load_average', $data['system']);
        $this->assertArrayHasKey('uptime_seconds', $data['system']);
    }
}
