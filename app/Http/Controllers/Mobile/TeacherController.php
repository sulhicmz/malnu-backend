<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Models\User;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\LearningMaterial;
use App\Models\SchoolManagement\Schedule;
use App\Models\Grading\Grade;
use App\Models\Attendance\Attendance;
use App\Models\ELearning\Quiz;
use App\Models\OnlineExam\Exam;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class TeacherController extends BaseMobileController
{
    /**
     * Get teacher profile information
     */
    public function profile()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        $teacher = $user->teacher;

        return $this->respondWithSuccess([
            'teacher' => [
                'id' => $teacher->id,
                'user_id' => $teacher->user_id,
                'nip' => $teacher->nip,
                'full_name' => $teacher->full_name,
                'birth_place' => $teacher->birth_place,
                'birth_date' => $teacher->birth_date,
                'gender' => $teacher->gender,
                'religion' => $teacher->religion,
                'address' => $teacher->address,
                'phone' => $teacher->phone,
                'email' => $teacher->email,
                'subject_id' => $teacher->subject_id,
                'subject_name' => $teacher->subject ? $teacher->subject->subject_name : 'Unknown',
                'created_at' => $teacher->created_at,
                'updated_at' => $teacher->updated_at
            ]
        ], 'Teacher profile retrieved successfully');
    }

    /**
     * Get teacher's classes
     */
    public function classes()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        $teacher = $user->teacher;
        
        // Get classes where this teacher is assigned (through schedules or direct assignment)
        $classIds = Schedule::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique()
            ->toArray();

        $classes = ClassModel::whereIn('id', $classIds)
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'major' => $class->major,
                    'year' => $class->year,
                    'capacity' => $class->capacity,
                    'student_count' => $class->students->count(),
                    'created_at' => $class->created_at
                ];
            });

        return $this->respondWithSuccess([
            'classes' => $classes
        ], 'Teacher classes retrieved successfully');
    }

    /**
     * Get students in a specific class
     */
    public function classStudents($classId)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher is assigned to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to access this class', 403);
        }

        $students = Student::where('class_id', $classId)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender,
                    'phone' => $student->phone,
                    'email' => $student->email
                ];
            });

        return $this->respondWithSuccess([
            'students' => $students,
            'class_name' => ClassModel::find($classId)->class_name ?? 'Unknown'
        ], 'Class students retrieved successfully');
    }

    /**
     * Get teacher's schedule
     */
    public function schedule()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        $schedules = Schedule::where('teacher_id', $user->teacher->id)
            ->with(['subject', 'class'])
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'subject_name' => $schedule->subject ? $schedule->subject->subject_name : 'Unknown',
                    'class_name' => $schedule->class ? $schedule->class->class_name : 'Unknown',
                    'room' => $schedule->room
                ];
            });

        return $this->respondWithSuccess([
            'schedule' => $schedules
        ], 'Teacher schedule retrieved successfully');
    }

    /**
     * Record attendance for a class
     */
    public function recordAttendance(Request $request, $classId)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher is assigned to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to access this class', 403);
        }

        $date = $request->input('date', date('Y-m-d'));
        $attendanceData = $request->input('attendance', []);

        $results = [];
        foreach ($attendanceData as $attendance) {
            $studentId = $attendance['student_id'] ?? null;
            $status = $attendance['status'] ?? 'present'; // present, absent, late, excuse
            
            if (!$studentId) {
                continue; // Skip if no student ID provided
            }

            // Verify student belongs to this class
            $student = Student::find($studentId);
            if (!$student || $student->class_id != $classId) {
                continue; // Skip if student doesn't belong to this class
            }

            // Check if attendance already exists for this date
            $existingAttendance = Attendance::where('student_id', $studentId)
                ->where('date', $date)
                ->first();

            if ($existingAttendance) {
                // Update existing attendance
                $existingAttendance->update([
                    'status' => $status,
                    'updated_at' => now()
                ]);
                $results[] = [
                    'student_id' => $studentId,
                    'status' => $status,
                    'updated' => true
                ];
            } else {
                // Create new attendance record
                Attendance::create([
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $status,
                    'created_by' => $user->id
                ]);
                $results[] = [
                    'student_id' => $studentId,
                    'status' => $status,
                    'updated' => false
                ];
            }
        }

        return $this->respondWithSuccess([
            'results' => $results,
            'date' => $date,
            'class_id' => $classId
        ], 'Attendance recorded successfully');
    }

    /**
     * Get attendance for a class
     */
    public function classAttendance($classId, $date = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher is assigned to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to access this class', 403);
        }

        if (!$date) {
            $date = date('Y-m-d'); // Default to today
        }

        $attendances = Attendance::whereHas('student', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->where('date', $date)
            ->with(['student'])
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'student_id' => $attendance->student_id,
                    'student_name' => $attendance->student->full_name,
                    'status' => $attendance->status,
                    'date' => $attendance->date
                ];
            });

        return $this->respondWithSuccess([
            'attendance' => $attendances,
            'date' => $date,
            'class_id' => $classId
        ], 'Class attendance retrieved successfully');
    }

    /**
     * Create assignment for a class
     */
    public function createAssignment(Request $request, $classId)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher is assigned to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to access this class', 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date'
        ]);

        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'class_id' => $classId,
            'subject_id' => $request->subject_id,
            'teacher_id' => $user->teacher->id,
            'due_date' => $request->due_date,
            'status' => 'active'
        ]);

        return $this->respondWithSuccess([
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'class_id' => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'teacher_id' => $assignment->teacher_id,
                'due_date' => $assignment->due_date,
                'status' => $assignment->status,
                'created_at' => $assignment->created_at
            ]
        ], 'Assignment created successfully');
    }

    /**
     * Get assignments for a class
     */
    public function classAssignments($classId)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher is assigned to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to access this class', 403);
        }

        $assignments = Assignment::where('class_id', $classId)
            ->where('teacher_id', $user->teacher->id)
            ->with(['subject'])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'subject_name' => $assignment->subject ? $assignment->subject->subject_name : 'Unknown',
                    'due_date' => $assignment->due_date,
                    'status' => $assignment->status,
                    'created_at' => $assignment->created_at
                ];
            });

        return $this->respondWithSuccess([
            'assignments' => $assignments
        ], 'Class assignments retrieved successfully');
    }

    /**
     * Record grades for students
     */
    public function recordGrades(Request $request, $classId, $subjectId)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->teacher) {
            return $this->respondWithError('Teacher profile not found', 404);
        }

        // Verify that the teacher teaches this subject to this class
        $scheduleExists = Schedule::where('teacher_id', $user->teacher->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();
            
        if (!$scheduleExists) {
            return $this->respondWithError('You are not authorized to grade this class/subject', 403);
        }

        $gradeData = $request->input('grades', []);

        $results = [];
        foreach ($gradeData as $grade) {
            $studentId = $grade['student_id'] ?? null;
            $score = $grade['score'] ?? null;
            $competencyId = $grade['competency_id'] ?? null;
            $semester = $grade['semester'] ?? 1;
            $year = $grade['year'] ?? date('Y');

            if (!$studentId || !$score || !$competencyId) {
                continue; // Skip if required data is missing
            }

            // Verify student belongs to this class
            $student = Student::find($studentId);
            if (!$student || $student->class_id != $classId) {
                continue; // Skip if student doesn't belong to this class
            }

            // Calculate grade letter based on score
            $gradeLetter = $this->calculateGradeLetter($score);

            // Check if grade already exists
            $existingGrade = Grade::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('competency_id', $competencyId)
                ->where('semester', $semester)
                ->where('year', $year)
                ->first();

            if ($existingGrade) {
                // Update existing grade
                $existingGrade->update([
                    'score' => $score,
                    'grade_letter' => $gradeLetter,
                    'updated_by' => $user->id
                ]);
                $results[] = [
                    'student_id' => $studentId,
                    'updated' => true
                ];
            } else {
                // Create new grade record
                Grade::create([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'competency_id' => $competencyId,
                    'score' => $score,
                    'grade_letter' => $gradeLetter,
                    'semester' => $semester,
                    'year' => $year,
                    'created_by' => $user->id
                ]);
                $results[] = [
                    'student_id' => $studentId,
                    'updated' => false
                ];
            }
        }

        return $this->respondWithSuccess([
            'results' => $results,
            'class_id' => $classId,
            'subject_id' => $subjectId
        ], 'Grades recorded successfully');
    }

    /**
     * Calculate grade letter based on score
     */
    private function calculateGradeLetter($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}