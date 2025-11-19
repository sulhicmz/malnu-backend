<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\ModelHasRole;
use Hypervel\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_model_fillable_attributes(): void
    {
        $role = new Role();
        
        $fillable = [
            'name',
            'guard_name',
        ];
        
        $this->assertEquals($fillable, $role->getFillable());
    }

    public function test_role_model_primary_key(): void
    {
        $role = new Role();
        
        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->incrementing);
    }

    public function test_role_has_permissions_relationship(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        
        $role->permissions()->attach($permission);
        
        $this->assertTrue($role->permissions->contains($permission));
    }

    public function test_role_can_be_assigned_to_user(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        
        $role->assignTo($user);
        
        $this->assertTrue(ModelHasRole::where([
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ])->exists());
    }

    public function test_role_assign_to_throws_exception_for_invalid_user(): void
    {
        $role = Role::factory()->create();
        
        $this->expectException(\InvalidArgumentException::class);
        $role->assignTo('invalid_user');
    }
}