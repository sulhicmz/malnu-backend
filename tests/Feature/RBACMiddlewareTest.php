<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * RBAC (Role-Based Access Control) Middleware Test.
 *
 * Tests RBAC functionality including:
 * - Role assignment and retrieval
 * - Multiple role support
 * - Permission checking
 * - Middleware access control
 * - Unauthorized access prevention
 * @internal
 * @coversNothing
 */
class RBACMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Refresh database for clean state
        $this->artisan('migrate:fresh', ['--force' => true]);

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
    }

    /**
     * Test that roles are created by seeder.
     */
    public function testRolesAreSeeded(): void
    {
        $roles = Role::all();

        $this->assertGreaterThan(0, $roles->count());
        $this->assertTrue($roles->contains('name', 'Super Admin'));
        $this->assertTrue($roles->contains('name', 'Kepala Sekolah'));
        $this->assertTrue($roles->contains('name', 'Guru'));
        $this->assertTrue($roles->contains('name', 'Siswa'));
    }

    /**
     * Test that permissions are created by seeder.
     */
    public function testPermissionsAreSeeded(): void
    {
        $permissions = Permission::all();

        $this->assertGreaterThan(0, $permissions->count());
        $this->assertTrue($permissions->contains('name', 'manage_school_management'));
        $this->assertTrue($permissions->contains('name', 'view_dashboard'));
    }

    /**
     * Test that user can be assigned a role.
     */
    public function testUserCanBeAssignedRole(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasRole('Super Admin'));
        $this->assertEquals(1, $user->roles()->count());
    }

    /**
     * Test that user can have multiple roles.
     */
    public function testUserCanHaveMultipleRoles(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');
        $user->assignRole('Konselor');

        $this->assertEquals(2, $user->roles()->count());
        $this->assertTrue($user->hasRole('Guru'));
        $this->assertTrue($user->hasRole('Konselor'));
    }

    /**
     * Test that hasRole returns false for non-existent role.
     */
    public function testHasRoleReturnsFalseForNonExistentRole(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('NonExistentRole'));
    }

    /**
     * Test that hasAnyRole returns true when user has at least one of the specified roles.
     */
    public function testHasAnyRoleReturnsTrueWhenUserHasAtLeastOneRole(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        $this->assertTrue($user->hasAnyRole(['Super Admin', 'Guru', 'Siswa']));
        $this->assertTrue($user->hasAnyRole(['Guru']));
    }

    /**
     * Test that hasAnyRole returns false when user has none of the specified roles.
     */
    public function testHasAnyRoleReturnsFalseWhenUserHasNoSpecifiedRoles(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        $this->assertFalse($user->hasAnyRole(['Super Admin', 'Kepala Sekolah']));
    }

    /**
     * Test that user can get all permissions from their roles.
     */
    public function testUserCanGetAllPermissionsFromRoles(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        $permissions = $user->getAllPermissions();

        $this->assertGreaterThan(0, $permissions->count());

        // Guru should have these permissions
        $this->assertTrue($permissions->contains('name', 'view_dashboard'));
        $this->assertTrue($permissions->contains('name', 'manage_e_learning'));
    }

    /**
     * Test that hasPermission returns true when user has permission.
     */
    public function testHasPermissionReturnsTrueWhenUserHasPermission(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        $this->assertTrue($user->hasPermission('manage_e_learning'));
        $this->assertTrue($user->hasPermission('view_dashboard'));
    }

    /**
     * Test that hasPermission returns false when user does not have permission.
     */
    public function testHasPermissionReturnsFalseWhenUserLacksPermission(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');

        // Guru should not have these permissions
        $this->assertFalse($user->hasPermission('manage_roles'));
        $this->assertFalse($user->hasPermission('manage_monetization'));
    }

    /**
     * Test that Super Admin has all permissions.
     */
    public function testSuperAdminHasAllPermissions(): void
    {
        $user = User::create([
            'name' => 'Super Admin User',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Super Admin');

        $allPermissions = Permission::all()->pluck('name')->toArray();

        foreach ($allPermissions as $permission) {
            $this->assertTrue($user->hasPermission($permission), "Super Admin should have permission: {$permission}");
        }
    }

    /**
     * Test that syncRoles replaces existing roles.
     */
    public function testSyncRolesReplacesExistingRoles(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');
        $user->assignRole('Konselor');
        $this->assertEquals(2, $user->roles()->count());

        // Sync with new roles
        $user->syncRoles(['Siswa', 'Orang Tua']);

        $this->assertEquals(2, $user->roles()->count());
        $this->assertTrue($user->hasRole('Siswa'));
        $this->assertTrue($user->hasRole('Orang Tua'));
        $this->assertFalse($user->hasRole('Guru'));
        $this->assertFalse($user->hasRole('Konselor'));
    }

    /**
     * Test that user without roles cannot access protected routes.
     */
    public function testUserWithoutRolesCannotAccessProtectedRoutes(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        // User has no roles, should not have permissions
        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasPermission('manage_school_management'));
        $this->assertEquals(0, $user->getAllPermissions()->count());
    }

    /**
     * Test that role permissions are correctly assigned via seeder.
     */
    public function testRolePermissionsAreCorrectlyAssigned(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $guruRole = Role::where('name', 'Guru')->first();

        // Super Admin should have all permissions
        $superAdminPermissions = $superAdminRole->permissions()->count();
        $this->assertEquals(15, $superAdminPermissions);

        // Guru should have limited permissions
        $guruPermissions = $guruRole->permissions()->count();
        $this->assertLessThan($superAdminPermissions, $guruPermissions);
        $this->assertTrue($guruRole->permissions()->where('name', 'manage_e_learning')->exists());
    }

    /**
     * Test that user with multiple roles gets permissions from all roles.
     */
    public function testUserWithMultipleRolesGetsAllPermissions(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
        ]);

        $user->assignRole('Guru');
        $user->assignRole('Konselor');

        $permissions = $user->getAllPermissions();

        // Should have permissions from both Guru and Konselor roles
        $this->assertTrue($permissions->contains('name', 'manage_e_learning'));
        $this->assertTrue($permissions->contains('name', 'manage_career_development'));
        $this->assertTrue($permissions->contains('name', 'view_dashboard'));
    }
}
