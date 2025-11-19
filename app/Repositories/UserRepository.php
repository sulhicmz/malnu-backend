<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Services\CacheService;
use Hyperf\Di\Annotation\Inject;

class UserRepository
{
    #[Inject]
    protected CacheService $cacheService;

    private string $cachePrefix = 'user_';

    /**
     * Get all users with caching
     */
    public function getAll(): array
    {
        $cacheKey = $this->cachePrefix . 'all_users';
        
        return $this->cacheService->getWithCache($cacheKey, function () {
            return User::with(['teacher', 'student', 'staff', 'parent'])->get()->toArray();
        }, 3600); // Cache for 1 hour
    }

    /**
     * Get user by ID with caching
     */
    public function findById(string $id): ?array
    {
        $cacheKey = $this->cachePrefix . 'id_' . $id;
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($id) {
            $user = User::with(['teacher', 'student', 'staff', 'parent'])->find($id);
            return $user ? $user->toArray() : null;
        }, 3600); // Cache for 1 hour
    }

    /**
     * Get user by email with caching
     */
    public function findByEmail(string $email): ?array
    {
        $cacheKey = $this->cachePrefix . 'email_' . md5($email);
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($email) {
            $user = User::where('email', $email)->with(['teacher', 'student', 'staff', 'parent'])->first();
            return $user ? $user->toArray() : null;
        }, 3600); // Cache for 1 hour
    }

    /**
     * Create a new user
     */
    public function create(array $data): array
    {
        $user = User::create($data);
        
        // Clear relevant cache entries
        $this->clearUserCache();
        
        return $user->toArray();
    }

    /**
     * Update user
     */
    public function update(string $id, array $data): array
    {
        $user = User::find($id);
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        $user->update($data);
        
        // Clear relevant cache entries
        $this->clearUserCache($id);
        
        return $user->toArray();
    }

    /**
     * Delete user
     */
    public function delete(string $id): bool
    {
        $result = User::destroy($id);
        
        // Clear relevant cache entries
        $this->clearUserCache($id);
        
        return $result > 0;
    }

    /**
     * Clear user-related cache
     */
    private function clearUserCache(?string $id = null): void
    {
        if ($id) {
            $this->cacheService->deleteFromCache($this->cachePrefix . 'id_' . $id);
            $user = User::find($id);
            if ($user) {
                $this->cacheService->deleteFromCache($this->cachePrefix . 'email_' . md5($user->email));
            }
        } else {
            $this->cacheService->deleteFromCache($this->cachePrefix . 'all_users');
        }
    }

    /**
     * Get users with pagination and caching
     */
    public function paginate(int $perPage = 15): array
    {
        $cacheKey = $this->cachePrefix . 'paginated_' . $perPage;
        
        return $this->cacheService->getWithCache($cacheKey, function () use ($perPage) {
            return User::with(['teacher', 'student', 'staff', 'parent'])
                ->paginate($perPage)
                ->toArray();
        }, 1800); // Cache for 30 minutes
    }
}