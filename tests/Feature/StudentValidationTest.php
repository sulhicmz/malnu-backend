<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentValidationTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
    }

    public function test_student_store_validation_passes_with_valid_data()
    {
        $token = JWTAuth::fromUser($this->user);

        $studentData = [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john@example.com',
            'class_id' => 'test-class',
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student created successfully'
                 ]);
    }

    public function test_student_store_validation_fails_with_missing_required_fields()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);

        $data = $response->json();
        $this->assertArrayHasKey('nisn', $data['error']['details']);
        $this->assertArrayHasKey('class_id', $data['error']['details']);
        $this->assertArrayHasKey('enrollment_year', $data['error']['details']);
        $this->assertArrayHasKey('status', $data['error']['details']);
    }

    public function test_student_store_validation_fails_with_invalid_email()
    {
        $token = JWTAuth::fromUser($this->user);

        $studentData = [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'invalid-email',
            'class_id' => 'test-class',
            'enrollment_year' => 2024,
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(422);
        $data = $response->json();
        $this->assertArrayHasKey('email', $data['error']['details']);
    }

    public function test_student_store_validation_fails_with_invalid_status()
    {
        $token = JWTAuth::fromUser($this->user);

        $studentData = [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john@example.com',
            'class_id' => 'test-class',
            'enrollment_year' => 2024,
            'status' => 'invalid-status',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(422);
        $data = $response->json();
        $this->assertArrayHasKey('status', $data['error']['details']);
    }

    public function test_student_store_validation_fails_with_invalid_enrollment_year()
    {
        $token = JWTAuth::fromUser($this->user);

        $studentData = [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john@example.com',
            'class_id' => 'test-class',
            'enrollment_year' => 1800,
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(422);
        $data = $response->json();
        $this->assertArrayHasKey('enrollment_year', $data['error']['details']);
    }

    public function test_student_update_validation_fails_with_duplicate_nisn()
    {
        $token = JWTAuth::fromUser($this->user);

        $existingStudent = Student::create([
            'name' => 'Existing Student',
            'nisn' => '9876543210',
            'class_id' => 'test-class',
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $updateData = [
            'nisn' => '9876543210',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/school/students/{$existingStudent->id}", $updateData);

        $response->assertStatus(422);
    }

    public function test_teacher_store_validation_passes_with_valid_data()
    {
        $token = JWTAuth::fromUser($this->user);

        $teacherData = [
            'name' => 'Jane Smith',
            'nip' => '198506152008011001',
            'email' => 'jane@example.com',
            'subject_id' => 'test-subject',
            'join_date' => '2020-01-01',
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/teachers', $teacherData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Teacher created successfully'
                 ]);
    }

    public function test_teacher_store_validation_fails_with_missing_required_fields()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/teachers', [
            'name' => 'Jane Smith',
        ]);

        $response->assertStatus(422);

        $data = $response->json();
        $this->assertArrayHasKey('nip', $data['error']['details']);
        $this->assertArrayHasKey('subject_id', $data['error']['details']);
        $this->assertArrayHasKey('join_date', $data['error']['details']);
        $this->assertArrayHasKey('status', $data['error']['details']);
    }

    public function test_teacher_store_validation_fails_with_invalid_email()
    {
        $token = JWTAuth::fromUser($this->user);

        $teacherData = [
            'name' => 'Jane Smith',
            'nip' => '198506152008011001',
            'email' => 'invalid-email',
            'subject_id' => 'test-subject',
            'join_date' => '2020-01-01',
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/teachers', $teacherData);

        $response->assertStatus(422);

        $data = $response->json();
        $this->assertArrayHasKey('email', $data['error']['details']);
    }
}
