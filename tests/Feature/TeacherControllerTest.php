<?php

namespace Tests\Feature;

use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
{
    public function test_index_returns_teachers()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_store_creates_teacher()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567890',
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_requires_name()
    {
        $subject = Subject::factory()->create();

        $data = [
            'nip' => '1234567890',
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_nip()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_subject_id()
    {
        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567890',
            'join_date' => '2024-01-15',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_join_date()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567890',
            'subject_id' => $subject->id,
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_requires_status()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567890',
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_validates_unique_nip()
    {
        $subject = Subject::factory()->create();
        $existingTeacher = Teacher::factory()->create();

        $data = [
            'name' => 'Another Teacher',
            'nip' => $existingTeacher->nip,
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_validates_status_enum()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567891',
            'subject_id' => $subject->id,
            'join_date' => '2024-01-15',
            'status' => 'invalid_status',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_validates_join_date_format()
    {
        $subject = Subject::factory()->create();

        $data = [
            'name' => 'Test Teacher',
            'nip' => '1234567892',
            'subject_id' => $subject->id,
            'join_date' => 'invalid-date',
            'status' => 'active',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->post('/api/school/teachers', $data);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_show_returns_teacher()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers/' . $teacher->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_update_modifies_teacher()
    {
        $teacher = Teacher::factory()->create();

        $data = [
            'name' => 'Updated Teacher Name',
            'status' => 'inactive',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->put('/api/school/teachers/' . $teacher->id, $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_destroy_removes_teacher()
    {
        $teacher = Teacher::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->delete('/api/school/teachers/' . $teacher->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_subject_id()
    {
        $subject = Subject::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers?subject_id=' . $subject->id);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_filter_by_status()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers?status=active');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_name()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers?search=Test');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_search_by_nip()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->getValidToken())
            ->get('/api/school/teachers?search=12345');

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getValidToken(): string
    {
        return 'test-token';
    }
}
