<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Student;
use App\Models\Grading\Grade;
use App\Models\Attendance\Attendance;
use App\Models\Monetization\Transaction;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\LearningMaterial;
use App\Models\OnlineExam\ExamResult;
use Hypervel\Http\Request;

class ParentController extends BaseMobileController
{
    /**
     * Get parent profile information
     */
    public function profile()
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        $parent = $user->parent;

        return $this->respondWithSuccess([
            'parent' => [
                'id' => $parent->id,
                'user_id' => $parent->user_id,
                'full_name' => $parent->full_name,
                'phone' => $parent->phone,
                'email' => $parent->email,
                'address' => $parent->address,
                'occupation' => $parent->occupation,
                'relationship' => $parent->relationship,
                'created_at' => $parent->created_at,
                'updated_at' => $parent->updated_at
            ]
        ], 'Parent profile retrieved successfully');
    }

    /**
     * Get student information for parent
     */
    public function studentInfo($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // If no specific student ID provided, get the first student associated with this parent
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        return $this->respondWithSuccess([
            'student' => [
                'id' => $student->id,
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
                'class_name' => $student->class ? $student->class->class_name : 'Unknown',
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at
            ]
        ], 'Student information retrieved successfully');
    }

    /**
     * Get student's grades
     */
    public function studentGrades($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $grades = Grade::where('student_id', $student->id)
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
            'grades' => $grades,
            'student_name' => $student->full_name
        ], 'Student grades retrieved successfully');
    }

    /**
     * Get student's attendance
     */
    public function studentAttendance($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $attendances = Attendance::where('student_id', $student->id)
            ->with(['schedule'])
            ->latest()
            ->limit(50) // Limit to last 50 records
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
            'attendance' => $attendances,
            'student_name' => $student->full_name
        ], 'Student attendance records retrieved successfully');
    }

    /**
     * Get student's assignments
     */
    public function studentAssignments($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $assignments = Assignment::where('class_id', $student->class_id)
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
            'assignments' => $assignments,
            'student_name' => $student->full_name
        ], 'Student assignments retrieved successfully');
    }

    /**
     * Get student's learning materials
     */
    public function studentLearningMaterials($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $materials = LearningMaterial::where('class_id', $student->class_id)
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
            'learning_materials' => $materials,
            'student_name' => $student->full_name
        ], 'Student learning materials retrieved successfully');
    }

    /**
     * Get student's exam results
     */
    public function studentExamResults($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $examResults = ExamResult::where('student_id', $student->id)
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
            'exam_results' => $examResults,
            'student_name' => $student->full_name
        ], 'Student exam results retrieved successfully');
    }

    /**
     * Get student's fee/transaction history
     */
    public function studentFees($studentId = null)
    {
        $user = $this->getUserFromToken();
        
        if (!$user || !$user->parent) {
            return $this->respondWithError('Parent profile not found', 404);
        }

        // Get the student
        if (!$studentId) {
            $student = Student::where('parent_id', $user->parent->id)->first();
            if (!$student) {
                return $this->respondWithError('No student found for this parent', 404);
            }
        } else {
            $student = Student::find($studentId);
            if (!$student || $student->parent_id !== $user->parent->id) {
                return $this->respondWithError('Student not found or not associated with this parent', 404);
            }
        }

        $transactions = Transaction::where('student_id', $student->id)
            ->with(['transactionItems'])
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'total_amount' => $transaction->total_amount,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'payment_date' => $transaction->payment_date,
                    'created_at' => $transaction->created_at
                ];
            });

        return $this->respondWithSuccess([
            'transactions' => $transactions,
            'student_name' => $student->full_name
        ], 'Student fee/transaction history retrieved successfully');
    }
}