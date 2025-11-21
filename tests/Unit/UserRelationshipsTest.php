<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use App\Models\Role;
use App\Models\Permission;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UserRelationshipsTest extends TestCase
{
    /**
     * Test user parent relationship.
     */
    public function testUserParentRelationship(): void
    {
        $user = new User();
        $relation = $user->parent();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user teacher relationship.
     */
    public function testUserTeacherRelationship(): void
    {
        $user = new User();
        $relation = $user->teacher();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user student relationship.
     */
    public function testUserStudentRelationship(): void
    {
        $user = new User();
        $relation = $user->student();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user staff relationship.
     */
    public function testUserStaffRelationship(): void
    {
        $user = new User();
        $relation = $user->staff();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user role assignment functionality.
     */
    public function testUserCanAssignRole(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        
        $user->assignRole('admin');
        
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
    }

    /**
     * Test user role sync functionality.
     */
    public function testUserCanSyncRoles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'admin']);
        $role2 = Role::factory()->create(['name' => 'teacher']);
        
        $user->syncRoles(['admin', 'teacher']);
        
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
        $this->assertEquals(2, $user->roles()->count());
    }

    /**
     * Test ParentOrtu model exists and has correct relationships.
     */
    public function testParentOrtuModelExists(): void
    {
        $parent = new ParentOrtu();
        
        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
        
        // Test user relationship
        $userRelation = $parent->user();
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
        
        // Test students relationship
        $studentsRelation = $parent->students();
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasMany::class, $studentsRelation);
        $this->assertEquals('parent_id', $studentsRelation->getForeignKeyName());
    }
}