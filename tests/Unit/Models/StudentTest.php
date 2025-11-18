<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Support\Facades\Hash;

class StudentTest extends TestCase
{
    /**
     * Test Student model can be created with required fields.
     */
    public function testStudentCanBeCreated(): void
    {
        $student = Student::create([
            'user_id' => null,
            'parent_id' => null,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => null,
        ]);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('Jane Student', $student->full_name);
        $this->assertEquals('12345', $student->nis);
    }

    /**
     * Test Student has correct primary key configuration.
     */
    public function testStudentPrimaryKeyConfiguration(): void
    {
        $student = new Student();
        
        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
    }

    /**
     * Test Student belongs to user relationship.
     */
    public function testStudentBelongsToUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'parent_id' => null,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => null,
        ]);

        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    /**
     * Test Student belongs to parent relationship.
     */
    public function testStudentBelongsToParent(): void
    {
        $parent = ParentOrtu::create([
            'user_id' => null,
            'full_name' => 'John Parent',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        $student = Student::create([
            'user_id' => null,
            'parent_id' => $parent->id,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => null,
        ]);

        $this->assertInstanceOf(ParentOrtu::class, $student->parent);
        $this->assertEquals($parent->id, $student->parent->id);
    }

    /**
     * Test Student belongs to class relationship.
     */
    public function testStudentBelongsToClass(): void
    {
        $class = ClassModel::create([
            'name' => 'Class A',
            'grade_level' => '10',
        ]);

        $student = Student::create([
            'user_id' => null,
            'parent_id' => null,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => $class->id,
        ]);

        $this->assertInstanceOf(ClassModel::class, $student->class);
        $this->assertEquals($class->id, $student->class->id);
    }

    /**
     * Test Student model fillable attributes.
     */
    public function testStudentFillableAttributes(): void
    {
        $student = new Student();
        $fillable = [
            'user_id',
            'parent_id',
            'full_name',
            'nis',
            'nisn',
            'class_id',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'religion',
            'address',
            'phone',
            'photo_url',
        ];
        
        $this->assertEquals($fillable, $student->getFillable());
    }
}