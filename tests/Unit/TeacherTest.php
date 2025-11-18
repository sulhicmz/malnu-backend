<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SchoolManagement\Teacher;
use Hypervel\Foundation\Testing\TestCase;

class TeacherTest extends TestCase
{
    public function testTeacherCanBeCreated()
    {
        $teacher = new Teacher();
        
        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
    }

    public function testTeacherHasUserRelationship()
    {
        $teacher = new Teacher();
        $relation = $teacher->user();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testTeacherHasClassesRelationship()
    {
        $teacher = new Teacher();
        $relation = $teacher->classes();
        
        $this->assertEquals('homeroom_teacher_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testTeacherHasClassSubjectsRelationship()
    {
        $teacher = new Teacher();
        $relation = $teacher->classSubjects();
        
        $this->assertEquals('teacher_id', $relation->getForeignKeyName());
    }

    public function testTeacherAttributes()
    {
        $teacher = new Teacher();
        
        $fillable = [
            'user_id', 'nip', 'expertise', 'join_date', 'status'
        ];
        
        $modelFillable = $teacher->getFillable();
        
        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $modelFillable);
        }
    }

    public function testTeacherCasts()
    {
        $teacher = new Teacher();
        $casts = $teacher->getCasts();
        
        $expectedCasts = [
            'join_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }
}