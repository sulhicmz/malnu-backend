<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\ELearning\Assignment;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hypervel\Http\Request;
use Hypervel\JWT\JWT;
use Hypervel\JWT\JWTException;

class MobileStudentController extends AbstractController
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Get student dashboard data
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

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student profile not found',
            ];
        }

        // Get recent grades
        $recentGrades = $student->grades()
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get upcoming assignments
        $upcomingAssignments = collect([]);
        if ($student->class) {
            $virtualClasses = $student->class->virtualClasses()
                ->with(['assignments' => function($query) {
                    $query->where('is_published', true)
                          ->where('publish_date', '<=', now())
                          ->orderBy('publish_date', 'desc')
                          ->limit(5);
                }])
                ->get();
                
            foreach ($virtualClasses as $virtualClass) {
                $upcomingAssignments = $upcomingAssignments->merge($virtualClass->assignments);
            }
        }

        // Get schedule for the week
        $weeklySchedule = Schedule::whereHas('classSubject', function($query) use ($student) {
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
                    'nisn' => $student->nisn,
                    'class' => $student->class ? $student->class->name : null,
                    'status' => $student->status,
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
                        'created_by' => $assignment->creator->name ?? 'Unknown',
                    ];
                }),
                'weekly_schedule' => $weeklySchedule->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time->format('H:i'),
                        'end_time' => $schedule->end_time->format('H:i'),
                        'subject' => $schedule->classSubject->subject->name ?? 'Unknown',
                        'teacher' => $schedule->classSubject->teacher->name ?? 'Unknown',
                        'room' => $schedule->room,
                    ];
                }),
            ],
        ];
    }

    /**
     * Get student grades
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

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student profile not found',
            ];
        }

        $grades = $student->grades()
            ->with(['subject', 'class'])
            ->orderBy('semester', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'success' => true,
            'data' => $grades->map(function($grade) {
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
        ];
    }

    /**
     * Get student assignments
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

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student profile not found',
            ];
        }

        // Get assignments for the student's class
        $assignments = collect([]);
        if ($student->class) {
            $virtualClasses = $student->class->virtualClasses()
                ->with(['assignments' => function($query) {
                    $query->where('is_published', true)
                          ->where('publish_date', '<=', now())
                          ->orderBy('publish_date', 'desc');
                }])
                ->get();
                
            foreach ($virtualClasses as $virtualClass) {
                $assignments = $assignments->merge($virtualClass->assignments);
            }
        }

        return [
            'success' => true,
            'data' => $assignments->map(function($assignment) {
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
        ];
    }

    /**
     * Get student schedule
     */
    public function schedule(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student profile not found',
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
            'data' => $schedule->map(function($schedule) {
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
        ];
    }

    /**
     * Get student profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        $student = Student::where('user_id', $user->id)
            ->with(['user', 'class'])
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student profile not found',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $student->id,
                'user' => [
                    'id' => $student->user->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'avatar_url' => $student->user->avatar_url,
                ],
                'nisn' => $student->nisn,
                'class' => [
                    'id' => $student->class->id,
                    'name' => $student->class->name,
                ],
                'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : null,
                'birth_place' => $student->birth_place,
                'address' => $student->address,
                'status' => $student->status,
                'enrollment_date' => $student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : null,
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