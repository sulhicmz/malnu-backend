<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Hypervel\Support\Facades\Hash;

class RoleTest extends TestCase
{
    /**
     * Test role model can be created with required fields.
     */
    public function testRoleCanBeCreated(): void
    {
        $role = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('admin', $role->name);
        $this->assertEquals('web', $role->guard_name);
    }

    /**
     * Test role can be assigned to a user.
     */
    public function testRoleCanBeAssignedToUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $role = Role::create([
            'name' => 'teacher',
            'guard_name' => 'web',
        ]);

        $role->assignTo($user);

        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
    }

    /**
     * Test role can have permissions attached.
     */
    public function testRoleCanHavePermissions(): void
    {
        $role = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $permission = Permission::create([
            'name' => 'manage-users',
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo($permission);

        $this->assertTrue($role->permissions()->where('name', 'manage-users')->exists());
    }

    /**
     * Test role model fillable attributes.
     */
    public function testRoleFillableAttributes(): void
    {
        $fillable = ['name', 'guard_name'];

        $role = new Role();
        $this->assertEquals($fillable, $role->getFillable());
    }

    /**
     * Test role primary key configuration.
     */
    public function testRolePrimaryKeyConfiguration(): void
    {
        $role = new Role();
        
        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->incrementing);
    }
}