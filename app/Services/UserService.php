<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Hypervel\Cache\Facades\Cache;

class UserService
{
    /**
     * Get all users with caching
     */
    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('users.all', 3600, function () {
            return User::with(['student', 'teacher', 'staff', 'parent'])->get();
        });
    }

    /**
     * Get user by ID with caching
     */
    public function getUserById(string $id): ?User
    {
        $cacheKey = "user.{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            return User::with(['student', 'teacher', 'staff', 'parent'])->find($id);
        });
    }

    /**
     * Clear user cache
     */
    public function clearUserCache(): void
    {
        Cache::forget('users.all');
        // In a real implementation, you might want to use tags to clear related caches
    }

    /**
     * Get users by role with caching
     */
    public function getUsersByRole(string $roleName): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "users.role.{$roleName}";
        
        return Cache::remember($cacheKey, 3600, function () use ($roleName) {
            return User::whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })->with(['student', 'teacher', 'staff', 'parent'])->get();
        });
    }
}