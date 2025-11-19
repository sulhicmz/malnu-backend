<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_students(): void
    {
        $user = User::factory()->create();
        Student::factory()->count(3)->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/students');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'user_id', 'class_id', 'nis', 'nisn']
                     ]
                 ]);
    }

    public function test_authenticated_user_can_create_student(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/students', [
            'user_id' => $user->id,
            'class_id' => 1,
            'nis' => '123456',
            'nisn' => '789012',
            'birth_date' => '2005-01-01',
            'birth_place' => 'Jakarta',
            'gender' => 'male',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'nis' => '123456',
        ]);
    }

    public function test_authenticated_user_can_update_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/students/{$student->id}", [
            'nis' => '654321',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nis' => '654321',
        ]);
    }

    public function test_authenticated_user_can_delete_student(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }
}