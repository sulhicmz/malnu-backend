<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use App\Models\ParentPortal\ParentOrtu;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @internal
 * @coversNothing
 */
class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test User factory creates valid instances.
     */
    public function testUserFactoryCreatesValidInstances(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertIsString($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    /**
     * Test Role factory creates valid instances.
     */
    public function testRoleFactoryCreatesValidInstances(): void
    {
        $role = Role::factory()->create();

        $this->assertNotNull($role->id);
        $this->assertNotNull($role->name);
        $this->assertEquals('web', $role->guard_name);
        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }

    /**
     * Test Teacher factory creates valid instances.
     */
    public function testTeacherFactoryCreatesValidInstances(): void
    {
        $teacher = Teacher::factory()->create();

        $this->assertNotNull($teacher->id);
        $this->assertIsString($teacher->id);
        $this->assertNotNull($teacher->nip);
        $this->assertNotNull($teacher->nuptk);
        $this->assertNotNull($teacher->phone);
        $this->assertNotNull($teacher->address);
        $this->assertNotNull($teacher->date_of_birth);
        $this->assertNotNull($teacher->gender);
        $this->assertNotNull($teacher->created_at);
        $this->assertNotNull($teacher->updated_at);
    }

    /**
     * Test Student factory creates valid instances.
     */
    public function testStudentFactoryCreatesValidInstances(): void
    {
        $student = Student::factory()->create();

        $this->assertNotNull($student->id);
        $this->assertIsString($student->id);
        $this->assertNotNull($student->nis);
        $this->assertNotNull($student->nisn);
        $this->assertNotNull($student->phone);
        $this->assertNotNull($student->address);
        $this->assertNotNull($student->date_of_birth);
        $this->assertNotNull($student->gender);
        $this->assertNotNull($student->created_at);
        $this->assertNotNull($student->updated_at);
    }

    /**
     * Test Staff factory creates valid instances.
     */
    public function testStaffFactoryCreatesValidInstances(): void
    {
        $staff = Staff::factory()->create();

        $this->assertNotNull($staff->id);
        $this->assertIsString($staff->id);
        $this->assertNotNull($staff->nip);
        $this->assertNotNull($staff->position);
        $this->assertNotNull($staff->phone);
        $this->assertNotNull($staff->address);
        $this->assertNotNull($staff->date_of_birth);
        $this->assertNotNull($staff->gender);
        $this->assertNotNull($staff->created_at);
        $this->assertNotNull($staff->updated_at);
    }

    /**
     * Test ParentOrtu factory creates valid instances.
     */
    public function testParentOrtuFactoryCreatesValidInstances(): void
    {
        $parent = ParentOrtu::factory()->create();

        $this->assertNotNull($parent->id);
        $this->assertIsString($parent->id);
        $this->assertNotNull($parent->full_name);
        $this->assertNotNull($parent->phone);
        $this->assertNotNull($parent->email);
        $this->assertNotNull($parent->address);
        $this->assertNotNull($parent->occupation);
        $this->assertNotNull($parent->relationship);
        $this->assertNotNull($parent->created_at);
        $this->assertNotNull($parent->updated_at);
    }

    /**
     * Test creating multiple users with factory.
     */
    public function testUserFactoryCanCreateMultipleInstances(): void
    {
        $users = User::factory(5)->create();

        $this->assertCount(5, $users);
        $this->assertSame(5, User::count());

        foreach ($users as $user) {
            $this->assertNotNull($user->id);
            $this->assertNotNull($user->name);
            $this->assertNotNull($user->email);
        }
    }

    /**
     * Test creating users with related models.
     */
    public function testUserFactoryCanCreateWithRelatedModels(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertEquals($user->id, $teacher->user_id);
    }

    /**
     * Test factory states work correctly.
     */
    public function testUserFactoryUnverifiedState(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
    }
}