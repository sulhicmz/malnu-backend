<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Hypervel\Cache\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const USER_CACHE_PREFIX = 'user_';
    private const USERS_ALL_CACHE_KEY = 'users_all';
    private const USERS_PAGINATED_CACHE_PREFIX = 'users_paginated_';

    /**
     * Get all users with caching
     */
    public function getAllUsers(): Collection
    {
        return Cache::remember(USERS_ALL_CACHE_KEY, self::CACHE_TTL, function () {
            return User::with(['student', 'teacher', 'staff', 'parent'])->get();
        });
    }

    /**
     * Get users with pagination and caching
     */
    public function getUsersPaginated(int $perPage = 15): mixed
    {
        $cacheKey = self::USERS_PAGINATED_CACHE_PREFIX . $perPage;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return User::with(['student', 'teacher', 'staff', 'parent'])
                ->paginate($perPage);
        });
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(string $id): ?User
    {
        $cacheKey = self::USER_CACHE_PREFIX . $id;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return User::with(['student', 'teacher', 'staff', 'parent'])->find($id);
        });
    }

    /**
     * Get user by email with caching
     */
    public function getUserByEmail(string $email): ?User
    {
        $cacheKey = self::USER_CACHE_PREFIX . 'email_' . md5($email);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($email) {
            return User::where('email', $email)->with(['student', 'teacher', 'staff', 'parent'])->first();
        });
    }

    /**
     * Create or update user and clear relevant caches
     */
    public function createOrUpdateUser(array $data, ?string $id = null): User
    {
        if ($id) {
            $user = User::find($id);
            if (!$user) {
                throw new \Exception('User not found');
            }
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        // Clear related caches
        $this->clearUserCache($user->id);
        $this->clearAllUsersCache();
        
        return $user;
    }

    /**
     * Delete user and clear caches
     */
    public function deleteUser(string $id): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        $result = $user->delete();

        // Clear related caches
        $this->clearUserCache($id);
        $this->clearAllUsersCache();
        
        return $result;
    }

    /**
     * Get user roles with caching
     */
    public function getUserRoles(User $user): Collection
    {
        $cacheKey = self::USER_CACHE_PREFIX . $user->id . '_roles';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $user->roles ?? collect([]);
        });
    }

    /**
     * Clear cache for a specific user
     */
    public function clearUserCache(string $userId): void
    {
        Cache::forget(self::USER_CACHE_PREFIX . $userId);
        Cache::forget(self::USER_CACHE_PREFIX . 'email_' . md5($this->getUserEmailById($userId)));
    }

    /**
     * Clear all users cache
     */
    public function clearAllUsersCache(): void
    {
        Cache::forget(USERS_ALL_CACHE_KEY);
        
        // Clear paginated caches for common page sizes
        for ($i = 10; $i <= 50; $i += 10) {
            Cache::forget(self::USERS_PAGINATED_CACHE_PREFIX . $i);
        }
    }

    /**
     * Get user email by ID (helper method)
     */
    private function getUserEmailById(string $userId): string
    {
        $user = User::find($userId);
        return $user ? $user->email : '';
    }
}