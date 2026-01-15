<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\CacheMonitoringService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CacheController extends BaseController
{
    private CacheMonitoringService $cacheMonitoring;

    public function __construct(CacheMonitoringService $cacheMonitoring)
    {
        parent::__construct();
        $this->cacheMonitoring = $cacheMonitoring;
    }

    public function getStatistics()
    {
        try {
            $stats = $this->cacheMonitoring->getStatistics();

            return $this->response->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to get cache statistics',
                    'code' => 'CACHE_STATS_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function getTopKeys()
    {
        try {
            $topKeys = $this->cacheMonitoring->getTopKeys();

            return $this->response->json([
                'success' => true,
                'data' => [
                    'top_keys' => $topKeys,
                ],
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to get top cache keys',
                    'code' => 'CACHE_TOP_KEYS_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function checkHealth()
    {
        try {
            $isHealthy = $this->cacheMonitoring->isPerformanceHealthy();

            return $this->response->json([
                'success' => true,
                'data' => [
                    'healthy' => $isHealthy,
                    'message' => $isHealthy
                        ? 'Cache performance is healthy'
                        : 'Cache performance needs attention',
                ],
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to check cache health',
                    'code' => 'CACHE_HEALTH_CHECK_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function resetStatistics()
    {
        try {
            $this->cacheMonitoring->resetStatistics();

            return $this->response->json([
                'success' => true,
                'message' => 'Cache statistics reset successfully',
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to reset cache statistics',
                    'code' => 'CACHE_STATS_RESET_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function clearAll()
    {
        try {
            $redis = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
            $redis->flushDB();

            return $this->response->json([
                'success' => true,
                'message' => 'All cache cleared successfully',
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to clear cache',
                    'code' => 'CACHE_CLEAR_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function clearByPattern()
    {
        try {
            $data = $this->request->all();

            if (!isset($data['pattern']) || empty($data['pattern'])) {
                return $this->response->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Pattern is required',
                        'code' => 'INVALID_PATTERN',
                    ],
                    'timestamp' => date('c'),
                ])->withStatus(400);
            }

            $pattern = $data['pattern'];
            $redis = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);

            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                $redis->del(...$keys);
            }

            return $this->response->json([
                'success' => true,
                'data' => [
                    'pattern' => $pattern,
                    'keys_cleared' => count($keys),
                ],
                'message' => 'Cache cleared by pattern successfully',
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to clear cache by pattern',
                    'code' => 'CACHE_CLEAR_PATTERN_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }

    public function warmUp()
    {
        try {
            $redis = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);

            $users = \App\Models\User::take(100)->get();
            foreach ($users as $user) {
                $user->setCached();
            }

            $roles = \App\Models\Role::all();
            foreach ($roles as $role) {
                $role->setCached();
            }

            $permissions = \App\Models\Permission::all();
            foreach ($permissions as $permission) {
                $permission->setCached();
            }

            return $this->response->json([
                'success' => true,
                'data' => [
                    'users_cached' => count($users),
                    'roles_cached' => count($roles),
                    'permissions_cached' => count($permissions),
                ],
                'message' => 'Cache warmed up successfully',
                'timestamp' => date('c'),
            ]);
        } catch (\Throwable $e) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to warm up cache',
                    'code' => 'CACHE_WARMUP_ERROR',
                ],
                'timestamp' => date('c'),
            ])->withStatus(500);
        }
    }
}
