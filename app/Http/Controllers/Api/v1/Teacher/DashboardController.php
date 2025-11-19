<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Teacher;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Get teacher's classes (as homeroom teacher)
        $classes = $teacher->classes()->with(['subject', 'students'])->get();

        // Also get classes where teacher teaches specific subjects
        $subjectClasses = $teacher->classSubjects()->with(['class', 'subject'])->get();

        // Get recent assignments created by this teacher
        $assignments = Assignment::where('created_by', $user->id)
            ->with(['class', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return $this->successResponse([
            'teacher' => $teacher,
            'homeroom_classes_count' => $classes->count(),
            'subject_classes_count' => $subjectClasses->count(),
            'homeroom_classes' => $classes,
            'subject_classes' => $subjectClasses->pluck('class'),
            'recent_assignments' => $assignments,
        ]);
    }
}