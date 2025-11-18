<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

class UserTest extends TestCase
{
    public function testUserModelCanBeCreated()
    {
        $user = User::factory()->make();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertIsString($user->id);
        $this->assertFalse($user->incrementing);
    }

    public function testUserModelAttributes()
    {
        $user = User::factory()->make();
        
        $fillable = [
            'name', 'username', 'email', 'password', 'full_name', 
            'phone', 'avatar_url', 'is_active', 'last_login_time', 
            'last_login_ip', 'remember_token', 'email_verified_at', 
            'slug', 'key_status'
        ];
        
        foreach ($fillable as $attribute) {
            $this->assertArrayHasKey($attribute, $user->getAttributes());
        }
    }

    public function testUserCanAssignRole()
    {
        $user = User::factory()->create();
        
        // Test that assignRole method exists
        $this->assertTrue(method_exists($user, 'assignRole'));
    }

    public function testUserCanSyncRoles()
    {
        $user = User::factory()->create();
        
        // Test that syncRoles method exists
        $this->assertTrue(method_exists($user, 'syncRoles'));
    }
}