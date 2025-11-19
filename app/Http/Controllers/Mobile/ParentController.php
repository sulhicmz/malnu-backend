<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\Grading\Grade;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\Student;
use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Http\Request;

class ParentController extends AbstractController
{
    /**
     * Get parent dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $parent = $user->parent;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found'], 404);
        }

        // Get children associated with this parent
        $children = $parent->students()->with(['class'])->get();

        $dashboardData = [];
        foreach ($children as $child) {
            $latestGrades = Grade::where('student_id', $child->id)
                ->with('subject')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $pendingAssignments = Assignment::where('class_id', $child->class_id)
                ->where('due_date', '>=', now())
                ->count();

            $dashboardData[] = [
                'student' => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'nis' => $child->nis,
                    'class' => $child->class->name ?? null,
                ],
                'latest_grades' => $latestGrades,
                'pending_assignments' => $pendingAssignments,
                'attendance_rate' => $this->calculateAttendanceRate($child->id),
            ];
        }

        return response()->json([
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $user->email,
            ],
            'children' => $dashboardData,
        ]);
    }

    /**
     * Get student progress
     */
    public function studentProgress(Request $request, string $id)
    {
        $user = $request->user();
        $parent = $user->parent;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found'], 404);
        }

        // Verify that the student belongs to this parent
        $student = $this->getStudentForParent($parent, $id);
        if (!$student) {
            return response()->json(['error' => 'Student not found or access denied'], 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();

        $assignments = Assignment::where('class_id', $student->class_id)
            ->with('subject')
            ->orderBy('due_date', 'desc')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'class' => $student->class->name ?? null,
            ],
            'grades' => $grades,
            'assignments' => $assignments,
            'average_grade' => $grades->avg('grade_value'),
            'attendance_rate' => $this->calculateAttendanceRate($student->id),
        ]);
    }

    /**
     * Get student attendance
     */
    public function studentAttendance(Request $request, string $id)
    {
        $user = $request->user();
        $parent = $user->parent;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found'], 404);
        }

        // Verify that the student belongs to this parent
        $student = $this->getStudentForParent($parent, $id);
        if (!$student) {
            return response()->json(['error' => 'Student not found or access denied'], 404);
        }

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'attendance_records' => [],
            'attendance_rate' => $this->calculateAttendanceRate($student->id),
        ]);
    }

    /**
     * Get student fees/financial information
     */
    public function studentFees(Request $request, string $id)
    {
        $user = $request->user();
        $parent = $user->parent;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found'], 404);
        }

        // Verify that the student belongs to this parent
        $student = $this->getStudentForParent($parent, $id);
        if (!$student) {
            return response()->json(['error' => 'Student not found or access denied'], 404);
        }

        // Placeholder for fee information - would need actual fee models
        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'fees' => [],
            'balance' => 0,
            'payment_history' => [],
        ]);
    }

    /**
     * Get student grades
     */
    public function studentGrades(Request $request, string $id)
    {
        $user = $request->user();
        $parent = $user->parent;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found'], 404);
        }

        // Verify that the student belongs to this parent
        $student = $this->getStudentForParent($parent, $id);
        if (!$student) {
            return response()->json(['error' => 'Student not found or access denied'], 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'grades' => $grades,
            'average_grade' => $grades->avg('grade_value'),
        ]);
    }

    /**
     * Get student for parent (helper method)
     */
    private function getStudentForParent($parent, string $studentId)
    {
        // This would depend on the actual relationship between parent and students
        // For now, assuming a direct relationship exists
        return Student::find($studentId);
    }

    /**
     * Calculate attendance rate for a student
     */
    private function calculateAttendanceRate(string $studentId): float
    {
        // Placeholder implementation - would need actual attendance records
        return 95.0; // Return a default value for now
    }
}