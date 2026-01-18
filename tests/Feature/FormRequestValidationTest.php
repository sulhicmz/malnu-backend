<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_store_student_validation()
    {
        $response = $this->client->post('/api/students', [
            'name' => '',
            'nisn' => '',
            'class_id' => '',
            'enrollment_year' => '',
            'status' => ''
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_student_requires_name()
    {
        $response = $this->client->post('/api/students', [
            'nisn' => '1234567890',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_student_requires_nisn()
    {
        $response = $this->client->post('/api/students', [
            'name' => 'John Doe',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_student_requires_class_id()
    {
        $response = $this->client->post('/api/students', [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'enrollment_year' => 2024,
            'status' => 'active'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_student_requires_status()
    {
        $response = $this->client->post('/api/students', [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'class_id' => 1,
            'enrollment_year' => 2024
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_teacher_validation()
    {
        $response = $this->client->post('/api/teachers', [
            'name' => '',
            'nip' => '',
            'subject_id' => '',
            'join_date' => ''
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_teacher_requires_name()
    {
        $response = $this->client->post('/api/teachers', [
            'nip' => '1234567890',
            'subject_id' => 1,
            'join_date' => '2024-01-01'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_teacher_requires_nip()
    {
        $response = $this->client->post('/api/teachers', [
            'name' => 'John Doe',
            'subject_id' => 1,
            'join_date' => '2024-01-01'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_teacher_requires_subject_id()
    {
        $response = $this->client->post('/api/teachers', [
            'name' => 'John Doe',
            'nip' => '1234567890',
            'join_date' => '2024-01-01'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_teacher_requires_join_date()
    {
        $response = $this->client->post('/api/teachers', [
            'name' => 'John Doe',
            'nip' => '1234567890',
            'subject_id' => 1
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_approve_leave_request_validation()
    {
        $response = $this->client->post('/api/leave-requests/1/approve', [
            'approval_comments' => 123
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_approve_leave_request_allows_null_comments()
    {
        $response = $this->client->post('/api/leave-requests/1/approve', []);

        $this->assertNotEquals(422, $response->getStatusCode());
    }

    public function test_reject_leave_request_requires_comments()
    {
        $response = $this->client->post('/api/leave-requests/1/reject', []);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_reject_leave_request_rejects_non_string_comments()
    {
        $response = $this->client->post('/api/leave-requests/1/reject', [
            'approval_comments' => 123
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_leave_request_validation()
    {
        $response = $this->client->post('/api/leave-requests', [
            'staff_id' => '',
            'leave_type_id' => '',
            'start_date' => '',
            'end_date' => '',
            'reason' => ''
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_leave_request_requires_staff_id()
    {
        $response = $this->client->post('/api/leave-requests', [
            'leave_type_id' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-02',
            'reason' => 'Personal reason'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_store_leave_request_requires_reason()
    {
        $response = $this->client->post('/api/leave-requests', [
            'staff_id' => 1,
            'leave_type_id' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-02'
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_update_student_validation()
    {
        $response = $this->client->put('/api/students/1', [
            'name' => str_repeat('a', 300),
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_update_teacher_validation()
    {
        $response = $this->client->put('/api/teachers/1', [
            'name' => str_repeat('a', 300),
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_update_leave_request_validation()
    {
        $response = $this->client->put('/api/leave-requests/1', [
            'comments' => 123
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}
