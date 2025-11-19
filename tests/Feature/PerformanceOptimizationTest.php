<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use App\Services\QueryOptimizationService;
use App\Services\CacheService;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;
    protected QueryOptimizationService $queryOptimizationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userService = new UserService();
        $this->queryOptimizationService = new QueryOptimizationService();
    }

    public function test_user_service_caching_works(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // First call - should hit database
        $retrievedUser1 = $this->userService->getUserById($user->id);
        $this->assertEquals($user->id, $retrievedUser1->id);

        // Second call - should hit cache
        $retrievedUser2 = $this->userService->getUserById($user->id);
        $this->assertEquals($user->id, $retrievedUser2->id);
        
        // Both should be the same user
        $this->assertEquals($retrievedUser1->id, $retrievedUser2->id);
    }

    public function test_get_all_users_with_caching(): void
    {
        // Create multiple test users
        User::factory()->count(5)->create();

        // First call - should hit database
        $users1 = $this->userService->getAllUsers();
        $this->assertCount(5, $users1);

        // Second call - should hit cache
        $users2 = $this->userService->getAllUsers();
        $this->assertCount(5, $users2);
        
        // Both should have the same count
        $this->assertEquals($users1->count(), $users2->count());
    }

    public function test_user_service_clear_cache(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Get user to populate cache
        $cachedUser = $this->userService->getUserById($user->id);
        $this->assertNotNull($cachedUser);

        // Clear the cache
        $this->userService->clearUserCache($user->id);

        // The cache should be cleared, but we should still be able to get the user from DB
        $freshUser = $this->userService->getUserById($user->id);
        $this->assertEquals($user->id, $freshUser->id);
    }

    public function test_query_optimization_service_works(): void
    {
        // Create a test user with roles relationship
        $user = User::factory()->create([
            'name' => 'Test User with Roles',
            'email' => 'test-roles@example.com'
        ]);

        // Test optimized query with roles
        $usersWithRoles = $this->queryOptimizationService->getUsersWithRolesOptimized();
        $this->assertIsIterable($usersWithRoles);
    }

    public function test_cache_service_functions(): void
    {
        $testKey = 'test_cache_key';
        $testValue = 'test_cache_value';

        // Test put and get
        CacheService::put($testKey, $testValue, 3600);
        $retrievedValue = CacheService::get($testKey);
        $this->assertEquals($testValue, $retrievedValue);

        // Test has
        $this->assertTrue(CacheService::has($testKey));

        // Test forget
        CacheService::forget($testKey);
        $this->assertFalse(CacheService::has($testKey));
    }

    public function test_get_users_with_specific_columns(): void
    {
        // Create test users
        User::factory()->count(3)->create();

        // Get users with specific columns
        $users = $this->queryOptimizationService->getUsersWithSpecificColumns();
        
        // Verify we got users
        $this->assertGreaterThanOrEqual(3, $users->count());
        
        // Verify that the returned objects have the expected attributes
        foreach ($users as $user) {
            $this->assertObjectHasAttribute('id', $user);
            $this->assertObjectHasAttribute('name', $user);
            $this->assertObjectHasAttribute('email', $user);
        }
    }

    public function test_get_users_with_role_condition(): void
    {
        // Create test users
        $user = User::factory()->create([
            'name' => 'Role Test User',
            'email' => 'role-test@example.com'
        ]);

        // Test with a role name that doesn't exist (should return empty collection)
        $users = $this->queryOptimizationService->getUsersWithRoleCondition('nonexistent-role');
        
        // Should return a collection (might be empty)
        $this->assertIsIterable($users);
    }
}