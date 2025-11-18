<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Hypervel\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class RefreshDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUser()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function testZeroUserAfterRefresh()
    {
        $this->assertSame(0, User::count());
    }

    public function testUserCreationWithFactory()
    {
        // Create a user using the factory approach
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function testMultipleUsersCreation()
    {
        // Create multiple users
        $users = User::factory()->count(3)->create();

        $this->assertCount(3, $users);
        $this->assertEquals(3, User::count());
    }
}
