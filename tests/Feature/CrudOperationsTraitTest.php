<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class CrudOperationsTraitTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testIndexReturnsPaginatedResults()
    {
        $token = JWTAuth::fromUser($this->user);

        Student::create([
            'name' => 'Student 1',
            'nisn' => '1111111111',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        Student::create([
            'name' => 'Student 2',
            'nisn' => '2222222222',
            'class_id' => 'class-2',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'data',
                         'per_page',
                         'current_page',
                         'last_page',
                         'total',
                     ],
                     'message',
                     'timestamp',
                 ]);
    }

    public function testIndexWithFilters()
    {
        $token = JWTAuth::fromUser($this->user);

        Student::create([
            'name' => 'Active Student',
            'nisn' => '1111111111',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        Student::create([
            'name' => 'Inactive Student',
            'nisn' => '2222222222',
            'class_id' => 'class-2',
            'enrollment_year' => '2024',
            'status' => 'inactive',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students?status=active');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('active', $data[0]['status']);
    }

    public function testIndexWithSearch()
    {
        $token = JWTAuth::fromUser($this->user);

        Student::create([
            'name' => 'John Doe',
            'nisn' => '1111111111',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        Student::create([
            'name' => 'Jane Smith',
            'nisn' => '2222222222',
            'class_id' => 'class-2',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students?search=John');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('John Doe', $data[0]['name']);
    }

    public function testStoreValidatesRequiredFields()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', []);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'message',
                         'code',
                         'details',
                     ],
                 ]);
    }

    public function testStoreValidatesEmailFormat()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', [
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);

        $details = $response->json('error.details');
        $this->assertArrayHasKey('email', $details);
    }

    public function testStoreValidatesUniqueFields()
    {
        $token = JWTAuth::fromUser($this->user);

        Student::create([
            'name' => 'Existing Student',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
            'email' => 'existing@example.com',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', [
            'name' => 'New Student',
            'nisn' => '1234567890',
            'class_id' => 'class-2',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'The nisn has already been taken.',
                     ],
                 ]);
    }

    public function testStoreCreatesRecord()
    {
        $token = JWTAuth::fromUser($this->user);

        $studentData = [
            'name' => 'New Student',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
            'email' => 'new@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/students', $studentData);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student created successfully',
                 ]);

        $this->assertDatabaseHas('students', [
            'name' => 'New Student',
            'nisn' => '1234567890',
        ]);
    }

    public function testShowReturnsRecord()
    {
        $token = JWTAuth::fromUser($this->user);

        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/school/students/{$student->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student retrieved successfully',
                     'data' => [
                         'id' => $student->id,
                         'name' => 'Test Student',
                     ],
                 ]);
    }

    public function testShowReturnsNotFoundForInvalidId()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/students/999999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'Student not found',
                     ],
                 ]);
    }

    public function testUpdateModifiesRecord()
    {
        $token = JWTAuth::fromUser($this->user);

        $student = Student::create([
            'name' => 'Original Name',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/school/students/{$student->id}", [
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student updated successfully',
                     'data' => [
                         'name' => 'Updated Name',
                         'status' => 'inactive',
                     ],
                 ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);
    }

    public function testUpdateValidatesUniqueFields()
    {
        $token = JWTAuth::fromUser($this->user);

        $student1 = Student::create([
            'name' => 'Student 1',
            'nisn' => '1111111111',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'name' => 'Student 2',
            'nisn' => '2222222222',
            'class_id' => 'class-2',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/school/students/{$student2->id}", [
            'nisn' => '1111111111',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'The nisn has already been taken.',
                     ],
                 ]);
    }

    public function testDestroyDeletesRecord()
    {
        $token = JWTAuth::fromUser($this->user);

        $student = Student::create([
            'name' => 'To Be Deleted',
            'nisn' => '1234567890',
            'class_id' => 'class-1',
            'enrollment_year' => '2024',
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/school/students/{$student->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student deleted successfully',
                 ]);

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    public function testDestroyReturnsNotFoundForInvalidId()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/school/students/999999');

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'message' => 'Student not found',
                     ],
                 ]);
    }
}
