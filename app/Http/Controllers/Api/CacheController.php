<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\CacheMonitoringService;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;

class CacheController extends BaseController
{
    #[Inject]
    private CacheMonitoringService $cacheMonitoring;

    /**
     * Get cache statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->cacheMonitoring->getStatistics();
            return $this->successResponse($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get top accessed cache keys
     */
    public function getTopKeys()
    {
        try {
            $limit = (int) $this->request->input('limit', 10);
            $topKeys = $this->cacheMonitoring->getTopKeys($limit);
            return $this->successResponse($topKeys, 'Top cache keys retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Check if cache performance is healthy
     */
    public function checkHealth()
    {
        try {
            $isHealthy = $this->cacheMonitoring->isPerformanceHealthy();
            $stats = $this->cacheMonitoring->getStatistics();

            return $this->successResponse([
                'healthy' => $isHealthy,
                'statistics' => $stats,
            ], $isHealthy ? 'Cache performance is healthy' : 'Cache performance is below target');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Reset cache statistics
     */
    public function resetStatistics()
    {
        try {
            $this->cacheMonitoring->resetStatistics();
            return $this->successResponse(null, 'Cache statistics reset successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Clear all cache
     */
    public function clearAll()
    {
        try {
            \Hyperf\Cache\Cache::clear();
            return $this->successResponse(null, 'All cache cleared successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Clear cache by pattern
     */
    public function clearByPattern()
    {
        try {
            $pattern = $this->request->input('pattern');
            if (empty($pattern)) {
                return $this->validationErrorResponse(['pattern' => ['Pattern is required']]);
            }

            $redis = \Hyperf\Redis\Redis::connection();
            $keys = $redis->keys($pattern);

            if (count($keys) > 0) {
                $redis->del(...$keys);
            }

            return $this->successResponse([
                'cleared_keys' => count($keys),
                'pattern' => $pattern,
            ], 'Cache cleared by pattern successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Warm up cache for common data
     */
    public function warmUp()
    {
        try {
            $warmed = [
                'users' => 0,
                'roles' => 0,
                'permissions' => 0,
            ];

            $userCount = \App\Models\User::count();
            if ($userCount <= 100) {
                $users = \App\Models\User::all();
                foreach ($users as $user) {
                    \App\Models\User::getCached($user->id);
                    $warmed['users']++;
                }
            }

            $roleCount = \App\Models\Role::count();
            if ($roleCount <= 50) {
                $roles = \App\Models\Role::all();
                foreach ($roles as $role) {
                    \App\Models\Role::getCached($role->id);
                    $warmed['roles']++;
                }
            }

            $permissionCount = \App\Models\Permission::count();
            if ($permissionCount <= 200) {
                $permissions = \App\Models\Permission::all();
                foreach ($permissions as $permission) {
                    \App\Models\Permission::getCached($permission->id);
                    $warmed['permissions']++;
                }
            }

            return $this->successResponse($warmed, 'Cache warmed up successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
