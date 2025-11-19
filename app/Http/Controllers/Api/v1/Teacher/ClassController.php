<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Teacher;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class ClassController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Get classes where teacher is homeroom teacher
        $homeroomClasses = $teacher->classes()->with(['subject', 'students'])->get();

        // Get classes where teacher teaches subjects
        $subjectClasses = $teacher->classSubjects()
            ->with(['class', 'subject'])
            ->get()
            ->pluck('class')
            ->unique('id');

        // Combine both sets of classes
        $allClasses = collect();
        $allClasses = $allClasses->concat($homeroomClasses);
        foreach ($subjectClasses as $class) {
            if (!$allClasses->contains('id', $class->id)) {
                $allClasses->push($class);
            }
        }

        return $this->successResponse([
            'classes' => $allClasses,
        ]);
    }

    public function students(Request $request, string $classId)
    {
        $user = $request->user();
        $teacher = $user->teacher;
        
        if (!$teacher) {
            return $this->errorResponse('Teacher profile not found', 404);
        }

        // Verify that the teacher has access to this class
        $hasClassAccess = $teacher->classes->contains('id', $classId) || 
                         $teacher->classSubjects->pluck('class_id')->contains($classId);

        if (!$hasClassAccess) {
            return $this->errorResponse('Access denied to this class', 403);
        }

        $students = Student::where('class_id', $classId)
            ->with(['user'])
            ->get();

        return $this->successResponse([
            'class_id' => $classId,
            'students' => $students,
        ]);
    }
}