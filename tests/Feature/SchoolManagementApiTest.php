<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @internal
 * @coversNothing
 */
class SchoolManagementApiTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
    }

    public function testStudentApiEndpoints()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Test GET /api/school/students
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data', // For paginated response
                    'per_page',
                    'current_page',
                    'last_page',
                    'total',
                ],
                'message',
                'timestamp',
            ]);

        // Test POST /api/school/students
        $studentData = [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => 'test-class',
            'enrollment_year' => '2024',
            'status' => 'active',
            'email' => 'test.student@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Student created successfully',
            ]);
    }

    public function testTeacherApiEndpoints()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Test GET /api/school/teachers
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/teachers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data', // For paginated response
                    'per_page',
                    'current_page',
                    'last_page',
                    'total',
                ],
                'message',
                'timestamp',
            ]);

        // Test POST /api/school/teachers
        $teacherData = [
            'name' => 'Test Teacher',
            'nip' => '198506152008011001',
            'subject_id' => 'test-subject',
            'join_date' => '2020-01-01',
            'email' => 'test.teacher@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/teachers', $teacherData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Teacher created successfully',
            ]);
    }

    public function testStudentShowEndpoint()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Create a test student
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567891',
            'class_id' => 'test-class',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/school/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                ],
            ]);
    }

    public function testTeacherShowEndpoint()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Create a test teacher
        $teacher = Teacher::create([
            'name' => 'Test Teacher',
            'nip' => '198506152008011002',
            'subject_id' => 'test-subject',
            'join_date' => '2020-01-01',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/school/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                ],
            ]);
    }
}
