<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Hypervel\Cache\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const USERS_ALL_CACHE_KEY = 'users:all';
    private const USER_BY_ID_CACHE_KEY = 'user:id:%s';
    private const USERS_COUNT_CACHE_KEY = 'users:count';

    /**
     * Get all users with caching
     */
    public function getAllUsers(): Collection
    {
        return Cache::remember(self::USERS_ALL_CACHE_KEY, self::CACHE_TTL, function () {
            return User::with(['student', 'teacher', 'parent', 'staff'])->get();
        });
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(string $id): ?User
    {
        $cacheKey = sprintf(self::USER_BY_ID_CACHE_KEY, $id);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return User::with(['student', 'teacher', 'parent', 'staff'])->find($id);
        });
    }

    /**
     * Get users count with caching
     */
    public function getUsersCount(): int
    {
        return Cache::remember(self::USERS_COUNT_CACHE_KEY, self::CACHE_TTL, function () {
            return User::count();
        });
    }

    /**
     * Create a new user and update cache
     */
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Clear related cache entries
        $this->clearUserCache();
        
        return $user;
    }

    /**
     * Update user and update cache
     */
    public function updateUser(string $id, array $data): ?User
    {
        $user = User::find($id);
        
        if ($user) {
            $user->update($data);
            
            // Clear related cache entries
            $this->clearUserCache($id);
        }
        
        return $user;
    }

    /**
     * Delete user and update cache
     */
    public function deleteUser(string $id): bool
    {
        $result = User::destroy($id);
        
        if ($result) {
            // Clear related cache entries
            $this->clearUserCache($id);
        }
        
        return $result;
    }

    /**
     * Clear user-related cache entries
     */
    public function clearUserCache(?string $id = null): void
    {
        if ($id) {
            $cacheKey = sprintf(self::USER_BY_ID_CACHE_KEY, $id);
            Cache::forget($cacheKey);
        } else {
            Cache::forget(self::USERS_ALL_CACHE_KEY);
            Cache::forget(self::USERS_COUNT_CACHE_KEY);
        }
    }

    /**
     * Clear all user cache
     */
    public function clearAllUserCache(): void
    {
        Cache::forget(self::USERS_ALL_CACHE_KEY);
        Cache::forget(self::USERS_COUNT_CACHE_KEY);
    }
}