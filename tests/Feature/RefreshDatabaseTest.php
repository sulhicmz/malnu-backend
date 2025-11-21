<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RefreshDatabaseTest extends TestCase
{
    public function testCreateUser()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function testCreateMultipleUsers()
    {
        User::factory()->count(3)->create();

        $this->assertEquals(3, User::count());
    }

    public function testDatabaseRefreshesBetweenTests()
    {
        // This test verifies that the RefreshDatabase trait is working
        // by ensuring the database is clean at the start of each test
        $this->assertSame(0, User::count());
    }

    public function testUserCanBeDeleted()
    {
        $user = User::factory()->create();
        
        $user->delete();
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
