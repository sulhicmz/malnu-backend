<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\SchoolManagement\Student;
use Carbon\Carbon;
use Exception;

class EnrollmentService
{
    public function updateEnrollmentStatus(string $studentId, string $status): Student
    {
        $student = Student::find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $validStatuses = ['active', 'inactive', 'graduated', 'transferred', 'suspended'];

        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid enrollment status');
        }

        $student->status = $status;
        $student->save();

        return $student;
    }

    public function assignToClass(string $studentId, string $classId): Student
    {
        $student = Student::find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $student->class_id = $classId;
        $student->save();

        return $student->load('class');
    }

    public function getEnrollmentHistory(string $studentId): array
    {
        $student = Student::with(['class'])->find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        return [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'nisn' => $student->nisn,
            'current_class' => $student->class->name ?? 'Not assigned',
            'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
            'current_status' => $student->status,
            'enrollment_years' => $this->calculateEnrollmentYears($student->enrollment_date),
        ];
    }

    public function getEnrollmentStatistics(): array
    {
        $totalStudents = Student::count();

        $activeStudents = Student::where('status', 'active')->count();

        $inactiveStudents = Student::where('status', 'inactive')->count();

        $graduatedStudents = Student::where('status', 'graduated')->count();

        $transferredStudents = Student::where('status', 'transferred')->count();

        $suspendedStudents = Student::where('status', 'suspended')->count();

        $newStudentsThisYear = Student::where(
            'enrollment_date',
            '>=',
            Carbon::now()->startOfYear()
        )->count();

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'inactive_students' => $inactiveStudents,
            'graduated_students' => $graduatedStudents,
            'transferred_students' => $transferredStudents,
            'suspended_students' => $suspendedStudents,
            'new_students_this_year' => $newStudentsThisYear,
            'enrollment_rate' => $totalStudents > 0
                ? round(($activeStudents / $totalStudents) * 100, 2)
                : 0
        ];
    }

    public function getClassEnrollment(string $classId): array
    {
        $students = Student::where('class_id', $classId)->get();

        return [
            'class_id' => $classId,
            'total_students' => $students->count(),
            'active_students' => $students->where('status', 'active')->count(),
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'status' => $student->status,
                    'enrollment_date' => $student->enrollment_date?->format('Y-m-d')
                ];
            })->toArray()
        ];
    }

    private function calculateEnrollmentYears(?Carbon $enrollmentDate): int
    {
        if (!$enrollmentDate) {
            return 0;
        }

        return $enrollmentDate->diffInYears(Carbon::now());
    }
}
