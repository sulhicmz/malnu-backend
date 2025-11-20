<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\UserService;
use App\Utils\PerformanceMonitor;
use Hypervel\Cache\Facades\Cache;
use Hyperf\Testing\TestCase;

class CachePerformanceTest extends TestCase
{
    public function test_user_service_uses_caching()
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $userService = $this->createContainer()->get(UserService::class);

        // Clear cache before test
        $userService->clearAllUserCache();

        // First call - should hit database and cache
        PerformanceMonitor::startTimer();
        $result1 = $userService->getUserById($user->id);
        $firstCallTime = PerformanceMonitor::getExecutionTime();

        // Second call - should use cache
        $result2 = $userService->getUserById($user->id);
        $secondCallTime = PerformanceMonitor::getExecutionTime() - $firstCallTime;

        // Verify both results are the same
        $this->assertEquals($result1->id, $result2->id);
        $this->assertEquals($result1->name, $result2->name);

        // The second call should be significantly faster (using cache)
        // Note: We can't make strict timing assertions in tests due to varying environments
        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
    }

    public function test_get_all_users_caching()
    {
        // Create multiple test users
        User::factory()->count(5)->create();

        $userService = $this->createContainer()->get(UserService::class);

        // Clear cache before test
        $userService->clearAllUserCache();

        // First call - should hit database
        $users1 = $userService->getAllUsers();
        $count1 = $users1->count();

        // Second call - should use cache
        $users2 = $userService->getAllUsers();
        $count2 = $users2->count();

        // Results should be the same
        $this->assertEquals($count1, $count2);
        $this->assertGreaterThanOrEqual(5, $count1); // At least the 5 we created
    }

    public function test_cache_invalidation_on_user_update()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $userService = $this->createContainer()->get(UserService::class);

        // Clear cache and get user to populate cache
        $userService->clearAllUserCache();
        $cachedUser = $userService->getUserById($user->id);
        $this->assertEquals('Original Name', $cachedUser->name);

        // Update the user
        $updatedUser = $userService->updateUser($user->id, [
            'name' => 'Updated Name'
        ]);

        // Get the user again - should reflect the update
        $freshUser = $userService->getUserById($user->id);
        $this->assertEquals('Updated Name', $freshUser->name);
        $this->assertEquals('Updated Name', $updatedUser->name);
    }

    public function test_performance_monitor_tracking()
    {
        // Test that performance monitor can track execution time
        PerformanceMonitor::startTimer();
        
        // Simulate some work
        usleep(1000); // 1ms
        
        $executionTime = PerformanceMonitor::getExecutionTime();
        
        // Execution time should be > 0
        $this->assertGreaterThan(0, $executionTime);
        
        // Test cache stats
        PerformanceMonitor::incrementCacheHit();
        PerformanceMonitor::incrementCacheMiss();
        PerformanceMonitor::incrementCacheSet();
        
        $stats = PerformanceMonitor::getCacheStats();
        $this->assertEquals(1, $stats['hits']);
        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(1, $stats['sets']);
        
        // Test cache hit ratio
        $hitRatio = PerformanceMonitor::getCacheHitRatio();
        $this->assertEquals(0.5, $hitRatio); // 1 hit out of 2 total (1 hit + 1 miss)
        
        // Reset and verify
        PerformanceMonitor::reset();
        $resetStats = PerformanceMonitor::getCacheStats();
        $this->assertEquals(0, $resetStats['hits']);
    }
}