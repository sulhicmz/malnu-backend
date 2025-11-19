<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\Grading\Grade;
use Hypervel\Http\Request;

class GradeController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return $this->errorResponse('Student profile not found', 404);
        }

        $grades = Grade::where('student_id', $student->id)
            ->with(['subject', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse([
            'grades' => $grades,
        ]);
    }
}