<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\ModelHasRole;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RBACMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seedRoles();
    }

    protected function seedRoles()
    {
        Role::firstOrCreate(['id' => '1', 'name' => 'Super Admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '2', 'name' => 'Kepala Sekolah', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '3', 'name' => 'Guru', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '4', 'name' => 'Siswa', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '5', 'name' => 'Orang Tua', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '6', 'name' => 'Staf TU', 'guard_name' => 'api']);
        Role::firstOrCreate(['id' => '7', 'name' => 'Konselor', 'guard_name' => 'api']);
    }

    public function test_user_can_be_assigned_single_role()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasRole('Super Admin'));
        $this->assertEquals(1, $user->roles()->count());
    }

    public function test_user_can_be_assigned_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->assignRole('Guru');

        $this->assertTrue($user->hasRole('Super Admin'));
        $this->assertTrue($user->hasRole('Guru'));
        $this->assertEquals(2, $user->roles()->count());
    }

    public function test_user_has_role_returns_false_for_non_assigned_role()
    {
        $user = User::factory()->create();
        $user->assignRole('Siswa');

        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('Guru'));
    }

    public function test_user_has_any_role_with_array_of_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Siswa');

        $this->assertTrue($user->hasAnyRole(['Super Admin', 'Siswa', 'Guru']));
        $this->assertFalse($user->hasAnyRole(['Super Admin', 'Guru']));
    }

    public function test_user_roles_relationship_returns_correct_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->assignRole('Guru');

        $roles = $user->roles;
        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('name', 'Super Admin'));
        $this->assertTrue($roles->contains('name', 'Guru'));
    }

    public function test_user_get_all_permissions_from_roles()
    {
        // Create permissions
        $permission1 = Permission::firstOrCreate(['id' => '1', 'name' => 'view students', 'guard_name' => 'api']);
        $permission2 = Permission::firstOrCreate(['id' => '2', 'name' => 'create students', 'guard_name' => 'api']);

        // Assign permissions to role
        $role = Role::where('name', 'Super Admin')->first();
        $role->permissions()->attach([$permission1->id, $permission2->id]);

        // Assign role to user
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $permissions = $user->getAllPermissions();
        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains('name', 'view students'));
        $this->assertTrue($permissions->contains('name', 'create students'));
    }

    public function test_user_has_permission_returns_true_for_assigned_permission()
    {
        $permission = Permission::firstOrCreate(['id' => '1', 'name' => 'view students', 'guard_name' => 'api']);
        $role = Role::where('name', 'Super Admin')->first();
        $role->permissions()->attach($permission->id);

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasPermission('view students'));
        $this->assertFalse($user->hasPermission('delete students'));
    }

    public function test_user_sync_roles_replaces_existing_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->assignRole('Guru');

        $user->syncRoles(['Siswa', 'Orang Tua']);

        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('Guru'));
        $this->assertTrue($user->hasRole('Siswa'));
        $this->assertTrue($user->hasRole('Orang Tua'));
        $this->assertEquals(2, $user->roles()->count());
    }

    public function test_super_admin_can_access_school_management_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_kepala_sekolah_can_access_school_management_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Kepala Sekolah');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_staff_tu_can_access_school_management_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Staf TU');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200);
    }

    public function test_siswa_cannot_access_school_management_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Siswa');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(403);
    }

    public function test_user_without_role_cannot_access_school_management_routes()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(403);
    }

    public function test_teacher_can_access_attendance_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Guru');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/staff-attendances');

        $response->assertStatus(200);
    }

    public function test_parent_cannot_access_attendance_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('Orang Tua');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/attendance/staff-attendances');

        $response->assertStatus(403);
    }

    public function test_all_authenticated_users_can_read_calendar()
    {
        $user = User::factory()->create();
        $user->assignRole('Siswa');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/calendar/calendars/1');

        $response->assertStatus(200);
    }

    public function test_student_cannot_write_to_calendar()
    {
        $user = User::factory()->create();
        $user->assignRole('Siswa');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-16',
        ]);

        $response->assertStatus(403);
    }

    public function test_teacher_can_write_to_calendar()
    {
        $user = User::factory()->create();
        $user->assignRole('Guru');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/calendar/events', [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => '2024-01-15',
            'end_date' => '2024-01-16',
        ]);

        $response->assertStatus(201);
    }
}
