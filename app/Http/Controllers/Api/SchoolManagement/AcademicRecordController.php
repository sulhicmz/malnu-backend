<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AcademicRecordController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Get comprehensive academic record for a student
     */
    public function getAcademicRecord(string $studentId)
    {
        try {
            $student = Student::with([
                'grades' => function($query) {
                    $query->with(['subject', 'class']);
                },
                'reports' => function($query) {
                    $query->with(['class']);
                },
                'class'
            ])->find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            // Calculate cumulative GPA
            $cumulativeGPA = $this->calculateCumulativeGPA($student->grades);

            $academicRecord = [
                'student' => $student,
                'cumulative_gpa' => $cumulativeGPA,
                'grades' => $student->grades,
                'reports' => $student->reports,
                'academic_history' => $this->buildAcademicHistory($student)
            ];

            return $this->successResponse($academicRecord, 'Academic record retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Calculate cumulative GPA for a student
     */
    private function calculateCumulativeGPA($grades)
    {
        if ($grades->isEmpty()) {
            return 0.00;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $gradeValue = $this->convertGradeToPoints($grade->grade);
            $credits = $grade->subject->credit_hours ?? 1; // Default to 1 credit if not specified
            
            $totalGradePoints += $gradeValue * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.00;
    }

    /**
     * Convert numeric grade to grade points
     */
    private function convertGradeToPoints($grade)
    {
        // Standard 4.0 GPA scale
        if ($grade >= 97) return 4.0;
        if ($grade >= 93) return 3.7;
        if ($grade >= 90) return 3.3;
        if ($grade >= 87) return 3.0;
        if ($grade >= 83) return 2.7;
        if ($grade >= 80) return 2.3;
        if ($grade >= 77) return 2.0;
        if ($grade >= 73) return 1.7;
        if ($grade >= 70) return 1.3;
        if ($grade >= 67) return 1.0;
        if ($grade >= 65) return 0.7;
        return 0.0;
    }

    /**
     * Build academic history by semester
     */
    private function buildAcademicHistory($student)
    {
        $history = [];
        $grades = $student->grades->groupBy('semester');

        foreach ($grades as $semester => $semesterGrades) {
            $semesterGPA = $this->calculateSemesterGPA($semesterGrades);
            
            $history[] = [
                'semester' => $semester,
                'gpa' => $semesterGPA,
                'subjects' => $semesterGrades->map(function($grade) {
                    return [
                        'subject' => $grade->subject,
                        'grade' => $grade->grade,
                        'grade_type' => $grade->grade_type,
                        'notes' => $grade->notes
                    ];
                })
            ];
        }

        return $history;
    }

    /**
     * Calculate GPA for a specific semester
     */
    private function calculateSemesterGPA($semesterGrades)
    {
        if ($semesterGrades->isEmpty()) {
            return 0.00;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($semesterGrades as $grade) {
            $gradeValue = $this->convertGradeToPoints($grade->grade);
            $credits = $grade->subject->credit_hours ?? 1;
            
            $totalGradePoints += $gradeValue * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.00;
    }

    /**
     * Generate transcript for a student
     */
    public function generateTranscript(string $studentId)
    {
        try {
            $student = Student::with([
                'grades' => function($query) {
                    $query->with(['subject', 'class']);
                },
                'reports',
                'class'
            ])->find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $cumulativeGPA = $this->calculateCumulativeGPA($student->grades);
            $academicHistory = $this->buildAcademicHistory($student);

            $transcript = [
                'student_info' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class ? $student->class->name : null,
                    'enrollment_date' => $student->enrollment_date,
                    'status' => $student->status
                ],
                'cumulative_gpa' => $cumulativeGPA,
                'academic_history' => $academicHistory,
                'total_credits' => $this->calculateTotalCredits($student->grades),
                'class_rankings' => $this->getClassRankings($student)
            ];

            return $this->successResponse($transcript, 'Transcript generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Calculate total credits earned
     */
    private function calculateTotalCredits($grades)
    {
        $totalCredits = 0;
        foreach ($grades as $grade) {
            $totalCredits += $grade->subject->credit_hours ?? 1;
        }
        return $totalCredits;
    }

    /**
     * Get class rankings for the student
     */
    private function getClassRankings($student)
    {
        if (!$student->class_id) {
            return null;
        }

        // Get all students in the same class
        $classStudents = Student::where('class_id', $student->class_id)
            ->with(['grades'])
            ->get();

        $rankings = [];
        foreach ($classStudents as $classStudent) {
            $gpa = $this->calculateCumulativeGPA($classStudent->grades);
            $rankings[] = [
                'student_id' => $classStudent->id,
                'student_name' => $classStudent->name,
                'gpa' => $gpa
            ];
        }

        // Sort by GPA descending
        usort($rankings, function($a, $b) {
            return $b['gpa'] <=> $a['gpa'];
        });

        // Add rank numbers
        $rankedStudents = [];
        foreach ($rankings as $index => $ranking) {
            $rankedStudents[] = [
                'student_id' => $ranking['student_id'],
                'student_name' => $ranking['student_name'],
                'gpa' => $ranking['gpa'],
                'rank' => $index + 1
            ];
        }

        return $rankedStudents;
    }

    /**
     * Get all students' academic records for a class
     */
    public function getClassAcademicRecords(string $classId)
    {
        try {
            $students = Student::with([
                'grades' => function($query) {
                    $query->with(['subject']);
                },
                'reports'
            ])
            ->where('class_id', $classId)
            ->get();

            $classRecords = [];
            foreach ($students as $student) {
                $cumulativeGPA = $this->calculateCumulativeGPA($student->grades);
                
                $classRecords[] = [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'nisn' => $student->nisn,
                        'status' => $student->status
                    ],
                    'cumulative_gpa' => $cumulativeGPA,
                    'total_subjects' => $student->grades->count(),
                    'latest_report' => $student->reports->sortByDesc('semester')->first()
                ];
            }

            // Sort by GPA descending
            usort($classRecords, function($a, $b) {
                return $b['cumulative_gpa'] <=> $a['cumulative_gpa'];
            });

            return $this->successResponse($classRecords, 'Class academic records retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update grade for a student
     */
    public function updateGrade(string $gradeId)
    {
        try {
            $grade = Grade::with(['student', 'subject'])->find($gradeId);

            if (!$grade) {
                return $this->notFoundResponse('Grade record not found');
            }

            $data = $this->request->all();

            // Validate the grade value
            if (isset($data['grade']) && ($data['grade'] < 0 || $data['grade'] > 100)) {
                return $this->validationErrorResponse([
                    'grade' => ['Grade must be between 0 and 100']
                ]);
            }

            $grade->update($data);

            return $this->successResponse($grade, 'Grade updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GRADE_UPDATE_ERROR', null, 400);
        }
    }
}