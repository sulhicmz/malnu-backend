<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

class StudentTest extends TestCase
{
    public function testStudentCanBeCreated()
    {
        // Since there's no factory, we'll test the model properties directly
        $student = new Student();
        
        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
    }

    public function testStudentHasUserRelationship()
    {
        $student = new Student();
        $relation = $student->user();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testStudentHasClassRelationship()
    {
        $student = new Student();
        $relation = $student->class();
        
        $this->assertEquals('class_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testStudentHasParentRelationship()
    {
        $student = new Student();
        $relation = $student->parent();
        
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testStudentHasGradesRelationship()
    {
        $student = new Student();
        $relation = $student->grades();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
    }

    public function testStudentAttributes()
    {
        $student = new Student();
        
        $fillable = [
            'user_id', 'nisn', 'class_id', 'birth_date', 
            'birth_place', 'address', 'parent_id', 'enrollment_date', 
            'status'
        ];
        
        $modelFillable = $student->getFillable();
        
        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $modelFillable);
        }
    }

    public function testStudentCasts()
    {
        $student = new Student();
        $casts = $student->getCasts();
        
        $expectedCasts = [
            'birth_date' => 'date',
            'enrollment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }
}