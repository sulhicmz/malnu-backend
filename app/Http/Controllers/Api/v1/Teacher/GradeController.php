<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Teacher;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class GradeController extends ApiController
{
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'subject_id' => 'required|string',
            'assignment_id' => 'nullable|string',
            'grade_value' => 'required|numeric|min:0|max:100',
            'grade_type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Verify that the student exists
        $student = Student::find($request->student_id);
        if (!$student) {
            return $this->errorResponse('Student not found', 404);
        }

        // Verify that the teacher has access to grade this student
        // This would require checking if the teacher teaches the subject to this student's class
        $hasAccess = $teacher->classSubjects()
            ->where('class_id', $student->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$hasAccess) {
            return $this->errorResponse('Access denied - cannot grade this student for this subject', 403);
        }

        // Create the grade record
        $grade = Grade::create([
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'assignment_id' => $request->assignment_id,
            'grade_value' => $request->grade_value,
            'grade_type' => $request->grade_type,
            'description' => $request->description,
            'created_by' => $user->id,
        ]);

        return $this->successResponse($grade, 'Grade recorded successfully');
    }
}