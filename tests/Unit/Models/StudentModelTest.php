<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\User;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    public function test_can_create_student(): void
    {
        $student = $this->createStudentWithUser();
        
        $this->assertInstanceOf(Student::class, $student);
        $this->assertNotNull($student->id);
        $this->assertNotNull($student->student_id);
    }

    public function test_student_belongs_to_user(): void
    {
        $user = User::factory()->student()->create();
        $student = Student::factory()->withUser($user)->create();
        
        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_belongs_to_class(): void
    {
        $class = ClassModel::factory()->create();
        $student = $this->createStudentWithUser([], ['class_id' => $class->id]);
        
        $this->assertInstanceOf(ClassModel::class, $student->class);
        $this->assertEquals($class->id, $student->class->id);
    }

    public function test_student_has_required_attributes(): void
    {
        $student = $this->createStudentWithUser([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ], [
            'student_id' => 'STU1234',
            'date_of_birth' => '2005-06-15',
            'gender' => 'male',
        ]);
        
        $this->assertEquals('STU1234', $student->student_id);
        $this->assertEquals('male', $student->gender);
    }

    public function test_student_can_have_guardian_information(): void
    {
        $student = $this->createStudentWithUser([], [
            'guardian_name' => 'Jane Doe',
            'guardian_phone' => '+1234567890',
            'guardian_email' => 'guardian@example.com',
        ]);
        
        $this->assertEquals('Jane Doe', $student->guardian_name);
        $this->assertEquals('+1234567890', $student->guardian_phone);
    }

    public function test_student_can_have_emergency_contact(): void
    {
        $student = $this->createStudentWithUser([], [
            'emergency_contact' => '+9876543210',
        ]);
        
        $this->assertEquals('+9876543210', $student->emergency_contact);
    }

    public function test_student_can_have_blood_group(): void
    {
        $student = $this->createStudentWithUser([], [
            'blood_group' => 'O+',
        ]);
        
        $this->assertEquals('O+', $student->blood_group);
    }
}
