<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Teacher;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class AssignmentController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Get assignments created by this teacher
        $assignments = Assignment::where('created_by', $user->id)
            ->with(['class', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse([
            'assignments' => $assignments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|string',
            'subject_id' => 'required|string',
            'due_date' => 'required|date',
            'max_score' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Verify that the teacher has access to this class
        $hasClassAccess = $teacher->classes->contains('id', $request->class_id) || 
                         $teacher->classSubjects->pluck('class_id')->contains($request->class_id);

        if (!$hasClassAccess) {
            return $this->errorResponse('Access denied to this class', 403);
        }

        // Create the assignment
        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'due_date' => $request->due_date,
            'max_score' => $request->max_score ?? 100,
            'created_by' => $user->id,
        ]);

        return $this->successResponse($assignment, 'Assignment created successfully');
    }
}