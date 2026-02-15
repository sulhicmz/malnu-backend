<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\User;

trait TestDataSetupTrait
{
    protected function createStudentWithUser(array $userAttributes = [], array $studentAttributes = []): Student
    {
        $user = User::factory()->student()->create($userAttributes);
        return Student::factory()->withUser($user)->create($studentAttributes);
    }

    protected function createTeacherWithUser(array $userAttributes = [], array $teacherAttributes = []): Teacher
    {
        $user = User::factory()->teacher()->create($userAttributes);
        return Teacher::factory()->withUser($user)->create($teacherAttributes);
    }

    protected function createClassWithStudents(int $studentCount = 5): array
    {
        $class = ClassModel::factory()->create();
        $students = [];
        
        for ($i = 0; $i < $studentCount; $i++) {
            $students[] = $this->createStudentWithUser([], ['class_id' => $class->id]);
        }
        
        return ['class' => $class, 'students' => $students];
    }

    protected function createSubjectWithTeachers(int $teacherCount = 2): array
    {
        $subject = Subject::factory()->create();
        $teachers = [];
        
        for ($i = 0; $i < $teacherCount; $i++) {
            $teachers[] = $this->createTeacherWithUser();
        }
        
        return ['subject' => $subject, 'teachers' => $teachers];
    }

    protected function createCompleteAcademicSetup(): array
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = $this->createTeacherWithUser();
        $students = [];
        
        for ($i = 0; $i < 10; $i++) {
            $students[] = $this->createStudentWithUser([], ['class_id' => $class->id]);
        }
        
        return [
            'class' => $class,
            'subject' => $subject,
            'teacher' => $teacher,
            'students' => $students,
        ];
    }
}
