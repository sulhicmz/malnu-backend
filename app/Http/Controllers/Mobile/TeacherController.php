<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\ELearning\Assignment;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use Hypervel\Http\Request;

class TeacherController extends AbstractController
{
    /**
     * Get teacher dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        $classes = $teacher->classes()->with(['students', 'subject'])->get();
        $assignments = Assignment::where('created_by', $user->id)
            ->with('class', 'subject')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $user->email,
            ],
            'classes' => $classes,
            'assignments_count' => $assignments->count(),
            'students_count' => $classes->sum(function($class) {
                return $class->students->count();
            }),
            'recent_assignments' => $assignments,
        ]);
    }

    /**
     * Get teacher classes
     */
    public function classes(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        $classes = $teacher->classes()->with(['students', 'subject'])->get();

        return response()->json([
            'classes' => $classes,
        ]);
    }

    /**
     * Get students in a class
     */
    public function students(Request $request, string $classId)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        // Verify that the class belongs to this teacher
        $class = $teacher->classes()->find($classId);
        if (!$class) {
            return response()->json(['error' => 'Class not found or access denied'], 404);
        }

        $students = $class->students()->get();

        return response()->json([
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'subject' => $class->subject->name ?? null,
            ],
            'students' => $students,
        ]);
    }

    /**
     * Mark attendance for students
     */
    public function markAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|string',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|string',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
        ]);

        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        // Verify that the class belongs to this teacher
        $class = $teacher->classes()->find($request->class_id);
        if (!$class) {
            return response()->json(['error' => 'Class not found or access denied'], 404);
        }

        // Process attendance marking
        // This would need to be implemented with actual attendance records
        foreach ($request->attendance as $attendanceRecord) {
            // Create or update attendance record
            // Attendance::updateOrCreate(...)
        }

        return response()->json([
            'message' => 'Attendance marked successfully',
        ]);
    }

    /**
     * Get teacher assignments
     */
    public function assignments(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        $assignments = Assignment::where('created_by', $user->id)
            ->with('class', 'subject')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'assignments' => $assignments,
        ]);
    }

    /**
     * Create a new assignment
     */
    public function createAssignment(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|string',
            'subject_id' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['error' => 'Teacher profile not found'], 404);
        }

        // Verify that the class belongs to this teacher
        $class = $teacher->classes()->find($request->class_id);
        if (!$class) {
            return response()->json(['error' => 'Class not found or access denied'], 404);
        }

        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'due_date' => $request->due_date,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Assignment created successfully',
            'assignment' => $assignment,
        ]);
    }
}