<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use App\Services\CacheMonitoringService;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Support\Facades\Cache;

class CacheTest extends TestCase
{
    public function test_user_service_caches_user_data()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        // Get instances of required services
        $userService = $this->app->get(UserService::class);
        $cacheMonitoring = $this->app->get(CacheMonitoringService::class);

        // First call - should be a cache miss
        $initialHits = $cacheMonitoring->getCacheStats()['hits'];
        $initialMisses = $cacheMonitoring->getCacheStats()['misses'];

        $result1 = $userService->getUserById($user->id);
        
        $afterFirstCall = $cacheMonitoring->getCacheStats();
        
        // After first call, we should have one more miss
        $this->assertEquals($initialMisses + 1, $afterFirstCall['misses']);

        // Second call - should be a cache hit
        $result2 = $userService->getUserById($user->id);
        
        $afterSecondCall = $cacheMonitoring->getCacheStats();
        
        // After second call, we should have one more hit
        $this->assertEquals($afterFirstCall['hits'] + 1, $afterSecondCall['hits']);
        
        // Results should be the same
        $this->assertEquals($result1->id, $result2->id);
        $this->assertEquals($result1->email, $result2->email);
    }

    public function test_user_service_caches_all_users()
    {
        // Create multiple test users
        User::factory()->count(3)->create();

        $userService = $this->app->get(UserService::class);
        $cacheMonitoring = $this->app->get(CacheMonitoringService::class);

        // First call - should be a cache miss
        $initialHits = $cacheMonitoring->getCacheStats()['hits'];
        $initialMisses = $cacheMonitoring->getCacheStats()['misses'];

        $result1 = $userService->getAllUsers();
        
        $afterFirstCall = $cacheMonitoring->getCacheStats();
        
        // After first call, we should have one more miss
        $this->assertGreaterThanOrEqual($initialMisses + 1, $afterFirstCall['misses']);

        // Second call - should be a cache hit
        $result2 = $userService->getAllUsers();
        
        $afterSecondCall = $cacheMonitoring->getCacheStats();
        
        // After second call, we should have more hits
        $this->assertGreaterThanOrEqual($afterFirstCall['hits'] + 1, $afterSecondCall['hits']);
        
        // Results should be the same count
        $this->assertEquals($result1->count(), $result2->count());
    }

    public function test_cache_invalidation_on_user_update()
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'name' => 'Original Name'
        ]);

        $userService = $this->app->get(UserService::class);
        
        // Cache the user
        $cachedUser = $userService->getUserById($user->id);
        
        // Update the user (this should invalidate the cache)
        $updatedUser = $userService->updateUser($user, [
            'name' => 'Updated Name'
        ]);

        // Get the user again - should come from DB, not cache
        $freshUser = $userService->getUserById($user->id);
        
        $this->assertEquals('Updated Name', $freshUser->name);
    }

    public function test_cache_health_status()
    {
        $cacheMonitoring = $this->app->get(CacheMonitoringService::class);
        
        $health = $cacheMonitoring->getHealthStatus();
        
        $this->assertArrayHasKey('status', $health);
        $this->assertArrayHasKey('hit_rate', $health);
        $this->assertArrayHasKey('health_check', $health);
        $this->assertTrue($health['health_check']);
    }
}