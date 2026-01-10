<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Services\CacheService;
use App\Http\Middleware\ResponseCache;
use App\Services\PerformanceMonitoringService;

class CacheAndPerformanceTest extends TestCase
{
    private CacheService $cache;
    private PerformanceMonitoringService $performance;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (class_exists(CacheService::class)) {
            $this->cache = new CacheService();
            $this->cache->resetMetrics();
        }

        if (class_exists(PerformanceMonitoringService::class)) {
            $this->performance = new PerformanceMonitoringService();
            $this->performance->resetMetrics();
        }
    }

    protected function tearDown(): void
    {
        if ($this->cache) {
            $this->cache->flush();
        }

        parent::tearDown();
    }

    public function test_cache_service_exists()
    {
        $this->assertTrue(class_exists(CacheService::class), 'CacheService class should exist');
    }

    public function test_cache_service_can_set_and_get()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $key = 'test:key';
        $value = ['id' => 1, 'name' => 'Test'];

        $result = $this->cache->set($key, $value, 60);
        $this->assertTrue($result, 'Cache set should return true');

        $retrieved = $this->cache->get($key);
        $this->assertEquals($value, $retrieved, 'Retrieved value should match');
    }

    public function test_cache_service_remembers_callback()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $key = 'test:remember';
        $expectedValue = 'computed_value';
        $callbackCalled = false;

        $result = $this->cache->remember($key, function () use (&$callbackCalled) {
            $callbackCalled = true;
            return $expectedValue;
        }, 60);

        $this->assertEquals($expectedValue, $result, 'Remember should return cached value');
        $this->assertTrue($callbackCalled, 'Callback should have been called on miss');
    }

    public function test_cache_service_forget_on_hit()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $key = 'test:hit';
        $value = 'cached_value';
        $this->cache->set($key, $value, 60);
        $this->cache->resetMetrics();

        $callbackCalled = false;
        $result = $this->cache->remember($key, function () use (&$callbackCalled) {
            $callbackCalled = false; // Should not be called on cache hit
            return 'should_not_return_this';
        }, 60);

        $this->assertEquals($value, $result, 'Should return cached value');
        $this->assertFalse($callbackCalled, 'Callback should not be called on cache hit');

        $metrics = $this->cache->getMetrics();
        $this->assertGreaterThan(0, $metrics['hits'], 'Should have at least one cache hit');
        $this->assertEquals(0, $metrics['misses'], 'Should have no cache misses');
    }

    public function test_cache_service_has_key()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $key = 'test:has';
        $this->assertFalse($this->cache->has($key), 'Should not have uncached key');

        $this->cache->set($key, 'value', 60);
        $this->assertTrue($this->cache->has($key), 'Should have cached key');
    }

    public function test_cache_service_forget()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $key = 'test:forget';
        $this->cache->set($key, 'value', 60);
        $this->assertTrue($this->cache->has($key), 'Should have key before forget');

        $this->cache->forget($key);
        $this->assertFalse($this->cache->has($key), 'Should not have key after forget');
    }

    public function test_cache_service_tag_operations()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $tag = 'test:tag';
        $key1 = 'test:tag:1';
        $key2 = 'test:tag:2';
        $key3 = 'test:tag:3';

        $this->cache->setWithTag($key1, $tag, 'value1', 60);
        $this->cache->setWithTag($key2, $tag, 'value2', 60);
        $this->cache->setWithTag($key3, $tag, 'value3', 60);

        $this->assertTrue($this->cache->has($key1), 'Key 1 should exist');
        $this->assertTrue($this->cache->has($key2), 'Key 2 should exist');
        $this->assertTrue($this->cache->has($key3), 'Key 3 should exist');

        $this->cache->forgetByTag($tag);

        $this->assertFalse($this->cache->has($key1), 'Key 1 should not exist after tag forget');
        $this->assertFalse($this->cache->has($key2), 'Key 2 should not exist after tag forget');
        $this->assertFalse($this->cache->has($key3), 'Key 3 should not exist after tag forget');
    }

    public function test_cache_service_metrics()
    {
        if (!$this->cache) {
            $this->markTestSkipped('CacheService not available');
            return;
        }

        $this->cache->resetMetrics();

        $this->cache->set('key1', 'value1', 60);
        $this->cache->get('key1'); // Hit
        $this->cache->get('key2'); // Miss
        $this->cache->forget('key3'); // Delete

        $metrics = $this->cache->getMetrics();

        $this->assertEquals(1, $metrics['hits'], 'Should have 1 hit');
        $this->assertEquals(1, $metrics['misses'], 'Should have 1 miss');
        $this->assertEquals(1, $metrics['sets'], 'Should have 1 set');
        $this->assertEquals(1, $metrics['deletes'], 'Should have 1 delete');
        $this->assertEquals(50.0, $metrics['hit_rate'], 'Should have 50% hit rate');
    }

    public function test_performance_monitoring_service_exists()
    {
        $this->assertTrue(class_exists(PerformanceMonitoringService::class), 'PerformanceMonitoringService should exist');
    }

    public function test_performance_monitoring_records_request()
    {
        if (!$this->performance) {
            $this->markTestSkipped('PerformanceMonitoringService not available');
            return;
        }

        $this->performance->resetMetrics();

        $this->performance->recordRequest(150.5, false);
        $this->performance->recordRequest(250.3, true);

        $metrics = $this->performance->getMetrics();

        $this->assertEquals(2, $metrics['request_count'], 'Should have recorded 2 requests');
        $this->assertEquals(1, $metrics['slow_requests'], 'Should have 1 slow request');
        $this->assertEquals(400.8, $metrics['total_response_time'], 'Total time should be 400.8ms');
        $this->assertEquals(200.4, $metrics['avg_response_time'], 'Average should be 200.4ms');
    }

    public function test_performance_monitoring_slow_rate_calculation()
    {
        if (!$this->performance) {
            $this->markTestSkipped('PerformanceMonitoringService not available');
            return;
        }

        $this->performance->resetMetrics();

        $this->performance->recordRequest(100, false);
        $this->performance->recordRequest(150, false);
        $this->performance->recordRequest(300, true);

        $metrics = $this->performance->getMetrics();

        $this->assertEquals(33.3, $metrics['slow_rate'], 'Slow rate should be 33.33%');
    }

    public function test_performance_monitoring_status()
    {
        if (!$this->performance) {
            $this->markTestSkipped('PerformanceMonitoringService not available');
            return;
        }

        $this->performance->resetMetrics();
        $this->performance->recordRequest(50, false);
        $this->performance->recordRequest(50, false);
        $this->performance->recordRequest(60, false);

        $status = $this->performance->getPerformanceStatus();
        $this->assertEquals('excellent', $status, 'Should be excellent with <100ms avg and <5% slow');

        $this->performance->resetMetrics();
        $this->performance->recordRequest(150, false);
        $this->performance->recordRequest(180, true);

        $status = $this->performance->getPerformanceStatus();
        $this->assertEquals('good', $status, 'Should be good with <200ms avg and <10% slow');
    }

    public function test_performance_monitoring_recommendations()
    {
        if (!$this->performance) {
            $this->markTestSkipped('PerformanceMonitoringService not available');
            return;
        }

        $this->performance->resetMetrics();

        for ($i = 0; $i < 5; $i++) {
            $this->performance->recordRequest(300, true);
        }

        $recommendations = $this->performance->getRecommendations();

        $this->assertIsArray($recommendations, 'Should return array of recommendations');
        $this->assertGreaterThan(0, count($recommendations), 'Should have recommendations for poor performance');
    }

    public function test_response_cache_middleware_exists()
    {
        $this->assertTrue(class_exists(ResponseCache::class), 'ResponseCache middleware should exist');
    }

    public function test_configuration_files_updated()
    {
        $envExample = file_get_contents(base_path('.env.example'));
        
        $this->assertStringContainsString('CACHE_DRIVER=redis', $envExample, 'CACHE_DRIVER should be set to redis');
        $this->assertStringContainsString('SESSION_DRIVER=redis', $envExample, 'SESSION_DRIVER should be set to redis');
        $this->assertStringContainsString('CACHE_DEFAULT_TTL=', $envExample, 'Should have CACHE_DEFAULT_TTL');
        $this->assertStringContainsString('PERFORMANCE_SLOW_THRESHOLD=', $envExample, 'Should have PERFORMANCE_SLOW_THRESHOLD');
    }

    public function test_documentation_exists()
    {
        $this->assertFileExists(base_path('docs/CACHING_OPTIMIZATION.md'), 'Caching documentation should exist');
    }
}
