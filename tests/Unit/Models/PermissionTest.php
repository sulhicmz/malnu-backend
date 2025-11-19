<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_can_be_assigned_to_role(): void
    {
        $permission = Permission::factory()->create(['name' => 'edit-posts']);
        $role = Role::factory()->create(['name' => 'editor']);
        
        $role->givePermissionTo($permission);
        
        $this->assertTrue($role->hasPermissionTo('edit-posts'));
    }

    public function test_permission_has_roles(): void
    {
        $permission = Permission::factory()->create(['name' => 'delete-posts']);
        $role = Role::factory()->create(['name' => 'admin']);
        
        $role->givePermissionTo($permission);
        
        $this->assertCount(1, $permission->roles);
        $this->assertEquals($role->id, $permission->roles->first()->id);
    }
}