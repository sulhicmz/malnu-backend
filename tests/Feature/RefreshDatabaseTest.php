<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @internal
 * @coversNothing
 */
class RefreshDatabaseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
        $users = User::factory(3)->create();

        $this->assertCount(3, $users);
        $this->assertSame(3, User::count());
    }

    public function testDatabaseIsRefreshedBetweenTests()
    {
        User::factory()->create();
        $this->assertSame(1, User::count());

        // After this test, the database will be refreshed for the next test
        $this->assertDatabaseCount('users', 1);
    }

    public function testUserCanBeDeleted()
    {
        $user = User::factory()->create();
        $this->assertDatabaseCount('users', 1);

        $user->delete();
        $this->assertDatabaseCount('users', 0);
    }

    public function testUserCanBeUpdated()
    {
        $user = User::factory()->create();
        $originalName = $user->name;

        $updatedName = $this->faker->name();
        $user->update(['name' => $updatedName]);

        $this->assertNotEquals($originalName, $user->name);
        $this->assertEquals($updatedName, $user->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updatedName,
        ]);
    }
}
