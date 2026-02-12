<?php

namespace Tests\Feature;

use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    public function test_index_returns_students()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_store_creates_student()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_requires_name()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_nisn()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Test Student',
            'class_id' => $class->id,
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_class_id()
    {
        $data = [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_enrollment_year()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_status()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'enrollment_year' => 2024,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_validates_unique_nisn()
    {
        $class = ClassModel::factory()->create();
        $existingStudent = Student::factory()->create(['class_id' => $class->id]);

        $data = [
            'name' => 'Another Student',
            'nisn' => $existingStudent->nisn,
            'class_id' => $class->id,
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_validates_status_enum()
    {
        $class = ClassModel::factory()->create();

        $data = [
            'name' => 'Test Student',
            'nisn' => '1234567891',
            'class_id' => $class->id,
            'enrollment_year' => 2024,
            'status' => 'invalid_status',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/students', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_show_returns_student()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students/' . $student->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_modifies_student()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $data = [
            'name' => 'Updated Student Name',
            'status' => 'inactive',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->put('/api/school/students/' . $student->id, $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_destroy_removes_student()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->delete('/api/school/students/' . $student->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_class_id()
    {
        $class = ClassModel::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students?class_id=' . $class->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_status()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students?status=active');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_name()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students?search=Test');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_nisn()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/students?search=12345');

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getValidToken(): string
    {
        return 'test-token';
    }
}
