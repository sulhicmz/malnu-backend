<?php

declare(strict_types=1);

namespace Tests\Integration\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_persisted_to_database(): void
    {
        $userData = [
            'name' => 'Integration Test User',
            'email' => 'integration@example.com',
            'password' => bcrypt('password'),
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $retrievedUser = User::find($user->id);
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($userData['name'], $retrievedUser->name);
    }

    public function test_user_can_be_updated_in_database(): void
    {
        $user = User::factory()->create();

        $updatedData = [
            'name' => 'Updated Integration User',
            'email' => 'updated-integration@example.com',
        ];

        $user->update($updatedData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updatedData['name'],
            'email' => $updatedData['email'],
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => $user->getOriginal('name'),
        ]);
    }

    public function test_user_can_be_deleted_from_database(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $retrievedUser = User::find($user->id);
        $this->assertNull($retrievedUser);
    }

    public function test_user_query_performance(): void
    {
        User::factory()->count(50)->create();

        $startTime = microtime(true);
        $users = User::all();
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert that query executes within reasonable time (under 100ms for 50 records)
        $this->assertLessThan(100, $executionTime);
        $this->assertCount(50, $users);
    }
}