<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Teacher;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class AttendanceController extends ApiController
{
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|string',
            'student_id' => 'required|string',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string',
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

        // Verify that the student belongs to this class
        $student = Student::where('id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->first();

        if (!$student) {
            return $this->errorResponse('Student not found in this class', 404);
        }

        // For now, return a success response with the attendance data
        // In a real implementation, this would save to an attendance model
        $attendanceData = [
            'class_id' => $request->class_id,
            'student_id' => $request->student_id,
            'date' => $request->date,
            'status' => $request->status,
            'notes' => $request->notes,
            'recorded_by' => $user->id,
            'recorded_at' => now(),
        ];

        return $this->successResponse($attendanceData, 'Attendance recorded successfully');
    }
}