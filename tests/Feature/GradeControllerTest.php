<?php

namespace Tests\Feature;

use App\Models\Grading\Grade;
use Tests\TestCase;

class GradeControllerTest extends TestCase
{
    public function test_index_returns_grades()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/grades/');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_store_creates_grade()
    {
        $data = [
            'student_id' => '550e8400-e29b-41d4-a716-446655440000',
            'subject_id' => '550e8400-e29b-41d4-a716-446655440001',
            'class_id' => '550e8400-e29b-41d4-a716-446655440002',
            'grade' => 85.50,
            'semester' => 1,
            'grade_type' => 'assignment',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/grades/', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_requires_student_id()
    {
        $data = [
            'subject_id' => '550e8400-e29b-41d4-a716-446655440001',
            'class_id' => '550e8400-e29b-41d4-a716-446655440002',
            'grade' => 85.50,
            'semester' => 1,
            'grade_type' => 'assignment',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/grades/', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_subject_id()
    {
        $data = [
            'student_id' => '550e8400-e29b-41d4-a716-446655440000',
            'class_id' => '550e8400-e29b-41d4-a716-446655440002',
            'grade' => 85.50,
            'semester' => 1,
            'grade_type' => 'assignment',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/grades/', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_grade()
    {
        $data = [
            'student_id' => '550e8400-e29b-41d4-a716-446655440000',
            'subject_id' => '550e8400-e29b-41d4-a716-446655440001',
            'class_id' => '550e8400-e29b-41d4-a716-446655440002',
            'semester' => 1,
            'grade_type' => 'assignment',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/grades/', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_show_returns_grade()
    {
        $grade = Grade::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/grades/' . $grade->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_modifies_grade()
    {
        $grade = Grade::factory()->create();

        $data = [
            'grade' => 90.00,
            'semester' => 2,
            'grade_type' => 'exam',
            'notes' => 'Final grade',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->put('/api/grades/' . $grade->id, $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_destroy_removes_grade()
    {
        $grade = Grade::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->delete('/api/grades/' . $grade->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_student_id()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/grades/?student_id=550e8400-e29b-41d4-a716-446655440000');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_subject_id()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/grades/?subject_id=550e8400-e29b-41d4-a716-446655440001');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_semester()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/grades/?semester=1');

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getValidToken(): string
    {
        return 'test-token';
    }
}
