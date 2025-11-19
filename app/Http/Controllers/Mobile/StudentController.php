<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Http\Request;

class StudentController extends AbstractController
{
    /**
     * Get student dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        // Get recent assignments
        $recentAssignments = Assignment::where('class_id', $student->class_id)
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get(['id', 'title', 'description', 'due_date', 'created_at']);

        // Get latest grades
        $latestGrades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'subject_id', 'grade_value', 'grade_letter', 'created_at']);

        // Get today's schedule
        $todaysSchedule = Schedule::where('class_id', $student->class_id)
            ->whereDate('date', now())
            ->with('subject', 'teacher')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'class' => $student->class->name ?? null,
            ],
            'recent_assignments' => $recentAssignments,
            'latest_grades' => $latestGrades,
            'todays_schedule' => $todaysSchedule,
            'attendance_rate' => $this->calculateAttendanceRate($student->id),
        ]);
    }

    /**
     * Get student grades
     */
    public function grades(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with(['subject', 'competency'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'grades' => $grades,
            'average_grade' => $grades->avg('grade_value'),
        ]);
    }

    /**
     * Get student assignments
     */
    public function assignments(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        $assignments = Assignment::where('class_id', $student->class_id)
            ->with(['subject', 'class'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'assignments' => $assignments,
            'pending_count' => $assignments->where('due_date', '>=', now())->count(),
            'overdue_count' => $assignments->where('due_date', '<', now())->count(),
        ]);
    }

    /**
     * Get student schedule
     */
    public function schedule(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        $schedule = Schedule::where('class_id', $student->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'schedule' => $schedule,
        ]);
    }

    /**
     * Get student attendance
     */
    public function attendance(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        // This would need to be implemented with actual attendance records
        // For now, returning a placeholder response
        return response()->json([
            'attendance_records' => [],
            'attendance_rate' => $this->calculateAttendanceRate($student->id),
        ]);
    }

    /**
     * Calculate attendance rate for a student
     */
    private function calculateAttendanceRate(string $studentId): float
    {
        // Placeholder implementation - would need actual attendance records
        // In a real implementation, this would count attendance records
        return 95.0; // Return a default value for now
    }
}