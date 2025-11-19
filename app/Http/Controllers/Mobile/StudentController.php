<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\LearningMaterial;
use App\Models\SchoolManagement\Schedule;
use App\Models\Grading\Grade;
use App\Models\Attendance\Attendance;
use App\Models\ELearning\Quiz;
use App\Models\OnlineExam\ExamResult;
use Hypervel\Http\Request;

class StudentController extends BaseMobileController
{
    /**
     * Get student profile information
     */
    public function profile()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $student = $user->student;

        return $this->respondWithSuccess([
            'student' => [
                'id' => $student->id,
                'user_id' => $student->user_id,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'full_name' => $student->full_name,
                'birth_place' => $student->birth_place,
                'birth_date' => $student->birth_date,
                'gender' => $student->gender,
                'religion' => $student->religion,
                'address' => $student->address,
                'phone' => $student->phone,
                'email' => $student->email,
                'class_id' => $student->class_id,
                'class_name' => $student->class ? $student->class->class_name : null,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at
            ]
        ], 'Student profile retrieved successfully');
    }

    /**
     * Get student's grades
     */
    public function grades()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $grades = Grade::where('student_id', $user->student->id)
            ->with(['subject', 'competency'])
            ->get()
            ->map(function ($grade) {
                return [
                    'id' => $grade->id,
                    'subject_name' => $grade->subject ? $grade->subject->subject_name : 'Unknown',
                    'competency_name' => $grade->competency ? $grade->competency->competency_name : 'Unknown',
                    'score' => $grade->score,
                    'grade_letter' => $grade->grade_letter,
                    'semester' => $grade->semester,
                    'year' => $grade->year,
                    'created_at' => $grade->created_at
                ];
            });

        return $this->respondWithSuccess([
            'grades' => $grades
        ], 'Grades retrieved successfully');
    }

    /**
     * Get student's assignments
     */
    public function assignments()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $assignments = Assignment::where('class_id', $user->student->class_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'subject_name' => $assignment->subject ? $assignment->subject->subject_name : 'Unknown',
                    'teacher_name' => $assignment->teacher ? $assignment->teacher->full_name : 'Unknown',
                    'due_date' => $assignment->due_date,
                    'status' => $assignment->status,
                    'created_at' => $assignment->created_at
                ];
            });

        return $this->respondWithSuccess([
            'assignments' => $assignments
        ], 'Assignments retrieved successfully');
    }

    /**
     * Get student's schedule
     */
    public function schedule()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $schedules = Schedule::where('class_id', $user->student->class_id)
            ->with(['subject', 'teacher', 'class'])
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'subject_name' => $schedule->subject ? $schedule->subject->subject_name : 'Unknown',
                    'teacher_name' => $schedule->teacher ? $schedule->teacher->full_name : 'Unknown',
                    'class_name' => $schedule->class ? $schedule->class->class_name : 'Unknown',
                    'room' => $schedule->room
                ];
            });

        return $this->respondWithSuccess([
            'schedule' => $schedules
        ], 'Schedule retrieved successfully');
    }

    /**
     * Get student's attendance
     */
    public function attendance()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $attendances = Attendance::where('student_id', $user->student->id)
            ->with(['schedule'])
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date,
                    'status' => $attendance->status,
                    'schedule_subject' => $attendance->schedule ? $attendance->schedule->subject->subject_name : 'Unknown',
                    'created_at' => $attendance->created_at
                ];
            });

        return $this->respondWithSuccess([
            'attendance' => $attendances
        ], 'Attendance records retrieved successfully');
    }

    /**
     * Get student's learning materials
     */
    public function learningMaterials()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $materials = LearningMaterial::where('class_id', $user->student->class_id)
            ->with(['subject', 'teacher'])
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'file_url' => $material->file_url,
                    'subject_name' => $material->subject ? $material->subject->subject_name : 'Unknown',
                    'teacher_name' => $material->teacher ? $material->teacher->full_name : 'Unknown',
                    'created_at' => $material->created_at
                ];
            });

        return $this->respondWithSuccess([
            'learning_materials' => $materials
        ], 'Learning materials retrieved successfully');
    }

    /**
     * Get student's exam results
     */
    public function examResults()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->student) {
            return $this->respondWithError('Student profile not found', 404);
        }

        $examResults = ExamResult::where('student_id', $user->student->id)
            ->with(['exam'])
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'exam_title' => $result->exam ? $result->exam->title : 'Unknown',
                    'score' => $result->score,
                    'grade' => $result->grade,
                    'percentage' => $result->percentage,
                    'created_at' => $result->created_at
                ];
            });

        return $this->respondWithSuccess([
            'exam_results' => $examResults
        ], 'Exam results retrieved successfully');
    }
}