<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Database\Factories\UserFactory;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 * @coversNothing
 */
class UserRelationshipsTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user parent relationship.
     */
    public function testUserParentRelationship(): void
    {
        $user = UserFactory::new()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($user->id, $user->parent->user_id);
    }

    /**
     * Test user teacher relationship.
     */
    public function testUserTeacherRelationship(): void
    {
        $user = UserFactory::new()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($user->id, $user->teacher->user_id);
    }

    /**
     * Test user student relationship.
     */
    public function testUserStudentRelationship(): void
    {
        $user = UserFactory::new()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($user->id, $user->student->user_id);
    }

    /**
     * Test user staff relationship.
     */
    public function testUserStaffRelationship(): void
    {
        $user = UserFactory::new()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($user->id, $user->staff->user_id);
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
    
    /**
     * Test relationship methods return correct relation types.
     */
    public function testUserRelationshipMethodsReturnCorrectTypes(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(\Hypervel\Database\Eloquent\Relations\HasOne::class, $user->parent());
        $this->assertInstanceOf(\Hypervel\Database\Eloquent\Relations\HasOne::class, $user->teacher());
        $this->assertInstanceOf(\Hypervel\Database\Eloquent\Relations\HasOne::class, $user->student());
        $this->assertInstanceOf(\Hypervel\Database\Eloquent\Relations\HasOne::class, $user->staff());
    }
}