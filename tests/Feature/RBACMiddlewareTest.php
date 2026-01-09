<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\RoleHasPermission;
use App\Services\AuthService;
use App\Services\JWTService;

class RBACMiddlewareTest extends TestCase
{
    private AuthService $authService;
    private JWTService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        $this->jwtService = new JWTService();
    }

    private function createTestUser(array $data = [])
    {
        $defaultData = [
            'name' => 'Test User',
            'email' => 'test' . time() . '@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_active' => true,
        ];

        $user = User::create(array_merge($defaultData, $data));
        return $user;
    }

    private function createTestRole(string $roleName)
    {
        return Role::firstOrCreate(
            ['name' => $roleName, 'guard_name' => 'web'],
            ['guard_name' => 'web']
        );
    }

    private function createTestPermission(string $permissionName)
    {
        return Permission::firstOrCreate(
            ['name' => $permissionName, 'guard_name' => 'web'],
            ['guard_name' => 'web']
        );
    }

    public function test_user_can_be_assigned_role()
    {
        $user = $this->createTestUser();
        $role = $this->createTestRole('Test Role');

        $user->assignRole('Test Role');

        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => User::class,
            'model_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_have_multiple_roles()
    {
        $user = $this->createTestUser();
        $role1 = $this->createTestRole('Role 1');
        $role2 = $this->createTestRole('Role 2');

        $user->assignRole('Role 1');
        $user->assignRole('Role 2');

        $this->assertTrue($user->hasRole('Role 1'));
        $this->assertTrue($user->hasRole('Role 2'));
        $this->assertEquals(2, $user->roles()->count());
    }

    public function test_user_has_role_method_returns_true_for_assigned_role()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Teacher');

        $user->assignRole('Teacher');

        $this->assertTrue($user->hasRole('Teacher'));
    }

    public function test_user_has_role_method_returns_false_for_unassigned_role()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Student');

        $user->assignRole('Student');

        $this->assertFalse($user->hasRole('Teacher'));
    }

    public function test_user_has_any_role_method()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Student');
        $this->createTestRole('Teacher');

        $user->assignRole('Student');

        $this->assertTrue($user->hasAnyRole(['Student', 'Teacher']));
        $this->assertTrue($user->hasAnyRole(['Student']));
        $this->assertFalse($user->hasAnyRole(['Teacher', 'Admin']));
    }

    public function test_user_permissions_via_roles()
    {
        $user = $this->createTestUser();
        $role = $this->createTestRole('Test Role');
        $permission = $this->createTestPermission('test_permission');

        RoleHasPermission::create([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        $user->assignRole('Test Role');

        $permissions = $user->getAllPermissions();

        $this->assertTrue($permissions->contains('test_permission'));
    }

    public function test_user_has_permission_method()
    {
        $user = $this->createTestUser();
        $role = $this->createTestRole('Test Role');
        $permission = $this->createTestPermission('test_permission');

        RoleHasPermission::create([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        $user->assignRole('Test Role');

        $this->assertTrue($user->hasPermission('test_permission'));
        $this->assertFalse($user->hasPermission('non_existent_permission'));
    }

    public function test_sync_roles_replaces_existing_roles()
    {
        $user = $this->createTestUser();
        $role1 = $this->createTestRole('Role 1');
        $role2 = $this->createTestRole('Role 2');
        $role3 = $this->createTestRole('Role 3');

        $user->assignRole('Role 1');
        $user->assignRole('Role 2');

        $this->assertEquals(2, $user->roles()->count());

        $user->syncRoles(['Role 3']);

        $this->assertEquals(1, $user->roles()->count());
        $this->assertTrue($user->hasRole('Role 3'));
        $this->assertFalse($user->hasRole('Role 1'));
        $this->assertFalse($user->hasRole('Role 2'));
    }

    public function test_role_middleware_denies_access_without_required_role()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Student');
        $user->assignRole('Student');

        $token = $this->jwtService->generateToken(['user_id' => $user->id, 'email' => $user->email]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/school/students');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('FORBIDDEN', (string) $response->getBody());
    }

    public function test_role_middleware_allows_access_with_required_role()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Super Admin');
        $user->assignRole('Super Admin');

        $token = $this->jwtService->generateToken(['user_id' => $user->id, 'email' => $user->email]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/school/students');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_role_middleware_supports_multiple_roles_with_pipe()
    {
        $user1 = $this->createTestUser(['email' => 'teacher1@example.com']);
        $user2 = $this->createTestUser(['email' => 'admin1@example.com']);

        $this->createTestRole('Super Admin');
        $this->createTestRole('Kepala Sekolah');
        $this->createTestRole('Staf TU');

        $user1->assignRole('Kepala Sekolah');
        $user2->assignRole('Super Admin');

        $token1 = $this->jwtService->generateToken(['user_id' => $user1->id, 'email' => $user1->email]);
        $token2 = $this->jwtService->generateToken(['user_id' => $user2->id, 'email' => $user2->email]);

        $response1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->get('/api/school/students');

        $response2 = $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->get('/api/school/students');

        $this->assertNotEquals(403, $response1->getStatusCode());
        $this->assertNotEquals(403, $response2->getStatusCode());
    }

    public function test_role_middleware_denies_access_for_student_to_school_management()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Siswa');
        $user->assignRole('Siswa');

        $token = $this->jwtService->generateToken(['user_id' => $user->id, 'email' => $user->email]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/school/students');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Insufficient permissions', (string) $response->getBody());
    }

    public function test_role_middleware_denies_access_for_parent_to_attendance_management()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Orang Tua');
        $user->assignRole('Orang Tua');

        $token = $this->jwtService->generateToken(['user_id' => $user->id, 'email' => $user->email]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/attendance/staff-attendances');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_user_without_token_is_denied_by_role_middleware()
    {
        $response = $this->get('/api/school/students');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_user_with_invalid_token_is_denied_by_role_middleware()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token'])
            ->get('/api/school/students');

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_super_admin_can_access_all_protected_routes()
    {
        $user = $this->createTestUser();
        $this->createTestRole('Super Admin');
        $user->assignRole('Super Admin');

        $token = $this->jwtService->generateToken(['user_id' => $user->id, 'email' => $user->email]);

        $response1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/school/students');

        $response2 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/attendance/staff-attendances');

        $this->assertNotEquals(403, $response1->getStatusCode());
        $this->assertNotEquals(403, $response2->getStatusCode());
    }
}
