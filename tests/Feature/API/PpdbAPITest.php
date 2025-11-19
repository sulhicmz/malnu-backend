<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\PPDB\PpdbRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpdbAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_ppdb_registrations(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        PpdbRegistration::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/ppdb/registrations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'student_name', 'email', 'status']
                     ]
                 ]);
    }

    public function test_guest_can_register_for_ppdb(): void
    {
        $response = $this->postJson('/api/ppdb/register', [
            'student_name' => 'Test Student',
            'email' => 'student@example.com',
            'phone' => '081234567890',
            'parent_name' => 'Parent Name',
            'parent_phone' => '081234567891',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ppdb_registrations', [
            'email' => 'student@example.com',
            'student_name' => 'Test Student',
        ]);
    }
}