<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\CacheInterface;

class CacheService
{
    #[Inject]
    private ApplicationContext $context;

    private CacheInterface $cache;

    public function __construct()
    {
        $this->cache = $this->context->getContainer()->get(CacheInterface::class);
    }

    private function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cache = $this->getCache();
        $value = $cache->get($key);
        
        return $value !== null ? $value : $default;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $cache = $this->getCache();
        return $cache->set($key, $value, $ttl);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cache = $this->getCache();
        $value = $cache->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $cache->set($key, $value, $ttl);
        
        return $value;
    }

    public function forget(string $key): bool
    {
        $cache = $this->getCache();
        return $cache->delete($key);
    }

    public function flush(): bool
    {
        $cache = $this->getCache();
        return $cache->clear();
    }

    public function has(string $key): bool
    {
        $cache = $this->getCache();
        return $cache->has($key);
    }

    public function rememberUser(string $userId, callable $callback): mixed
    {
        $key = "user:{$userId}";
        return $this->remember($key, 3600, $callback);
    }

    public function rememberRole(string $roleId, callable $callback): mixed
    {
        $key = "role:{$roleId}";
        return $this->remember($key, 7200, $callback);
    }

    public function rememberPermissions(string $userId, callable $callback): mixed
    {
        $key = "user:{$userId}:permissions";
        return $this->remember($key, 1800, $callback);
    }

    public function rememberRoles(string $userId, callable $callback): mixed
    {
        $key = "user:{$userId}:roles";
        return $this->remember($key, 1800, $callback);
    }

    public function rememberStudents(string $classId, callable $callback): mixed
    {
        $key = "class:{$classId}:students";
        return $this->remember($key, 600, $callback);
    }

    public function rememberTeachers(string $classId, callable $callback): mixed
    {
        $key = "class:{$classId}:teachers";
        return $this->remember($key, 600, $callback);
    }

    public function forgetUser(string $userId): bool
    {
        $this->forget("user:{$userId}");
        $this->forget("user:{$userId}:permissions");
        $this->forget("user:{$userId}:roles");
        
        return true;
    }

    public function forgetRole(string $roleId): bool
    {
        $this->forget("role:{$roleId}");
        
        return true;
    }

    public function forgetClass(string $classId): bool
    {
        $this->forget("class:{$classId}:students");
        $this->forget("class:{$classId}:teachers");
        
        return true;
    }
}