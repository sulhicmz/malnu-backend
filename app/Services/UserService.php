<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Hypervel\Cache\CacheManager;
use Hypervel\Support\Facades\Cache;

class UserService
{
    protected CacheManager $cache;
    protected CacheMonitoringService $cacheMonitoring;

    public function __construct(CacheManager $cache, CacheMonitoringService $cacheMonitoring)
    {
        $this->cache = $cache;
        $this->cacheMonitoring = $cacheMonitoring;
    }

    /**
     * Get all users with caching
     */
    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'users.all';
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->cacheMonitoring->recordHit();
            return $cached;
        } else {
            $this->cacheMonitoring->recordMiss();
            $users = User::with(['roles', 'permissions'])->get();
            Cache::put($cacheKey, $users, 3600);
            return $users;
        }
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(string $id): ?User
    {
        $cacheKey = "user.{$id}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->cacheMonitoring->recordHit();
            return $cached;
        } else {
            $this->cacheMonitoring->recordMiss();
            $user = User::with(['roles', 'permissions'])->find($id);
            if ($user) {
                Cache::put($cacheKey, $user, 3600);
            }
            return $user;
        }
    }

    /**
     * Get user by email with caching
     */
    public function getUserByEmail(string $email): ?User
    {
        $cacheKey = "user.email.{$email}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->cacheMonitoring->recordHit();
            return $cached;
        } else {
            $this->cacheMonitoring->recordMiss();
            $user = User::where('email', $email)->first();
            if ($user) {
                Cache::put($cacheKey, $user, 3600);
            }
            return $user;
        }
    }

    /**
     * Create or update user and invalidate related cache
     */
    public function createUser(array $data): User
    {
        $user = User::create($data);
        
        // Invalidate related cache
        $this->invalidateUserCache();
        
        return $user;
    }

    /**
     * Update user and invalidate related cache
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        
        // Invalidate related cache
        $this->invalidateUserCache($user->id);
        
        return $user;
    }

    /**
     * Delete user and invalidate related cache
     */
    public function deleteUser(User $user): bool
    {
        $result = $user->delete();
        
        // Invalidate related cache
        $this->invalidateUserCache($user->id);
        
        return $result;
    }

    /**
     * Invalidate user-related cache
     */
    public function invalidateUserCache(?string $userId = null): void
    {
        if ($userId) {
            Cache::forget("user.{$userId}");
            Cache::forget("user.email.{$userId}");
        } else {
            Cache::forget('users.all');
            // In a real implementation, you might want to use tags or more sophisticated invalidation
        }
    }

    /**
     * Get active users with caching
     */
    public function getActiveUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'users.active';
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->cacheMonitoring->recordHit();
            return $cached;
        } else {
            $this->cacheMonitoring->recordMiss();
            $users = User::where('is_active', true)->with(['roles', 'permissions'])->get();
            Cache::put($cacheKey, $users, 1800);
            return $users;
        }
    }
}