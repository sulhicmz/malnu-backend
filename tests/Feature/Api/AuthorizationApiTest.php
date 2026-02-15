<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class AuthorizationApiTest extends TestCase
{
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->apiGetAsUser('/admin/dashboard', $admin);

        $response->assertStatus(200);
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->apiGetAsUser('/admin/dashboard', $teacher);

        $response->assertStatus(403);
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->apiGetAsUser('/admin/dashboard', $student);

        $response->assertStatus(403);
    }

    public function test_teacher_can_access_teacher_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->apiGetAsUser('/teacher/classes', $teacher);

        $response->assertStatus(200);
    }

    public function test_student_can_access_student_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->apiGetAsUser('/student/grades', $student);

        $response->assertStatus(200);
    }

    public function test_parent_can_access_parent_routes(): void
    {
        $parent = User::factory()->parent()->create();

        $response = $this->apiGetAsUser('/parent/children', $parent);

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_routes_of_other_roles(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->apiGetAsUser('/teacher/classes', $student);

        $response->assertStatus(403);
    }
}
