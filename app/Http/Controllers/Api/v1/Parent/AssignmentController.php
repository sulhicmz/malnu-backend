<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Parent;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class AssignmentController extends ApiController
{
    public function index(Request $request, string $studentId)
    {
        $user = $request->user();
        $parent = $user->parent;
        
        if (!$parent) {
            return $this->errorResponse('Parent profile not found', 404);
        }

        // Verify that the student belongs to this parent
        $student = Student::where('id', $studentId)
            ->whereHas('parent', function($query) use ($parent) {
                $query->where('id', $parent->id);
            })
            ->first();

        if (!$student) {
            return $this->errorResponse('Student not found or access denied', 404);
        }

        $assignments = Assignment::where('class_id', $student->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('due_date', 'asc')
            ->get();

        return $this->successResponse([
            'student' => $student,
            'assignments' => $assignments,
        ]);
    }
}