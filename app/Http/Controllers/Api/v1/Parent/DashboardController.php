<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Parent;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\Grading\Grade;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $parent = $user->parent;
        
        if (!$parent) {
            return $this->errorResponse('Parent profile not found', 404);
        }

        // Get related students
        $students = $parent->students()->with(['user', 'class'])->get();

        // Get summary data for all students
        $summary = [];
        foreach ($students as $student) {
            $recentGrades = Grade::where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
            $assignmentsPending = Assignment::where('class_id', $student->class_id)
                ->where('due_date', '>=', now())
                ->count();

            $summary[] = [
                'student' => $student,
                'recent_grades' => $recentGrades,
                'assignments_pending_count' => $assignmentsPending,
                'attendance_rate' => 'N/A', // Placeholder - would need actual attendance model
            ];
        }

        return $this->successResponse([
            'parent' => $parent,
            'students' => $students,
            'summary' => $summary,
        ]);
    }
}