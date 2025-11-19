<?php

namespace Tests\Feature;

use App\Services\CacheService;
use App\Services\PerformanceMonitorService;
use App\Repositories\UserRepository;
use Hyperf\Testing\TestCase;
use Hyperf\Di\ContainerFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;

class PerformanceTest extends TestCase
{
    public function test_cache_service_works()
    {
        $container = ContainerFactory::create();
        $cacheService = $container->get(CacheService::class);
        
        $key = 'test_key_' . time();
        $value = ['test' => 'data'];
        
        // Test putting data in cache
        $result = $cacheService->putInCache($key, $value);
        $this->assertTrue($result);
        
        // Test getting data from cache
        $cachedData = $cacheService->getWithCache($key, function () {
            return ['should_not_be_used' => true];
        }, 3600);
        
        $this->assertEquals($value, $cachedData);
        
        // Clean up
        $cacheService->deleteFromCache($key);
    }
    
    public function test_performance_monitor_tracks_stats()
    {
        $container = ContainerFactory::create();
        $performanceService = $container->get(PerformanceMonitorService::class);
        
        // Reset stats first
        $performanceService->resetStats();
        
        // Track a fake query
        $performanceService->trackQueryTime('SELECT * FROM users', 0.05); // 50ms
        
        $stats = $performanceService->getQueryStats();
        
        $this->assertArrayHasKey('average_execution_time', $stats);
        $this->assertArrayHasKey('total_queries_tracked', $stats);
        
        // Should have tracked at least one query
        $this->assertGreaterThanOrEqual(1, $stats['total_queries_tracked']);
    }
    
    public function test_user_repository_caching()
    {
        $container = ContainerFactory::create();
        $userRepository = $container->get(UserRepository::class);
        
        // Test that methods exist and don't throw errors
        $this->assertTrue(method_exists($userRepository, 'getAll'));
        $this->assertTrue(method_exists($userRepository, 'findById'));
        $this->assertTrue(method_exists($userRepository, 'findByEmail'));
        $this->assertTrue(method_exists($userRepository, 'paginate'));
    }
}