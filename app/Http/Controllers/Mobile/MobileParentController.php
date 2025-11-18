<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;
use Hypervel\JWT\JWT;
use Hypervel\JWT\JWTException;

class MobileParentController extends AbstractController
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Get parent dashboard data
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

        // Get all students associated with this parent
        $students = Student::where('parent_id', $user->id)->with(['user', 'class'])->get();

        $dashboardData = [];
        foreach ($students as $student) {
            // Get recent grades for this student
            $recentGrades = $student->grades()
                ->with(['subject', 'class'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get upcoming assignments for this student
            $upcomingAssignments = $student->class->virtualClasses()
                ->with(['assignments' => function($query) {
                    $query->where('is_published', true)
                          ->where('publish_date', '<=', now())
                          ->orderBy('publish_date', 'desc')
                          ->limit(5);
                }])
                ->get()
                ->pluck('assignments')
                ->flatten();

            $dashboardData[] = [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                ],
                'recent_grades' => $recentGrades->map(function($grade) {
                    return [
                        'id' => $grade->id,
                        'subject' => $grade->subject->name ?? 'Unknown',
                        'grade' => $grade->grade,
                        'semester' => $grade->semester,
                        'grade_type' => $grade->grade_type,
                        'created_at' => $grade->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'upcoming_assignments' => $upcomingAssignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'content' => $assignment->content,
                        'publish_date' => $assignment->publish_date->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        }

        return [
            'success' => true,
            'data' => $dashboardData,
        ];
    }

    /**
     * Get student grades
     */
    public function studentGrades(Request $request, $studentId)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        // Verify that the student belongs to this parent
        $student = Student::where('id', $studentId)
            ->where('parent_id', $user->id)
            ->with(['user', 'class'])
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found or not associated with this parent',
            ];
        }

        $grades = $student->grades()
            ->with(['subject', 'class'])
            ->orderBy('semester', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                ],
                'grades' => $grades->map(function($grade) {
                    return [
                        'id' => $grade->id,
                        'subject' => [
                            'id' => $grade->subject->id,
                            'name' => $grade->subject->name,
                        ],
                        'grade' => $grade->grade,
                        'semester' => $grade->semester,
                        'grade_type' => $grade->grade_type,
                        'notes' => $grade->notes,
                        'created_at' => $grade->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ],
        ];
    }

    /**
     * Get student assignments
     */
    public function studentAssignments(Request $request, $studentId)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        // Verify that the student belongs to this parent
        $student = Student::where('id', $studentId)
            ->where('parent_id', $user->id)
            ->with(['user', 'class'])
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found or not associated with this parent',
            ];
        }

        // Get assignments for the student's class
        $assignments = $student->class->virtualClasses()
            ->with(['assignments' => function($query) {
                $query->where('is_published', true)
                      ->where('publish_date', '<=', now())
                      ->orderBy('publish_date', 'desc');
            }])
            ->get()
            ->pluck('assignments')
            ->flatten();

        return [
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                ],
                'assignments' => $assignments->map(function($assignment) {
                    return [
                        'id' => $assignment->id,
                        'title' => $assignment->title,
                        'content' => $assignment->content,
                        'file_url' => $assignment->file_url,
                        'publish_date' => $assignment->publish_date->format('Y-m-d H:i:s'),
                        'created_by' => [
                            'id' => $assignment->creator->id,
                            'name' => $assignment->creator->name,
                        ],
                    ];
                }),
            ],
        ];
    }

    /**
     * Get student attendance (placeholder - would need actual attendance model)
     */
    public function studentAttendance(Request $request, $studentId)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        // Verify that the student belongs to this parent
        $student = Student::where('id', $studentId)
            ->where('parent_id', $user->id)
            ->with(['user', 'class'])
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found or not associated with this parent',
            ];
        }

        // Placeholder for attendance data
        // In a real implementation, this would connect to an attendance tracking system
        return [
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                ],
                'attendance_summary' => [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                    'total_days' => 0,
                ],
                'recent_attendance' => [],
            ],
        ];
    }

    /**
     * Get student schedule
     */
    public function studentSchedule(Request $request, $studentId)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        // Verify that the student belongs to this parent
        $student = Student::where('id', $studentId)
            ->where('parent_id', $user->id)
            ->with(['user', 'class'])
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found or not associated with this parent',
            ];
        }

        $schedule = Schedule::whereHas('classSubject', function($query) use ($student) {
                $query->where('class_id', $student->class_id);
            })
            ->with(['classSubject.subject', 'classSubject.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return [
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                ],
                'schedule' => $schedule->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'day_of_week' => $this->getDayName($schedule->day_of_week),
                        'day_number' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time->format('H:i'),
                        'end_time' => $schedule->end_time->format('H:i'),
                        'subject' => [
                            'id' => $schedule->classSubject->subject->id,
                            'name' => $schedule->classSubject->subject->name,
                        ],
                        'teacher' => [
                            'id' => $schedule->classSubject->teacher->id,
                            'name' => $schedule->classSubject->teacher->name,
                        ],
                        'room' => $schedule->room,
                    ];
                }),
            ],
        ];
    }

    /**
     * Get day name from day number
     */
    private function getDayName($dayNumber)
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return $days[$dayNumber] ?? 'Unknown';
    }
}