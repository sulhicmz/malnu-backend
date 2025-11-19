<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Hypervel\Support\Facades\Cache;

class UserService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'user_';

    /**
     * Get all users with caching
     */
    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = self::CACHE_PREFIX . 'all_users';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return User::with(['student', 'teacher', 'parent', 'staff'])->get();
        });
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(string $id): ?User
    {
        $cacheKey = self::CACHE_PREFIX . "id_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return User::with(['student', 'teacher', 'parent', 'staff'])->find($id);
        });
    }

    /**
     * Get user by email with caching
     */
    public function getUserByEmail(string $email): ?User
    {
        $cacheKey = self::CACHE_PREFIX . "email_{$email}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($email) {
            return User::where('email', $email)->first();
        });
    }

    /**
     * Get users by role with caching
     */
    public function getUsersByRole(string $roleName): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = self::CACHE_PREFIX . "role_{$roleName}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($roleName) {
            return User::whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })->with(['student', 'teacher', 'parent', 'staff'])->get();
        });
    }

    /**
     * Create a new user and clear related cache
     */
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Clear related cache
        $this->clearUserCache();
        
        return $user;
    }

    /**
     * Update user and clear related cache
     */
    public function updateUser(string $id, array $data): bool
    {
        $user = User::find($id);
        
        if (!$user) {
            return false;
        }
        
        $user->update($data);
        
        // Clear related cache
        $this->clearUserCache($id);
        
        return true;
    }

    /**
     * Delete user and clear related cache
     */
    public function deleteUser(string $id): bool
    {
        $user = User::find($id);
        
        if (!$user) {
            return false;
        }
        
        $result = $user->delete();
        
        // Clear related cache
        $this->clearUserCache($id);
        
        return $result;
    }

    /**
     * Clear user cache
     */
    public function clearUserCache(?string $id = null): void
    {
        if ($id) {
            // Clear specific user cache
            Cache::forget(self::CACHE_PREFIX . "id_{$id}");
        } else {
            // Clear all user cache
            // Note: In a real application, you might want to use tags for cache invalidation
            // For now, we'll clear the all_users cache
            Cache::forget(self::CACHE_PREFIX . 'all_users');
        }
    }

    /**
     * Clear user cache by email
     */
    public function clearUserCacheByEmail(string $email): void
    {
        Cache::forget(self::CACHE_PREFIX . "email_{$email}");
    }

    /**
     * Clear user cache by role
     */
    public function clearUserCacheByRole(string $roleName): void
    {
        Cache::forget(self::CACHE_PREFIX . "role_{$roleName}");
    }
}