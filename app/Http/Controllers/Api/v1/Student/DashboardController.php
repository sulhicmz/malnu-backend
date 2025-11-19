<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Schedule;
use Hypervel\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get student profile
        $student = $user->student;
        if (!$student) {
            return $this->errorResponse('Student profile not found', 404);
        }

        // Get recent assignments
        $assignments = Assignment::where('class_id', $student->class_id)
            ->where('due_date', '>=', now())
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Get recent grades
        $grades = Grade::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get today's schedule
        $schedule = Schedule::where('class_id', $student->class_id)
            ->where('date', today())
            ->get();

        return $this->successResponse([
            'student' => $student,
            'recent_assignments' => $assignments,
            'recent_grades' => $grades,
            'today_schedule' => $schedule,
        ]);
    }
}