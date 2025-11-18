<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
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

    public function testUserParentRelationship()
    {
        $user = new User();
        $relation = $user->parent();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserTeacherRelationship()
    {
        $user = new User();
        $relation = $user->teacher();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserStudentRelationship()
    {
        $user = new User();
        $relation = $user->student();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserStaffRelationship()
    {
        $user = new User();
        $relation = $user->staff();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
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

    public function testParentOrtuModelExistsAndProperties()
    {
        $parent = new ParentOrtu();
        
        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
    }
}