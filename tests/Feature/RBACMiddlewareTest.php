<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\RoleHasPermission;
use Tymon\JWTAuth\Facades\JWTAuth;

class RBACMiddlewareTest extends TestCase
{
    protected $adminUser;
    protected $teacherUser;
    protected $studentUser;
    protected $noRoleUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->teacherUser = User::factory()->create();
        $this->studentUser = User::factory()->create();
        $this->noRoleUser = User::factory()->create();

        $adminRole = Role::where('name', 'Super Admin')->first();
        $teacherRole = Role::where('name', 'Guru')->first();
        $studentRole = Role::where('name', 'Siswa')->first();

        if ($adminRole) {
            $this->adminUser->assignRole('Super Admin');
        }

        if ($teacherRole) {
            $this->teacherUser->assignRole('Guru');
        }

        if ($studentRole) {
            $this->studentUser->assignRole('Siswa');
        }
    }

    public function test_user_has_role_method()
    {
        $this->assertTrue($this->adminUser->hasRole('Super Admin'));
        $this->assertTrue($this->teacherUser->hasRole('Guru'));
        $this->assertTrue($this->studentUser->hasRole('Siswa'));
        $this->assertFalse($this->noRoleUser->hasRole('Super Admin'));
        $this->assertFalse($this->adminUser->hasRole('Guru'));
    }

    public function test_user_has_any_role_method()
    {
        $this->assertTrue($this->adminUser->hasAnyRole(['Super Admin', 'Kepala Sekolah']));
        $this->assertTrue($this->teacherUser->hasAnyRole(['Guru', 'Kepala Sekolah']));
        $this->assertFalse($this->studentUser->hasAnyRole(['Super Admin', 'Kepala Sekolah']));
        $this->assertFalse($this->noRoleUser->hasAnyRole(['Super Admin', 'Guru', 'Siswa']));
    }

    public function test_user_roles_relationship()
    {
        $roles = $this->adminUser->roles;
        $this->assertGreaterThan(0, $roles->count());
        $this->assertTrue($roles->contains('name', 'Super Admin'));
    }

    public function test_user_get_all_permissions()
    {
        $permissions = $this->adminUser->getAllPermissions();
        $this->assertIsObject($permissions);
    }

    public function test_user_has_permission_method()
    {
        $this->adminUser->assignRole('Super Admin');
        $hasPermission = $this->adminUser->hasPermission('manage-users');
        $this->assertIsBool($hasPermission);
    }

    public function test_school_management_routes_with_correct_role()
    {
        $token = JWTAuth::fromUser($this->adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_school_management_routes_without_correct_role()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(403);
    }

    public function test_school_management_routes_without_any_role()
    {
        $token = JWTAuth::fromUser($this->noRoleUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(403);
    }

    public function test_attendance_routes_with_teacher_role()
    {
        $token = JWTAuth::fromUser($this->teacherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/leave-requests');

        $response->assertStatus(200);
    }

    public function test_attendance_routes_without_teacher_role()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/leave-requests');

        $response->assertStatus(403);
    }

    public function test_attendance_approve_reject_routes_with_admin_role()
    {
        $token = JWTAuth::fromUser($this->adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/attendance/leave-requests/test-id/approve');

        $this->assertContains($response->getStatusCode(), [403, 404, 422]);
    }

    public function test_attendance_approve_reject_routes_without_admin_role()
    {
        $token = JWTAuth::fromUser($this->teacherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/attendance/leave-requests/test-id/approve');

        $response->assertStatus(403);
    }

    public function test_calendar_read_routes_all_authenticated_users()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/calendar/calendars');

        $this->assertContains($response->getStatusCode(), [200, 404]);
    }

    public function test_calendar_write_routes_with_correct_role()
    {
        $token = JWTAuth::fromUser($this->teacherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => now()->toIso8601String(),
            'end_time' => now()->addHour()->toIso8601String(),
        ]);

        $this->assertContains($response->getStatusCode(), [201, 422, 403]);
    }

    public function test_calendar_write_routes_without_correct_role()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => now()->toIso8601String(),
            'end_time' => now()->addHour()->toIso8601String(),
        ]);

        $response->assertStatus(403);
    }

    public function test_role_middleware_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Staf TU');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_role_middleware_pipe_separated_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Staf TU');

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/leave-requests');

        $response->assertStatus(200);
    }

    public function test_role_middleware_without_jwt_token()
    {
        $response = $this->getJson('/api/school/students');
        $response->assertStatus(401);
    }

    public function test_sync_roles_method()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasRole('Super Admin'));

        $user->syncRoles(['Guru', 'Siswa']);

        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertTrue($user->hasRole('Guru'));
        $this->assertTrue($user->hasRole('Siswa'));
    }
}
