<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_users(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();
        
        $role->assignTo($user);
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertCount(1, $role->users);
        $this->assertEquals($user->id, $role->users->first()->id);
    }

    public function test_role_can_be_assigned_to_user(): void
    {
        $role = Role::factory()->create(['name' => 'teacher']);
        $user = User::factory()->create();
        
        $role->assignTo($user);
        
        $this->assertTrue($user->hasRole('teacher'));
    }
}