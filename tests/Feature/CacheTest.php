<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use Hyperf\Testing\TestCase;
use Hyperf\Support\Facades\Cache;

class CacheTest extends TestCase
{
    public function test_user_service_caching()
    {
        // Create a test user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'full_name' => 'Test User Full Name',
        ]);

        $userService = $this->container->get(UserService::class);

        // Test user creation and retrieval
        $retrievedUser = $userService->getUserById($user->id);
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->name, $retrievedUser->name);

        // Test that user is cached
        $cacheKey = 'user_id_' . $user->id;
        $cachedUser = Cache::get($cacheKey);
        $this->assertNotNull($cachedUser);

        // Test cache clearing
        $userService->clearUserCache($user->id);
        $cachedUserAfterClear = Cache::get($cacheKey);
        $this->assertNull($cachedUserAfterClear);

        // Clean up
        $user->delete();
    }

    public function test_user_service_get_all_caching()
    {
        $userService = $this->container->get(UserService::class);

        // Get all users (should be cached)
        $users = $userService->getAllUsers();
        $this->assertIsIterable($users);

        // Verify cache was set
        $cachedUsers = Cache::get('user_all_users');
        $this->assertNotNull($cachedUsers);

        // Clean up cache
        $userService->clearUserCache();
        $cachedUsersAfterClear = Cache::get('user_all_users');
        $this->assertNull($cachedUsersAfterClear);
    }
}