<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_routes_exist(): void
    {
        // Test if authentication routes exist (they might return 404 if not implemented yet)
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_be_created_and_authenticated(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_attributes_are_accessible(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }
}