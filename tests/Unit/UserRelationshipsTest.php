<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Hyperf\Foundation\Testing\TestCase;

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
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
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
        $this->assertEquals('user_id', $userRelation->getForeignKeyName());
        
        // Test students relationship
        $studentsRelation = $parent->students();
        $this->assertEquals('parent_id', $studentsRelation->getForeignKeyName());
    }
}