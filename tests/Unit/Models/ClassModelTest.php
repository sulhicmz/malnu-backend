<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Teacher;

class ClassModelTest extends TestCase
{
    /**
     * Test ClassModel can be created with required fields.
     */
    public function testClassModelCanBeCreated(): void
    {
        $class = ClassModel::create([
            'name' => 'Class A',
            'grade_level' => '10',
        ]);

        $this->assertInstanceOf(ClassModel::class, $class);
        $this->assertEquals('Class A', $class->name);
        $this->assertEquals('10', $class->grade_level);
    }

    /**
     * Test ClassModel has correct primary key configuration.
     */
    public function testClassModelPrimaryKeyConfiguration(): void
    {
        $class = new ClassModel();
        
        $this->assertEquals('id', $class->getKeyName());
        $this->assertEquals('string', $class->getKeyType());
        $this->assertFalse($class->incrementing);
    }

    /**
     * Test ClassModel belongs to homeroom teacher relationship.
     */
    public function testClassModelBelongsToHomeroomTeacher(): void
    {
        $teacher = Teacher::create([
            'user_id' => null,
            'full_name' => 'Mr. Smith',
            'nip' => '98765',
            'subject_id' => null,
        ]);

        $class = ClassModel::create([
            'name' => 'Class A',
            'grade_level' => '10',
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $this->assertInstanceOf(Teacher::class, $class->homeroomTeacher);
        $this->assertEquals($teacher->id, $class->homeroomTeacher->id);
    }

    /**
     * Test ClassModel model fillable attributes.
     */
    public function testClassModelFillableAttributes(): void
    {
        $class = new ClassModel();
        $fillable = [
            'name',
            'grade_level',
            'homeroom_teacher_id',
            'description',
            'capacity',
        ];
        
        $this->assertEquals($fillable, $class->getFillable());
    }
}