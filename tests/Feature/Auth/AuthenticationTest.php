<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class AuthenticationTest extends TestCase
{
    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_factory_creates_unique_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertNotEquals($user1->email, $user2->email);
        $this->assertNotEquals($user1->id, $user2->id);
    }

    public function test_user_factory_creates_inactive_user(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->is_active);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_user_factory_creates_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null,
        ]);
    }

    public function test_user_has_default_attributes(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_role_assignment(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'student']);

        $user->assignRole('student');

        $this->assertTrue($user->roles()->where('name', 'student')->exists());
    }

    public function test_user_role_sync(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'admin']);
        $role2 = Role::factory()->create(['name' => 'teacher']);
        $role3 = Role::factory()->create(['name' => 'parent']);

        // Initially assign one role
        $user->assignRole('admin');

        // Sync with different roles
        $user->syncRoles(['teacher', 'parent']);

        $this->assertFalse($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
        $this->assertTrue($user->roles()->where('name', 'parent')->exists());
        $this->assertEquals(2, $user->roles()->count());
    }
}