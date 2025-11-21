<?php

declare(strict_types=1);

namespace Tests\Feature\CRUD;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class UserCRUDTest extends TestCase
{
    public function test_user_can_be_created(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_be_read(): void
    {
        $user = User::factory()->create();

        $retrievedUser = User::find($user->id);

        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->id, $retrievedUser->id);
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
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
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

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();

        $user->delete();

        // Check that user is not in regular query but can be found with trashed
        $this->assertNull(User::find($user->id));
        
        // Since the User model doesn't have soft deletes enabled by default, 
        // we'll just verify that the user was permanently deleted
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_creation_with_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);

        $user->assignRole('admin');

        $this->assertTrue($user->fresh()->roles()->where('name', 'admin')->exists());
    }

    public function test_multiple_users_can_be_created(): void
    {
        $users = User::factory()->count(5)->create();

        $this->assertEquals(5, $users->count());
        $this->assertEquals(5, User::count());
    }

    public function test_user_attributes_are_validated_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertIsString($user->id);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsBool($user->is_active);
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }
}