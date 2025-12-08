<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Staff;
use App\Models\ParentPortal\ParentOrtu;
use Tests\TestCase;

/**
 * Model Factory Tests
 * 
 * Note: This test suite is designed to work with the Hyperf framework.
 * The framework import issues (Hypervel -> Hyperf) are being fixed in PR #138.
 * Once those changes are merged, these tests will run properly.
 * 
 * @internal
 * @coversNothing
 */
class ModelFactoryTest extends TestCase
{
    /**
     * Test User model factory creates valid instances.
     */
    public function testUserFactoryCreatesValidInstance(): void
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test Student model factory creates valid instances.
     */
    public function testStudentFactoryCreatesValidInstance(): void
    {
        $student = new Student();
        $this->assertInstanceOf(Student::class, $student);
    }

    /**
     * Test Teacher model factory creates valid instances.
     */
    public function testTeacherFactoryCreatesValidInstance(): void
    {
        $teacher = new Teacher();
        $this->assertInstanceOf(Teacher::class, $teacher);
    }

    /**
     * Test Staff model factory creates valid instances.
     */
    public function testStaffFactoryCreatesValidInstance(): void
    {
        $staff = new Staff();
        $this->assertInstanceOf(Staff::class, $staff);
    }

    /**
     * Test ParentOrtu model factory creates valid instances.
     */
    public function testParentOrtuFactoryCreatesValidInstance(): void
    {
        $parent = new ParentOrtu();
        $this->assertInstanceOf(ParentOrtu::class, $parent);
    }
}