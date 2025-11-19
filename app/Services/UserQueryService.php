<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\CacheService;
use Hyperf\Di\Annotation\Inject;

class UserQueryService
{
    #[Inject]
    protected CacheService $cacheService;

    private string $cachePrefix = 'user_query_';

    /**
     * Get users with optimized query to prevent N+1
     * This method demonstrates eager loading to prevent N+1 queries
     */
    public function getUsersWithEagerLoading(): array
    {
        $cacheKey = $this->cachePrefix . 'users_with_eager_loading';
        
        return $this->cacheService->getWithCache($cacheKey, function () {
            // Use eager loading to prevent N+1 queries
            return User::with([
                'teacher:id,user_id,subject_id,employment_date,qualification,experience_years',
                'student:id,user_id,class_id,enrollment_date,graduation_year',
                'staff:id,user_id,position,department,hire_date',
                'parent:id,user_id,student_id,relationship,contact_info'
            ])->get()->toArray();
        }, 1800); // Cache for 30 minutes
    }

    /**
     * Get users with specific relationships based on role
     */
    public function getUsersByRoleWithEagerLoading(string $role): array
    {
        $cacheKey = $this->cachePrefix . 'users_by_role_' . md5($role);
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($role) {
            $query = User::query();
            
            // Apply role-based eager loading
            switch (strtolower($role)) {
                case 'teacher':
                    $query->with(['teacher']);
                    break;
                case 'student':
                    $query->with(['student']);
                    break;
                case 'staff':
                    $query->with(['staff']);
                    break;
                case 'parent':
                    $query->with(['parent']);
                    break;
                default:
                    $query->with(['teacher', 'student', 'staff', 'parent']);
                    break;
            }
            
            return $query->get()->toArray();
        }, 1800); // Cache for 30 minutes
    }

    /**
     * Get user with optimized relationships
     */
    public function getUserWithEagerLoading(string $id): ?array
    {
        $cacheKey = $this->cachePrefix . 'user_with_eager_' . $id;
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($id) {
            return User::with([
                'teacher:id,user_id,subject_id,employment_date,qualification,experience_years',
                'student:id,user_id,class_id,enrollment_date,graduation_year',
                'staff:id,user_id,position,department,hire_date',
                'parent:id,user_id,student_id,relationship,contact_info'
            ])->find($id)?->toArray();
        }, 3600); // Cache for 1 hour
    }

    /**
     * Search users with optimized query
     */
    public function searchUsers(string $searchTerm, int $limit = 20): array
    {
        $cacheKey = $this->cachePrefix . 'search_' . md5($searchTerm . $limit);
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($searchTerm, $limit) {
            return User::where(function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('full_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('username', 'LIKE', "%{$searchTerm}%");
                })
                ->with([
                    'teacher:id,user_id,subject_id,employment_date,qualification,experience_years',
                    'student:id,user_id,class_id,enrollment_date,graduation_year',
                    'staff:id,user_id,position,department,hire_date',
                    'parent:id,user_id,student_id,relationship,contact_info'
                ])
                ->limit($limit)
                ->get()
                ->toArray();
        }, 900); // Cache for 15 minutes
    }

    /**
     * Get active users count (cached)
     */
    public function getActiveUsersCount(): int
    {
        $cacheKey = $this->cachePrefix . 'active_users_count';
        
        return $this->cacheService->getWithCache($cacheKey, function () {
            return User::where('is_active', true)->count();
        }, 300); // Cache for 5 minutes
    }

    /**
     * Get users statistics
     */
    public function getUsersStats(): array
    {
        $cacheKey = $this->cachePrefix . 'users_stats';
        
        return $this->cacheService->getWithCache($cacheKey, function () {
            return [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        }, 600); // Cache for 10 minutes
    }
}