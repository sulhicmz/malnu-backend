<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
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
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    /**
     * Test user teacher relationship.
     */
    public function testUserTeacherRelationship(): void
    {
        $user = new User();
        $relation = $user->teacher();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    /**
     * Test user student relationship.
     */
    public function testUserStudentRelationship(): void
    {
        $user = new User();
        $relation = $user->student();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    /**
     * Test user staff relationship.
     */
    public function testUserStaffRelationship(): void
    {
        $user = new User();
        $relation = $user->staff();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
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
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
        
        // Test students relationship
        $studentsRelation = $parent->students();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $studentsRelation);
        $this->assertEquals('parent_id', $studentsRelation->getForeignKeyName());
    }
    
    /**
     * Test Teacher model relationships.
     */
    public function testTeacherModelRelationships(): void
    {
        $teacher = new Teacher();
        
        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
        
        // Test user relationship
        $userRelation = $teacher->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
    }
    
    /**
     * Test Student model relationships.
     */
    public function testStudentModelRelationships(): void
    {
        $student = new Student();
        
        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
        
        // Test user relationship
        $userRelation = $student->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
    }
    
    /**
     * Test Staff model relationships.
     */
    public function testStaffModelRelationships(): void
    {
        $staff = new Staff();
        
        $this->assertEquals('id', $staff->getKeyName());
        $this->assertEquals('string', $staff->getKeyType());
        $this->assertFalse($staff->incrementing);
        
        // Test user relationship
        $userRelation = $staff->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
    }
}