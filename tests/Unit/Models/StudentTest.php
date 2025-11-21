<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_belongs_to_class(): void
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        $this->assertInstanceOf(ClassModel::class, $student->class);
        $this->assertEquals($class->id, $student->class->id);
    }

    public function test_student_has_many_grades(): void
    {
        $student = Student::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $student->grades);
    }

    public function test_student_has_many_exams(): void
    {
        $student = Student::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $student->exams);
    }
}