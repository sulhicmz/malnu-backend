<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance\StudentAttendance;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hyperf\Database\Model\ModelNotFoundException;

class ParentPortalService
{
    /**
     * Get authenticated parent's dashboard with overview of all children.
     */
    public function getDashboard(int $parentId): array
    {
        $parent = ParentOrtu::where('user_id', $parentId)->first();

        if (!$parent) {
            throw new \Exception('Parent record not found');
        }

        $students = $parent->students()->with(['class', 'user'])->get();

        $childrenOverview = [];
        foreach ($students as $student) {
            $latestGrade = Grade::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $attendanceSummary = $this->getAttendanceSummary($student->id);

            $childrenOverview[] = [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'nisn' => $student->nisn,
                'class_name' => $student->class->name ?? 'Not assigned',
                'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
                'latest_grade' => $latestGrade ? $latestGrade->grade : null,
                'attendance_summary' => $attendanceSummary,
            ];
        }

        return [
            'parent_info' => [
                'id' => $parent->id,
                'occupation' => $parent->occupation,
                'address' => $parent->address,
            ],
            'children_count' => count($students),
            'children' => $childrenOverview,
        ];
    }

    /**
     * Get detailed grade report for a specific child.
     */
    public function getChildGrades(int $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        $grades = Grade::where('student_id', $studentId)
            ->with(['subject', 'class', 'assignment', 'exam'])
            ->orderBy('created_at', 'desc')
            ->get();

        $gradesBySubject = [];
        foreach ($grades as $grade) {
            $subjectName = $grade->subject->name ?? 'Unknown';

            if (!isset($gradesBySubject[$subjectName])) {
                $gradesBySubject[$subjectName] = [
                    'subject_name' => $subjectName,
                    'grades' => [],
                ];
            }

            $gradesBySubject[$subjectName]['grades'][] = [
                'id' => $grade->id,
                'grade' => $grade->grade,
                'semester' => $grade->semester,
                'grade_type' => $grade->grade_type,
                'assignment_name' => $grade->assignment->title ?? null,
                'exam_name' => $grade->exam->title ?? null,
                'notes' => $grade->notes,
                'created_at' => $grade->created_at?->format('Y-m-d H:i:s'),
            ];
        }

        $student = Student::with(['class', 'user'])->find($studentId);

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'nisn' => $student->nisn,
                'class_name' => $student->class->name ?? 'Not assigned',
            ],
            'grades_by_subject' => array_values($gradesBySubject),
            'total_grades' => count($grades),
        ];
    }

    /**
     * Get attendance records for a specific child.
     */
    public function getChildAttendance(int $parentId, string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        $query = StudentAttendance::where('student_id', $studentId)
            ->with(['class', 'teacher', 'markedBy'])
            ->orderBy('attendance_date', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        $attendanceRecords = $query->get();

        $attendanceSummary = $this->getAttendanceSummary($studentId, $startDate, $endDate);

        $student = Student::with(['class', 'user'])->find($studentId);

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'nisn' => $student->nisn,
                'class_name' => $student->class->name ?? 'Not assigned',
            ],
            'summary' => $attendanceSummary,
            'records' => $attendanceRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->attendance_date->format('Y-m-d'),
                    'status' => $record->status,
                    'check_in_time' => $record->check_in_time?->format('H:i:s'),
                    'check_out_time' => $record->check_out_time?->format('H:i:s'),
                    'class_name' => $record->class->name ?? 'Unknown',
                    'teacher_name' => $record->teacher->name ?? 'Unknown',
                    'notes' => $record->notes,
                    'marked_by' => $record->markedBy->name ?? 'Unknown',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get current and upcoming assignments for a specific child.
     */
    public function getChildAssignments(int $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        $student = Student::with(['class', 'user'])->find($studentId);

        $virtualClassId = $student->class_id;

        $assignments = Assignment::where('virtual_class_id', $virtualClassId)
            ->where('is_published', true)
            ->where('publish_date', '<=', date('Y-m-d H:i:s'))
            ->orderBy('publish_date', 'desc')
            ->get();

        $upcomingAssignments = $assignments->filter(function ($assignment) {
            return $assignment->publish_date >= date('Y-m-d H:i:s');
        });

        $pastAssignments = $assignments->filter(function ($assignment) {
            return $assignment->publish_date < date('Y-m-d H:i:s');
        });

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'nisn' => $student->nisn,
                'class_name' => $student->class->name ?? 'Not assigned',
            ],
            'upcoming_assignments' => $upcomingAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'content' => $assignment->content,
                    'file_url' => $assignment->file_url,
                    'material_type' => $assignment->material_type,
                    'publish_date' => $assignment->publish_date?->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray(),
            'past_assignments' => $pastAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'content' => $assignment->content,
                    'file_url' => $assignment->file_url,
                    'material_type' => $assignment->material_type,
                    'publish_date' => $assignment->publish_date?->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray(),
            'total_assignments' => count($assignments),
        ];
    }

    /**
     * Verify that parent has access to specified child.
     */
    private function verifyParentAccess(int $parentId, string $studentId): void
    {
        $parent = ParentOrtu::where('user_id', $parentId)->first();

        if (!$parent) {
            throw new \Exception('Parent record not found');
        }

        $hasAccess = Student::where('id', $studentId)
            ->where('parent_id', $parent->id)
            ->exists();

        if (!$hasAccess) {
            throw new \Exception('Access denied: You do not have permission to view this child\'s data');
        }
    }

    /**
     * Get attendance summary statistics.
     */
    private function getAttendanceSummary(string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StudentAttendance::where('student_id', $studentId);

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        $totalRecords = $query->count();

        if ($totalRecords === 0) {
            return [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
                'attendance_rate' => 0.0,
            ];
        }

        $present = (clone $query)->where('status', 'present')->count();
        $absent = (clone $query)->where('status', 'absent')->count();
        $late = (clone $query)->where('status', 'late')->count();
        $excused = (clone $query)->where('status', 'excused')->count();

        $attendanceRate = $totalRecords > 0 ? round(($present + $excused) / $totalRecords * 100, 2) : 0.0;

        return [
            'total' => $totalRecords,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'attendance_rate' => $attendanceRate,
        ];
    }
}
