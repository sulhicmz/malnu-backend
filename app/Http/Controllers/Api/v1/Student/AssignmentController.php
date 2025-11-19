<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\ELearning\Assignment;
use Hypervel\Http\Request;

class AssignmentController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return $this->errorResponse('Student profile not found', 404);
        }

        $assignments = Assignment::where('class_id', $student->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Add status (completed/pending) to each assignment
        foreach ($assignments as $assignment) {
            $assignment->status = $assignment->due_date < now() ? 'overdue' : 'pending';
            if ($assignment->pivot && $assignment->pivot->submitted_at) {
                $assignment->status = 'submitted';
            }
        }

        return $this->successResponse([
            'assignments' => $assignments,
        ]);
    }
}