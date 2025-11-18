<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\ModelHasRole;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @internal
 * @coversNothing
 */
class RoleModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test role model can be created.
     */
    public function testRoleCanBeCreated(): void
    {
        $role = Role::factory()->create();

        $this->assertInstanceOf(Role::class, $role);
        $this->assertNotNull($role->id);
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    /**
     * Test role factory creates valid roles.
     */
    public function testRoleFactoryCreatesValidRoles(): void
    {
        $role = Role::factory()->make();

        $this->assertNotNull($role->name);
        $this->assertNotNull($role->guard_name);
    }

    /**
     * Test role has correct attributes.
     */
    public function testRoleHasCorrectAttributes(): void
    {
        $role = Role::factory()->create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $this->assertEquals('admin', $role->name);
        $this->assertEquals('web', $role->guard_name);
    }

    /**
     * Test role can be assigned to a user.
     */
    public function testRoleCanBeAssignedToUser(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);

        $role->assignTo($user);

        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
    }

    /**
     * Test role can have users.
     */
    public function testRoleCanHaveUsers(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'teacher']);
        
        $role->assignTo($user);

        $this->assertTrue($role->users()->where('id', $user->id)->exists());
    }

    /**
     * Test role has timestamps.
     */
    public function testRoleHasTimestamps(): void
    {
        $role = Role::factory()->create();

        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
        $this->assertInstanceOf(\DateTime::class, $role->created_at);
        $this->assertInstanceOf(\DateTime::class, $role->updated_at);
    }

    /**
     * Test role name is unique within guard.
     */
    public function testRoleNameIsUniqueWithinGuard(): void
    {
        $role1 = Role::factory()->create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // Creating another role with same name and guard should fail
        $this->expectException(\Exception::class);
        Role::factory()->create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
    }

    /**
     * Test role can be deleted with associated user assignments.
     */
    public function testRoleCanBeDeletedWithUserAssignments(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'moderator']);

        $role->assignTo($user);

        $roleId = $role->id;
        $role->delete();

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $user->id,
            'role_id' => $roleId
        ]);
    }

    /**
     * Test role has correct primary key configuration.
     */
    public function testRoleHasCorrectPrimaryKeyConfiguration(): void
    {
        $role = new Role();

        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('int', $role->getKeyType());
        $this->assertTrue($role->getIncrementing());
    }
}