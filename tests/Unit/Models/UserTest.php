<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_factory_creates_user(): void
    {
        $user = UserFactory::new()->create();

        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertTrue($user->is_active);
    }

    public function test_user_has_correct_primary_key(): void
    {
        $user = new User();

        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->incrementing);
    }

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = UserFactory::new()->create();
        $role = RoleFactory::new()->create(['name' => 'admin']);

        $user->assignRole('admin');

        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'model_type' => User::class,
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_sync_roles(): void
    {
        $user = UserFactory::new()->create();
        $role1 = RoleFactory::new()->create(['name' => 'admin']);
        $role2 = RoleFactory::new()->create(['name' => 'teacher']);

        $user->syncRoles(['admin']);
        $this->assertCount(1, $user->roles);

        $user->syncRoles(['admin', 'teacher']);
        $this->assertCount(2, $user->roles);

        $user->syncRoles(['teacher']);
        $this->assertCount(1, $user->roles);
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User();

        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('full_name', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('is_active', $fillable);
    }
}