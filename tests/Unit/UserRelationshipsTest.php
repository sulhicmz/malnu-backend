<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_parent_relationship(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($parent->id, $user->parent->id);
    }

    public function test_user_has_teacher_relationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    public function test_user_has_student_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    public function test_user_has_staff_relationship(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($staff->id, $user->staff->id);
    }

    public function test_user_can_be_assigned_role(): void
    {
        $user = User::factory()->create();
        
        $user->assignRole('admin');
        
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_can_sync_roles(): void
    {
        $user = User::factory()->create();
        
        $user->syncRoles(['admin', 'teacher']);
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('teacher'));
    }
}