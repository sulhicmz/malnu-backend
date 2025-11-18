<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Hypervel\Http\Request;
use Hypervel\JWT\JWT;
use Hypervel\JWT\JWTException;

class MobileTeacherController extends AbstractController
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Get teacher dashboard data
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return [
                'success' => false,
                'message' => 'Teacher profile not found',
            ];
        }

        // Get classes taught by this teacher
        $classes = $teacher->classSubjects()
            ->with(['class', 'subject'])
            ->get()
            ->pluck('class')
            ->unique('id');

        // Get recent grades entered by this teacher
        $recentGrades = Grade::where('created_by', $user->id)
            ->with(['student.user', 'subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get assignments created by this teacher
        $recentAssignments = Assignment::where('created_by', $user->id)
            ->with(['virtualClass'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'success' => true,
            'data' => [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $user->name,
                ],
                'classes_count' => $classes->count(),
                'recent_grades_count' => $recentGrades->count(),
                'recent_assignments_count' => $recentAssignments->count(),
                'recent_grades' => $recentGrades->map(function($grade) {
                    return [
                        'id' => $grade->id,
                        'student' => [
                            'id' => $grade->student->id,
                            'name' => $grade->student->user->name,
                        ],
                        'subject' => [
                            'id' => $grade->subject->id,
                            'name' => $grade->subject->name,
                        ],
                        'grade' => $grade->grade,
                        'grade_type' => $grade->grade_type,
                        'created_at' => $grade->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'recent_assignments' => $recentAssignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'class' => $assignment->virtualClass->name ?? 'Unknown',
                        'publish_date' => $assignment->publish_date->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ];
    }

    /**
     * Get classes taught by the teacher
     */
    public function classes(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return [
                'success' => false,
                'message' => 'Teacher profile not found',
            ];
        }

        $classes = $teacher->classSubjects()
            ->with(['class', 'subject'])
            ->get()
            ->pluck('class')
            ->unique('id');

        return [
            'success' => true,
            'data' => $classes->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level ?? null,
                    'major' => $class->major ?? null,
                    'student_count' => $class->students()->count(),
                ];
            }),
        ];
    }

    /**
     * Get students in a specific class
     */
    public function students(Request $request, $classId)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return [
                'success' => false,
                'message' => 'Teacher profile not found',
            ];
        }

        // Verify that the teacher teaches this class
        $hasClass = $teacher->classSubjects()
            ->where('class_id', $classId)
            ->exists();

        if (!$hasClass) {
            return [
                'success' => false,
                'message' => 'You do not teach this class',
            ];
        }

        $students = Student::where('class_id', $classId)
            ->with(['user'])
            ->get();

        return [
            'success' => true,
            'data' => $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'user' => [
                        'id' => $student->user->id,
                        'name' => $student->user->name,
                        'email' => $student->user->email,
                    ],
                    'nisn' => $student->nisn,
                    'status' => $student->status,
                ];
            }),
        ];
    }

    /**
     * Get assignments created by the teacher
     */
    public function assignments(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $assignments = Assignment::where('created_by', $user->id)
            ->with(['virtualClass'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'success' => true,
            'data' => $assignments->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'content' => $assignment->content,
                    'file_url' => $assignment->file_url,
                    'is_published' => $assignment->is_published,
                    'publish_date' => $assignment->publish_date->format('Y-m-d H:i:s'),
                    'class' => [
                        'id' => $assignment->virtualClass->id,
                        'name' => $assignment->virtualClass->name,
                    ],
                ];
            }),
        ];
    }

    /**
     * Get grades entered by the teacher
     */
    public function grades(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $grades = Grade::where('created_by', $user->id)
            ->with(['student.user', 'subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'success' => true,
            'data' => $grades->map(function($grade) {
                return [
                    'id' => $grade->id,
                    'student' => [
                        'id' => $grade->student->id,
                        'name' => $grade->student->user->name,
                    ],
                    'subject' => [
                        'id' => $grade->subject->id,
                        'name' => $grade->subject->name,
                    ],
                    'class' => [
                        'id' => $grade->class->id,
                        'name' => $grade->class->name,
                    ],
                    'grade' => $grade->grade,
                    'semester' => $grade->semester,
                    'grade_type' => $grade->grade_type,
                    'notes' => $grade->notes,
                    'created_at' => $grade->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }
}