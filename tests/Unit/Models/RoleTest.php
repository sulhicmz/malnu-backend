<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_factory_creates_role(): void
    {
        $role = RoleFactory::new()->create();

        $this->assertNotNull($role->id);
        $this->assertNotNull($role->name);
        $this->assertEquals('web', $role->guard_name);
    }

    public function test_role_has_correct_primary_key(): void
    {
        $role = new Role();

        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->incrementing);
    }

    public function test_role_can_be_assigned_to_user(): void
    {
        $user = UserFactory::new()->create();
        $role = RoleFactory::new()->create(['name' => 'admin']);

        $role->assignTo($user);

        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'model_type' => User::class,
            'role_id' => $role->id,
        ]);
    }

    public function test_role_assign_to_throws_exception_for_invalid_user(): void
    {
        $role = RoleFactory::new()->create();
        
        $this->expectException(\InvalidArgumentException::class);
        $role->assignTo('not a user');
    }

    public function test_role_fillable_attributes(): void
    {
        $role = new Role();

        $fillable = $role->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('guard_name', $fillable);
    }
}