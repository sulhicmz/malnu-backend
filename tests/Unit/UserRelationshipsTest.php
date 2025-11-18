<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @internal
 * @coversNothing
 */
class UserRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user parent relationship returns correct relation type.
     */
    public function testUserParentRelationship(): void
    {
        $user = new User();
        $relation = $user->parent();
        
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user teacher relationship returns correct relation type.
     */
    public function testUserTeacherRelationship(): void
    {
        $user = new User();
        $relation = $user->teacher();
        
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user student relationship returns correct relation type.
     */
    public function testUserStudentRelationship(): void
    {
        $user = new User();
        $relation = $user->student();
        
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test user staff relationship returns correct relation type.
     */
    public function testUserStaffRelationship(): void
    {
        $user = new User();
        $relation = $user->staff();
        
        $this->assertInstanceOf(HasOne::class, $relation);
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

    /**
     * Test actual user relationships with database records.
     */
    public function testUserCanHaveParentRelationship(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($parent->id, $user->parent->id);
    }

    /**
     * Test actual user relationships with teacher.
     */
    public function testUserCanHaveTeacherRelationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    /**
     * Test actual user relationships with student.
     */
    public function testUserCanHaveStudentRelationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    /**
     * Test actual user relationships with staff.
     */
    public function testUserCanHaveStaffRelationship(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($staff->id, $user->staff->id);
    }
}