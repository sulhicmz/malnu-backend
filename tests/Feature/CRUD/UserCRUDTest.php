<?php

declare(strict_types=1);

namespace Tests\Feature\CRUD;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
        
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_can_be_read(): void
    {
        $user = User::factory()->create();

        $retrievedUser = User::find($user->id);

        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->name, $retrievedUser->name);
        $this->assertEquals($user->email, $retrievedUser->email);
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create();

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $user->update($updatedData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updatedData['name'],
            'email' => $updatedData['email'],
        ]);
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}