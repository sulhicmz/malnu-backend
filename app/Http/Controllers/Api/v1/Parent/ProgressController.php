<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Parent;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\Grading\Grade;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class ProgressController extends ApiController
{
    public function show(Request $request, string $studentId)
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

        // Get grades for progress analysis
        $grades = Grade::where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assignments progress
        $assignments = Assignment::where('class_id', $student->class_id)
            ->orderBy('due_date', 'asc')
            ->get();

        // Calculate progress metrics
        $subjectGrades = [];
        foreach ($grades as $grade) {
            $subjectId = $grade->subject_id;
            if (!isset($subjectGrades[$subjectId])) {
                $subjectGrades[$subjectId] = [
                    'subject' => $grade->subject,
                    'grades' => [],
                    'average' => 0,
                ];
            }
            $subjectGrades[$subjectId]['grades'][] = $grade;
        }

        // Calculate averages for each subject
        foreach ($subjectGrades as &$subjectGrade) {
            $total = 0;
            $count = 0;
            foreach ($subjectGrade['grades'] as $grade) {
                $total += $grade->grade_value;
                $count++;
            }
            $subjectGrade['average'] = $count > 0 ? $total / $count : 0;
        }

        return $this->successResponse([
            'student' => $student,
            'progress' => [
                'overall_average' => count($grades) > 0 ? $grades->avg('grade_value') : 0,
                'subject_grades' => array_values($subjectGrades),
                'assignments_completed' => $assignments->where('due_date', '<', now())->count(),
                'assignments_pending' => $assignments->where('due_date', '>=', now())->count(),
            ],
        ]);
    }
}