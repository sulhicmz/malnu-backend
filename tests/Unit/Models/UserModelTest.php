<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_can_create_user(): void
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->email);
    }

    public function test_user_has_required_attributes(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $parent = User::factory()->parent()->create();
        
        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('teacher', $teacher->role);
        $this->assertEquals('student', $student->role);
        $this->assertEquals('parent', $parent->role);
    }

    public function test_user_can_be_active_or_inactive(): void
    {
        $activeUser = User::factory()->create();
        $inactiveUser = User::factory()->inactive()->create();
        
        $this->assertTrue($activeUser->is_active);
        $this->assertFalse($inactiveUser->is_active);
    }

    public function test_user_email_verification(): void
    {
        $verifiedUser = User::factory()->create();
        $unverifiedUser = User::factory()->unverified()->create();
        
        $this->assertNotNull($verifiedUser->email_verified_at);
        $this->assertNull($unverifiedUser->email_verified_at);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plainpassword',
        ]);
        
        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(password_verify('plainpassword', $user->password));
    }

    public function test_user_can_check_role(): void
    {
        $admin = User::factory()->admin()->create();
        
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());
        $this->assertFalse($admin->isStudent());
    }
}
