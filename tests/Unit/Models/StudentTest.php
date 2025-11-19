<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Foundation\Testing\RefreshDatabase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_model_fillable_attributes(): void
    {
        $student = new Student();
        
        $fillable = [
            'user_id',
            'nisn',
            'class_id',
            'birth_date',
            'birth_place',
            'address',
            'parent_id',
            'enrollment_date',
            'status',
        ];
        
        $this->assertEquals($fillable, $student->getFillable());
    }

    public function test_student_model_primary_key(): void
    {
        $student = new Student();
        
        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
    }

    public function test_student_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_has_class_relationship(): void
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);
        
        $this->assertInstanceOf(ClassModel::class, $student->class);
        $this->assertEquals($class->id, $student->class->id);
    }

    public function test_student_casts_attributes(): void
    {
        $student = new Student();
        
        $casts = [
            'birth_date' => 'date',
            'enrollment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        $this->assertEquals($casts, $student->getCasts());
    }
}