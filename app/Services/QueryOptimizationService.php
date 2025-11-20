<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Hypervel\Cache\Facades\Cache;

class QueryOptimizationService
{
    /**
     * Get users with roles using eager loading to prevent N+1 queries
     */
    public function getUsersWithRolesOptimized(): mixed
    {
        $cacheKey = 'users_with_roles_optimized';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Using eager loading to prevent N+1 queries
            return User::with(['roles', 'permissions'])->get();
        });
    }

    /**
     * Get roles with permissions using eager loading
     */
    public function getRolesWithPermissionsOptimized(): mixed
    {
        $cacheKey = 'roles_with_permissions_optimized';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Using eager loading to prevent N+1 queries
            return Role::with(['permissions'])->get();
        });
    }

    /**
     * Get permissions with roles using eager loading
     */
    public function getPermissionsWithRolesOptimized(): mixed
    {
        $cacheKey = 'permissions_with_roles_optimized';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Using eager loading to prevent N+1 queries
            return Permission::with(['roles'])->get();
        });
    }

    /**
     * Get users with multiple relationships optimized
     */
    public function getUsersWithMultipleRelationships(): mixed
    {
        $cacheKey = 'users_with_multiple_relationships';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Using eager loading for multiple relationships to prevent N+1 queries
            return User::with([
                'student',
                'teacher', 
                'staff',
                'parent',
                'roles',
                'permissions',
                'assignmentsCreated',
                'quizzesCreated',
                'discussionsCreated'
            ])->get();
        });
    }

    /**
     * Get paginated users with relationships optimized
     */
    public function getPaginatedUsersWithRelationships(int $perPage = 15): mixed
    {
        $cacheKey = "paginated_users_with_relationships_{$perPage}";
        
        return Cache::remember($cacheKey, 1800, function () use ($perPage) {
            // Using eager loading with pagination to prevent N+1 queries
            return User::with([
                'student',
                'teacher',
                'staff',
                'parent',
                'roles'
            ])->paginate($perPage);
        });
    }

    /**
     * Example of using select with specific columns to optimize queries
     */
    public function getUsersWithSpecificColumns(): mixed
    {
        $cacheKey = 'users_specific_columns';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Select only needed columns to optimize query performance
            return User::select(['id', 'name', 'email', 'full_name', 'is_active', 'created_at'])
                ->with(['roles:id,name'])
                ->get();
        });
    }

    /**
     * Example of using whereHas to optimize queries with conditions on relationships
     */
    public function getUsersWithRoleCondition(string $roleName): mixed
    {
        $cacheKey = "users_with_role_{$roleName}";
        
        return Cache::remember($cacheKey, 3600, function () use ($roleName) {
            // Using whereHas to optimize queries with relationship conditions
            return User::whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })->with(['roles', 'student', 'teacher'])->get();
        });
    }

    /**
     * Example of using has to optimize queries with relationship existence checks
     */
    public function getUsersWithAnyRole(): mixed
    {
        $cacheKey = 'users_with_any_role';
        
        return Cache::remember($cacheKey, 3600, function () {
            // Using has to optimize queries with relationship existence checks
            return User::has('roles')
                ->with(['roles', 'student', 'teacher'])
                ->get();
        });
    }

    /**
     * Clear optimization-related caches
     */
    public function clearOptimizationCaches(): void
    {
        $cacheKeys = [
            'users_with_roles_optimized',
            'roles_with_permissions_optimized',
            'permissions_with_roles_optimized',
            'users_with_multiple_relationships',
            'users_specific_columns',
            'users_with_any_role'
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear paginated caches
        for ($i = 10; $i <= 50; $i += 10) {
            Cache::forget("paginated_users_with_relationships_{$i}");
        }
    }
}