<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\ELearning\LearningMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ELearningAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_learning_materials(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        LearningMaterial::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/elearning/materials');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_teacher_can_create_learning_material(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/elearning/materials', [
            'title' => 'Test Learning Material',
            'description' => 'This is a test learning material',
            'content' => '<p>Learning content here</p>',
            'subject_id' => 1,
            'grade_level' => '10',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('learning_materials', [
            'title' => 'Test Learning Material',
        ]);
    }
}