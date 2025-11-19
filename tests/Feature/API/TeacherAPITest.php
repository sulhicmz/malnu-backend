<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\SchoolManagement\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_teachers(): void
    {
        $user = User::factory()->create();
        Teacher::factory()->count(2)->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/teachers');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_create_teacher(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/teachers', [
            'user_id' => $user->id,
            'nip' => '198501012010011001',
            'birth_date' => '1985-01-01',
            'birth_place' => 'Jakarta',
            'gender' => 'male',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
            'nip' => '198501012010011001',
        ]);
    }
}