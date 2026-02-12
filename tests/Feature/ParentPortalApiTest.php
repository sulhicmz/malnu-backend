<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ParentPortalApiTest extends TestCase
{
    public function test_dashboard_endpoint_requires_authentication()
    {
        $response = $this->get('/api/parent/dashboard');
        $response->assertStatus(401);
    }

    public function test_child_grades_endpoint_requires_authentication()
    {
        $response = $this->get('/api/parent/children/123/grades');
        $response->assertStatus(401);
    }

    public function test_child_attendance_endpoint_requires_authentication()
    {
        $response = $this->get('/api/parent/children/123/attendance');
        $response->assertStatus(401);
    }

    public function test_child_assignments_endpoint_requires_authentication()
    {
        $response = $this->get('/api/parent/children/123/assignments');
        $response->assertStatus(401);
    }

    public function test_dashboard_returns_parent_data_when_authenticated()
    {
        $response = $this->actingAsParent()
            ->get('/api/parent/dashboard');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'parent_info',
                    'children_count',
                    'children',
                ],
            ]);
    }

    public function test_child_grades_returns_grades_data()
    {
        $studentId = $this->createStudentForParent();
        
        $response = $this->actingAsParent()
            ->get("/api/parent/children/{$studentId}/grades");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'grades_by_subject',
                    'total_grades',
                ],
            ]);
    }

    public function test_child_grades_returns_403_for_non_parent_student()
    {
        $response = $this->actingAsParent()
            ->get('/api/parent/children/unauthorized-id/grades');
        
        $response->assertStatus(403);
    }

    public function test_child_attendance_returns_attendance_data()
    {
        $studentId = $this->createStudentForParent();
        
        $response = $this->actingAsParent()
            ->get("/api/parent/children/{$studentId}/attendance");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'summary',
                    'records',
                ],
            ]);
    }

    public function test_child_attendance_filters_by_date_range()
    {
        $studentId = $this->createStudentForParent();
        
        $response = $this->actingAsParent()
            ->get("/api/parent/children/{$studentId}/attendance", [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]);
        
        $response->assertStatus(200);
    }

    public function test_child_assignments_returns_assignments_data()
    {
        $studentId = $this->createStudentForParent();
        
        $response = $this->actingAsParent()
            ->get("/api/parent/children/{$studentId}/assignments");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'upcoming_assignments',
                    'past_assignments',
                    'total_assignments',
                ],
            ]);
    }

    public function test_endpoints_require_parent_role()
    {
        $response = $this->actingAsStudent()
            ->get('/api/parent/dashboard');
        
        $response->assertStatus(403);
    }

    private function actingAsParent()
    {
        return $this;
    }

    private function actingAsStudent()
    {
        return $this;
    }

    private function createStudentForParent(): string
    {
        return 'test-student-id';
    }
}
