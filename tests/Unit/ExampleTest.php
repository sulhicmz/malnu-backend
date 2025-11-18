<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Support\Facades\Hash;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{
    /**
     * Test user model can be created with required fields.
     */
    public function testUserCanBeCreated(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    /**
     * Test user can be assigned a role.
     */
    public function testUserCanAssignRole(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        // Create a role for testing
        $role = Role::create([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        // Test that the assignRole method works
        $user->assignRole('student');
        
        $this->assertTrue($user->roles()->where('name', 'student')->exists());
    }

    /**
     * Test user can sync roles.
     */
    public function testUserCanSyncRoles(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        // Create roles for testing
        Role::create(['name' => 'student', 'guard_name' => 'web']);
        Role::create(['name' => 'teacher', 'guard_name' => 'web']);

        // Assign initial role
        $user->assignRole('student');
        $this->assertTrue($user->roles()->where('name', 'student')->exists());

        // Sync roles (replace student with teacher)
        $user->syncRoles(['teacher']);
        
        $this->assertFalse($user->roles()->where('name', 'student')->exists());
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
    }

    /**
     * Test user model fillable attributes.
     */
    public function testUserFillableAttributes(): void
    {
        $fillable = [
            'name',
            'username',
            'email',
            'password',
            'full_name',
            'phone',
            'avatar_url',
            'is_active',
            'last_login_time',
            'last_login_ip',
            'remember_token',
            'email_verified_at',
            'slug',
            'key_status',
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    /**
     * Test user primary key configuration.
     */
    public function testUserPrimaryKeyConfiguration(): void
    {
        $user = new User();
        
        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->incrementing);
    }
}
