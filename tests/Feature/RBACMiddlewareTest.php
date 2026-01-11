<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\ModelHasRole;
use App\Models\Permission;
use App\Models\RoleHasPermission;

class RBACMiddlewareTest extends TestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean database
        ModelHasRole::query()->delete();
        RoleHasPermission::query()->delete();
        Permission::query()->delete();
        Role::query()->delete();
        User::query()->delete();

        // Create test roles
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'api']);
        $teacherRole = Role::create(['name' => 'Guru', 'guard_name' => 'api']);
        $studentRole = Role::create(['name' => 'Siswa', 'guard_name' => 'api']);

        // Create test user
        $this->testUser = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'is_active' => true,
        ]);

        // Assign Super Admin role to test user
        $this->testUser->assignRole('Super Admin');
    }

    public function test_user_has_role_relationship()
    {
        $this->testUser->assignRole('Guru');

        $roles = $this->testUser->roles()->get();
        
        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('name', 'Super Admin'));
        $this->assertTrue($roles->contains('name', 'Guru'));
    }

    public function test_user_has_role()
    {
        $result = $this->testUser->hasRole('Super Admin');
        
        $this->assertTrue($result);
    }

    public function test_user_does_not_have_role()
    {
        $result = $this->testUser->hasRole('Siswa');
        
        $this->assertFalse($result);
    }

    public function test_user_has_any_role()
    {
        $result = $this->testUser->hasAnyRole(['Super Admin', 'Siswa']);
        
        $this->assertTrue($result);
    }

    public function test_user_has_any_role_multiple()
    {
        $this->testUser->assignRole('Siswa');
        
        $result = $this->testUser->hasAnyRole(['Kepala Sekolah', 'Siswa']);
        
        $this->assertTrue($result);
    }

    public function test_user_does_not_have_any_role()
    {
        $result = $this->testUser->hasAnyRole(['Kepala Sekolah', 'Orang Tua']);
        
        $this->assertFalse($result);
    }

    public function test_get_all_permissions()
    {
        // Create permissions
        $permission1 = Permission::create(['name' => 'create_student', 'guard_name' => 'api']);
        $permission2 = Permission::create(['name' => 'view_attendance', 'guard_name' => 'api']);
        
        // Create role with permissions
        $teacherRole = Role::where('name', 'Guru')->first();
        $teacherRole->permissions()->attach([$permission1->id, $permission2->id]);
        
        // Assign role to user
        $this->testUser->assignRole('Guru');
        
        // Get all permissions
        $permissions = $this->testUser->getAllPermissions();
        
        $this->assertCount(2, $permissions);
        $this->assertTrue(collect($permissions)->contains('name', 'create_student'));
        $this->assertTrue(collect($permissions)->contains('name', 'view_attendance'));
    }

    public function test_user_has_permission()
    {
        // Create permission
        $permission = Permission::create(['name' => 'manage_users', 'guard_name' => 'api']);
        
        // Create role with permission
        $adminRole = Role::where('name', 'Super Admin')->first();
        $adminRole->permissions()->attach($permission->id);
        
        // Test has permission
        $result = $this->testUser->hasPermission('manage_users');
        
        $this->assertTrue($result);
    }

    public function test_user_does_not_have_permission()
    {
        // Create permission
        $permission = Permission::create(['name' => 'delete_system', 'guard_name' => 'api']);
        
        // Create role with permission
        $studentRole = Role::where('name', 'Siswa')->first();
        $studentRole->permissions()->attach($permission->id);
        
        // Test has permission (user doesn't have student role)
        $result = $this->testUser->hasPermission('delete_system');
        
        $this->assertFalse($result);
    }

    public function test_sync_roles_removes_old_roles()
    {
        // Verify initial role count
        $this->assertTrue($this->testUser->hasRole('Super Admin'));
        
        // Sync to new roles
        $this->testUser->syncRoles(['Guru', 'Siswa']);
        
        // Verify old role is removed
        $this->assertFalse($this->testUser->hasRole('Super Admin'));
        
        // Verify new roles are added
        $this->assertTrue($this->testUser->hasRole('Guru'));
        $this->assertTrue($this->testUser->hasRole('Siswa'));
        
        // Verify only 2 roles remain
        $roles = $this->testUser->roles()->get();
        $this->assertCount(2, $roles);
    }

    public function test_assign_role_persists_to_database()
    {
        $testUser = User::create([
            'name' => 'Another User',
            'username' => 'anotheruser',
            'email' => 'another@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'full_name' => 'Another User',
            'is_active' => true,
        ]);

        $testUser->assignRole('Siswa');

        // Refresh from database
        $freshUser = User::with('roles')->find($testUser->id);
        
        $this->assertCount(1, $freshUser->roles);
        $this->assertEquals('Siswa', $freshUser->roles->first()->name);
    }
}
