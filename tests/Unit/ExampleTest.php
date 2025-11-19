<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    /**
     * Test basic model creation.
     */
    public function testUserModelCanBeInstantiated(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->incrementing);
    }

    /**
     * Test role model creation.
     */
    public function testRoleModelCanBeInstantiated(): void
    {
        $role = new Role();
        
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->incrementing);
    }

    /**
     * Test permission model creation.
     */
    public function testPermissionModelCanBeInstantiated(): void
    {
        $permission = new Permission();
        
        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('id', $permission->getKeyName());
        $this->assertEquals('string', $permission->getKeyType());
        $this->assertFalse($permission->incrementing);
    }

    /**
     * Test basic string operations.
     */
    public function testStringOperations(): void
    {
        $string = 'Hello World';
        
        $this->assertEquals('Hello World', $string);
        $this->assertStringContainsString('World', $string);
        $this->assertEquals(11, strlen($string));
    }
}
