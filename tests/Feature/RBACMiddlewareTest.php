<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

class RBACMiddlewareTest extends TestCase
{
    protected $adminUser;
    protected $teacherUser;
    protected $studentUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'id' => 'role-admin',
            'name' => 'Super Admin',
            'guard_name' => 'api'
        ]);

        $teacherRole = Role::create([
            'id' => 'role-teacher',
            'name' => 'Guru',
            'guard_name' => 'api'
        ]);

        $studentRole = Role::create([
            'id' => 'role-student',
            'name' => 'Siswa',
            'guard_name' => 'api'
        ]);

        // Create users with different roles
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('Guru');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('Siswa');

        // Regular user without role
        $this->regularUser = User::factory()->create();
    }

    public function test_admin_can_access_school_management_routes()
    {
        $token = JWTAuth::fromUser($this->adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_school_management_routes()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'Insufficient permissions',
                         'code' => 'FORBIDDEN'
                     ]
                 ]);
    }

    public function test_teacher_can_access_attendance_routes()
    {
        $token = JWTAuth::fromUser($this->teacherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/staff-attendances');

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_attendance_routes()
    {
        $token = JWTAuth::fromUser($this->regularUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/staff-attendances');

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'Insufficient permissions',
                         'code' => 'FORBIDDEN'
                     ]
                 ]);
    }

    public function test_admin_can_create_calendar_event()
    {
        $token = JWTAuth::fromUser($this->adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => \Carbon\Carbon::now()->toIso8601String(),
            'end_date' => \Carbon\Carbon::now()->addHour()->toIso8601String(),
        ]);

        $response->assertStatus(201);
    }

    public function test_student_cannot_create_calendar_event()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => \Carbon\Carbon::now()->toIso8601String(),
            'end_date' => \Carbon\Carbon::now()->addHour()->toIso8601String(),
        ]);

        $response->assertStatus(403);
    }

    public function test_student_can_view_calendar_events()
    {
        $token = JWTAuth::fromUser($this->studentUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/calendar/events/test-event-id');

        // Read operations should return 200 or 404 if not found, not 403
        $this->assertTrue($response->status() === 200 || $response->status() === 404);
    }

    public function test_user_has_role_method()
    {
        $this->assertTrue($this->adminUser->hasRole('Super Admin'));
        $this->assertTrue($this->teacherUser->hasRole('Guru'));
        $this->assertTrue($this->studentUser->hasRole('Siswa'));
        $this->assertFalse($this->adminUser->hasRole('Siswa'));
        $this->assertFalse($this->regularUser->hasRole('Super Admin'));
    }

    public function test_user_has_any_role_method()
    {
        $this->assertTrue($this->adminUser->hasAnyRole(['Super Admin', 'Guru']));
        $this->assertTrue($this->teacherUser->hasAnyRole(['Super Admin', 'Guru']));
        $this->assertFalse($this->studentUser->hasAnyRole(['Super Admin', 'Guru']));
    }

    public function test_user_roles_relationship()
    {
        $adminRoles = $this->adminUser->roles;
        $this->assertCount(1, $adminRoles);
        $this->assertEquals('Super Admin', $adminRoles->first()->name);

        $regularRoles = $this->regularUser->roles;
        $this->assertCount(0, $regularRoles);
    }

    public function test_admin_can_approve_leave_request()
    {
        $token = JWTAuth::fromUser($this->adminUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/attendance/leave-requests/test-id/approve');

        $response->assertStatus(404); // Not found is expected, but should not be 403
    }

    public function test_teacher_cannot_approve_leave_request()
    {
        $token = JWTAuth::fromUser($this->teacherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/attendance/leave-requests/test-id/approve');

        $response->assertStatus(403);
    }
}
