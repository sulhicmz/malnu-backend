<?php

declare(strict_types = 1);

use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Staff;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\User;
use Hyperf\Database\Seeders\Seeder;

class SchoolManagementSeeder extends Seeder
{
    /**
     * Seed the school management tables.
     */
    public function run(): void
    {
        $this->createSubjects();
        $this->createTeachers();
        $this->createParents();
        $this->createClasses();
        $this->createStudents();
        $this->createStaff();
        $this->assignClassSubjects();
    }

    private function createSubjects(): void
    {
        $subjects = [
            ['code' => 'MTH', 'name' => 'Mathematics', 'description' => 'Mathematics subject', 'credit_hours' => 4],
            ['code' => 'ENG', 'name' => 'English', 'description' => 'English language', 'credit_hours' => 3],
            ['code' => 'SCI', 'name' => 'Science', 'description' => 'General science', 'credit_hours' => 3],
            ['code' => 'SOC', 'name' => 'Social Studies', 'description' => 'Social studies', 'credit_hours' => 2],
            ['code' => 'IND', 'name' => 'Bahasa Indonesia', 'description' => 'Indonesian language', 'credit_hours' => 4],
            ['code' => 'REL', 'name' => 'Religion', 'description' => 'Religious education', 'credit_hours' => 2],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }

    private function createTeachers(): void
    {
        $teacherUser = User::where('username', 'guru')->first();
        if ($teacherUser) {
            Teacher::create([
                'user_id' => $teacherUser->id,
                'nip' => '198001012015001001',
                'expertise' => 'Mathematics',
                'join_date' => '2015-01-01',
                'status' => 'active',
            ]);
        }
    }

    private function createParents(): void
    {
        $parentUser = User::where('username', 'ortu')->first();
        if ($parentUser) {
            ParentOrtu::create([
                'user_id' => $parentUser->id,
                'occupation' => 'Engineer',
                'address' => 'Jl. Education No. 123, City',
            ]);
        }
    }

    private function createClasses(): void
    {
        $teacher = Teacher::first();
        
        $classes = [
            ['name' => 'X-A', 'level' => '10', 'academic_year' => '2024/2025', 'capacity' => 30],
            ['name' => 'X-B', 'level' => '10', 'academic_year' => '2024/2025', 'capacity' => 30],
            ['name' => 'XI-A', 'level' => '11', 'academic_year' => '2024/2025', 'capacity' => 28],
            ['name' => 'XII-A', 'level' => '12', 'academic_year' => '2024/2025', 'capacity' => 25],
        ];

        foreach ($classes as $classData) {
            $class = ClassModel::create($classData);
            if ($teacher) {
                $class->update(['homeroom_teacher_id' => $teacher->id]);
            }
        }
    }

    private function createStudents(): void
    {
        $studentUser = User::where('username', 'siswa')->first();
        $class = ClassModel::first();
        $parent = ParentOrtu::first();

        if ($studentUser && $class) {
            Student::create([
                'user_id' => $studentUser->id,
                'nisn' => '20240001',
                'class_id' => $class->id,
                'parent_id' => $parent?->id,
                'birth_date' => '2008-01-15',
                'birth_place' => 'City',
                'address' => 'Jl. Student No. 456, City',
                'enrollment_date' => '2024-07-01',
                'status' => 'active',
            ]);
        }
    }

    private function createStaff(): void
    {
        $staffUser = User::where('username', 'tu')->first();
        if ($staffUser) {
            Staff::create([
                'user_id' => $staffUser->id,
                'position' => 'Administrative Staff',
                'department' => 'Administration',
                'join_date' => '2020-01-01',
                'status' => 'active',
            ]);
        }
    }

    private function assignClassSubjects(): void
    {
        $classes = ClassModel::all();
        $subjects = Subject::all();
        $teacher = Teacher::first();

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                $class->subjects()->attach($subject->id, [
                    'teacher_id' => $teacher?->id,
                    'schedule_info' => 'Schedule to be determined',
                ]);
            }
        }
    }
}