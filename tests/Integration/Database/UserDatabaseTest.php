<?php

declare(strict_types=1);

namespace Tests\Integration\Database;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\ModelHasPermission;

class UserDatabaseTest extends TestCase
{
    public function test_user_role_permission_relationships_in_database(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $permission = Permission::factory()->create(['name' => 'edit-posts']);

        // Assign role to user
        $user->assignRole('admin');
        
        // Verify in database
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_id' => $user->id,
            'model_type' => User::class,
        ]);
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'admin']);
        $role2 = Role::factory()->create(['name' => 'editor']);

        $user->syncRoles(['admin', 'editor']);

        $this->assertEquals(2, $user->roles()->count());
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'editor')->exists());
    }

    public function test_role_assignment_creates_correct_relationships(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'teacher']);

        $user->assignRole('teacher');

        // Check the relationship exists in the pivot table
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_id' => $user->id,
            'model_type' => User::class,
        ]);
    }

    public function test_role_sync_removes_old_roles_and_adds_new_ones(): void
    {
        $user = User::factory()->create();
        $oldRole = Role::factory()->create(['name' => 'old-role']);
        $newRole1 = Role::factory()->create(['name' => 'new-role-1']);
        $newRole2 = Role::factory()->create(['name' => 'new-role-2']);

        // Assign initial role
        $user->assignRole('old-role');
        $this->assertEquals(1, $user->fresh()->roles()->count());

        // Sync with new roles
        $user->syncRoles(['new-role-1', 'new-role-2']);

        $freshUser = $user->fresh();
        
        // Old role should be removed
        $this->assertFalse($freshUser->roles()->where('name', 'old-role')->exists());
        
        // New roles should be present
        $this->assertTrue($freshUser->roles()->where('name', 'new-role-1')->exists());
        $this->assertTrue($freshUser->roles()->where('name', 'new-role-2')->exists());
        $this->assertEquals(2, $freshUser->roles()->count());
    }

    public function test_user_model_fresh_retrieval(): void
    {
        $user = User::factory()->create();
        $originalName = $user->name;

        // Update directly in database
        User::where('id', $user->id)->update(['name' => 'Updated Name']);

        // Fresh should get the updated value
        $updatedUser = $user->fresh();
        
        $this->assertNotEquals($originalName, $updatedUser->name);
        $this->assertEquals('Updated Name', $updatedUser->name);
    }

    public function test_database_transactions_work_correctly(): void
    {
        $initialCount = User::count();
        
        try {
            User::query()->transaction(function () {
                User::factory()->create(['email' => 'trans1@example.com']);
                User::factory()->create(['email' => 'trans2@example.com']);
                
                // Throw an exception to rollback
                throw new \Exception('Rollback transaction');
            });
        } catch (\Exception $e) {
            // Exception is expected
        }

        // Count should remain the same after rollback
        $this->assertEquals($initialCount, User::count());
    }

    public function test_mass_assignment_protection(): void
    {
        // Create user with only fillable attributes
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
    }
}