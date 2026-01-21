<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Services\CacheService;

class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = make(CacheService::class);
        $this->cacheService->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cacheService->flush();
    }

    public function test_cache_set_and_retrieve(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test_value'];

        $result = $this->cacheService->set($key, $value, 3600);

        $this->assertTrue($result);

        $retrievedValue = $this->cacheService->get($key);

        $this->assertEquals($value, $retrievedValue);
    }

    public function test_cache_hit(): void
    {
        $key = 'test_hit_key';
        $value = ['data' => 'test_value'];

        $this->cacheService->set($key, $value, 3600);

        $retrievedValue = $this->cacheService->get($key);

        $this->assertNotNull($retrievedValue);
        $this->assertEquals($value, $retrievedValue);
    }

    public function test_cache_miss(): void
    {
        $key = 'test_miss_key';
        $defaultValue = 'default_value';

        $retrievedValue = $this->cacheService->get($key, $defaultValue);

        $this->assertEquals($defaultValue, $retrievedValue);
    }

    public function test_cache_forget(): void
    {
        $key = 'test_forget_key';
        $value = ['data' => 'test_value'];

        $this->cacheService->set($key, $value, 3600);

        $retrievedValue = $this->cacheService->get($key);
        $this->assertNotNull($retrievedValue);

        $this->cacheService->forget($key);

        $retrievedValue = $this->cacheService->get($key);
        $this->assertNull($retrievedValue);
    }

    public function test_cache_has(): void
    {
        $key = 'test_has_key';
        $value = ['data' => 'test_value'];

        $this->assertFalse($this->cacheService->has($key));

        $this->cacheService->set($key, $value, 3600);

        $this->assertTrue($this->cacheService->has($key));
    }

    public function test_cache_remember_with_callback(): void
    {
        $key = 'test_remember_key';
        $value = ['data' => 'calculated_value'];
        $executed = false;

        $callback = function() use ($value, &$executed) {
            $executed = true;
            return $value;
        };

        $result = $this->cacheService->remember($key, 3600, $callback);

        $this->assertTrue($executed);
        $this->assertEquals($value, $result);

        $executed = false;
        $result = $this->cacheService->remember($key, 3600, $callback);

        $this->assertFalse($executed);
        $this->assertEquals($value, $result);
    }

    public function test_remember_user(): void
    {
        $userId = 'user_123';
        $userData = ['id' => $userId, 'name' => 'Test User'];

        $result = $this->cacheService->rememberUser($userId, function() use ($userData) {
            return $userData;
        });

        $this->assertEquals($userData, $result);
    }

    public function test_forget_user(): void
    {
        $userId = 'user_123';
        $userData = ['id' => $userId, 'name' => 'Test User'];

        $this->cacheService->rememberUser($userId, function() use ($userData) {
            return $userData;
        });

        $result = $this->cacheService->get("user:{$userId}");
        $this->assertEquals($userData, $result);

        $this->cacheService->forgetUser($userId);

        $result = $this->cacheService->get("user:{$userId}");
        $this->assertNull($result);
    }

    public function test_remember_role(): void
    {
        $roleId = 'role_admin';
        $roleData = ['id' => $roleId, 'name' => 'Admin'];

        $result = $this->cacheService->rememberRole($roleId, function() use ($roleData) {
            return $roleData;
        });

        $this->assertEquals($roleData, $result);
    }

    public function test_forget_role(): void
    {
        $roleId = 'role_admin';
        $roleData = ['id' => $roleId, 'name' => 'Admin'];

        $this->cacheService->rememberRole($roleId, function() use ($roleData) {
            return $roleData;
        });

        $result = $this->cacheService->get("role:{$roleId}");
        $this->assertEquals($roleData, $result);

        $this->cacheService->forgetRole($roleId);

        $result = $this->cacheService->get("role:{$roleId}");
        $this->assertNull($result);
    }

    public function test_remember_permissions(): void
    {
        $userId = 'user_123';
        $permissions = ['read', 'write', 'delete'];

        $result = $this->cacheService->rememberPermissions($userId, function() use ($permissions) {
            return $permissions;
        });

        $this->assertEquals($permissions, $result);
    }

    public function test_remember_roles(): void
    {
        $userId = 'user_123';
        $roles = ['admin', 'teacher'];

        $result = $this->cacheService->rememberRoles($userId, function() use ($roles) {
            return $roles;
        });

        $this->assertEquals($roles, $result);
    }

    public function test_remember_students(): void
    {
        $classId = 'class_123';
        $students = [['id' => 's1'], ['id' => 's2']];

        $result = $this->cacheService->rememberStudents($classId, function() use ($students) {
            return $students;
        });

        $this->assertEquals($students, $result);
    }

    public function test_remember_teachers(): void
    {
        $classId = 'class_123';
        $teachers = [['id' => 't1'], ['id' => 't2']];

        $result = $this->cacheService->rememberTeachers($classId, function() use ($teachers) {
            return $teachers;
        });

        $this->assertEquals($teachers, $result);
    }

    public function test_flush_cache(): void
    {
        $key1 = 'flush_test_key_1';
        $key2 = 'flush_test_key_2';

        $this->cacheService->set($key1, 'value1', 3600);
        $this->cacheService->set($key2, 'value2', 3600);

        $this->assertTrue($this->cacheService->has($key1));
        $this->assertTrue($this->cacheService->has($key2));

        $this->cacheService->flush();

        $this->assertFalse($this->cacheService->has($key1));
        $this->assertFalse($this->cacheService->has($key2));
    }
}