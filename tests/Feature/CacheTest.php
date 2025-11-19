<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\UserService;
use Hypervel\Cache\Facades\Cache;
use Hypervel\Foundation\Testing\TestCase;

class CacheTest extends TestCase
{
    public function test_user_service_caches_results()
    {
        // Create a test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $userService = new UserService();
        
        // Clear any existing cache
        Cache::forget('users.all');
        
        // First call - should hit the database
        $users1 = $userService->getAllUsers();
        
        // Second call - should use cache
        $users2 = $userService->getAllUsers();
        
        // Both should return the same user
        $this->assertEquals($users1->count(), $users2->count());
        $this->assertEquals($user->id, $users1->first()->id);
        
        // Clean up
        $user->delete();
    }

    public function test_user_service_caches_individual_user()
    {
        // Create a test user
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => 'password',
        ]);

        $userService = new UserService();
        
        // Clear any existing cache
        $cacheKey = "user.{$user->id}";
        Cache::forget($cacheKey);
        
        // First call - should hit the database
        $retrievedUser1 = $userService->getUserById($user->id);
        
        // Second call - should use cache
        $retrievedUser2 = $userService->getUserById($user->id);
        
        // Both should return the same user
        $this->assertEquals($user->id, $retrievedUser1->id);
        $this->assertEquals($user->id, $retrievedUser2->id);
        
        // Clean up
        $user->delete();
    }
}