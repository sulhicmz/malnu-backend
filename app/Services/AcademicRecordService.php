<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\SchoolManagement\Student;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;

class AcademicRecordService
{
    public function calculateGPA(string $studentId, ?int $semester = null): array
    {
        $student = Student::with(['grades', 'grades.subject'])->find($studentId);

        if (!$student) {
            throw new \Exception('Student not found');
        }

        $query = Grade::where('student_id', $studentId);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return [
                'gpa' => 0,
                'total_grades' => 0,
                'grades' => []
            ];
        }

        $totalGradePoints = 0;
        $gradeDetails = [];

        foreach ($grades as $grade) {
            $gradePoint = $this->convertToGradePoint($grade->grade);
            $totalGradePoints += $gradePoint;

            $gradeDetails[] = [
                'subject' => $grade->subject->name ?? 'N/A',
                'grade' => $grade->grade,
                'grade_point' => $gradePoint,
                'semester' => $grade->semester,
                'grade_type' => $grade->grade_type
            ];
        }

        $gpa = count($grades) > 0 ? round($totalGradePoints / count($grades), 2) : 0;

        return [
            'gpa' => $gpa,
            'total_grades' => count($grades),
            'total_grade_points' => round($totalGradePoints, 2),
            'grades' => $gradeDetails,
            'semester' => $semester ?? 'all'
        ];
    }

    public function generateTranscript(string $studentId): array
    {
        $student = Student::with([
            'class',
            'grades',
            'grades.subject',
            'reports',
            'portfolios'
        ])->find($studentId);

        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::where('student_id', $studentId)
            ->with('subject')
            ->orderBy('semester')
            ->orderBy('grade_type')
            ->get();

        $semesters = $grades->groupBy('semester')->sortKeys();
        $transcriptData = [];

        foreach ($semesters as $semester => $semesterGrades) {
            $totalGradePoints = 0;
            $subjectGrades = [];

            foreach ($semesterGrades as $grade) {
                $gradePoint = $this->convertToGradePoint($grade->grade);
                $totalGradePoints += $gradePoint;

                $subjectGrades[] = [
                    'subject' => $grade->subject->name ?? 'N/A',
                    'grade' => $grade->grade,
                    'grade_point' => $gradePoint,
                    'grade_type' => $grade->grade_type,
                    'notes' => $grade->notes
                ];
            }

            $semesterGpa = count($semesterGrades) > 0
                ? round($totalGradePoints / count($semesterGrades), 2)
                : 0;

            $transcriptData[] = [
                'semester' => $semester,
                'gpa' => $semesterGpa,
                'subjects' => $subjectGrades,
                'total_subjects' => count($subjectGrades)
            ];
        }

        $overallGpa = $this->calculateGPA($studentId)['gpa'];

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn,
                'class' => $student->class->name ?? 'N/A',
                'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
                'status' => $student->status
            ],
            'overall_gpa' => $overallGpa,
            'semesters' => $transcriptData,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];
    }

    private function convertToGradePoint(float $grade): float
    {
        if ($grade >= 90) {
            return 4.0;
        } elseif ($grade >= 85) {
            return 3.7;
        } elseif ($grade >= 80) {
            return 3.3;
        } elseif ($grade >= 75) {
            return 3.0;
        } elseif ($grade >= 70) {
            return 2.7;
        } elseif ($grade >= 65) {
            return 2.3;
        } elseif ($grade >= 60) {
            return 2.0;
        } elseif ($grade >= 55) {
            return 1.7;
        } elseif ($grade >= 50) {
            return 1.3;
        } else {
            return 1.0;
        }
    }

    public function getStudentProgress(string $studentId): array
    {
        $student = Student::with(['grades', 'grades.subject'])->find($studentId);

        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::where('student_id', $studentId)->get();

        if ($grades->isEmpty()) {
            return [
                'average_grade' => 0,
                'total_subjects' => 0,
                'progress_by_subject' => []
            ];
        }

        $totalGrade = 0;
        $subjectProgress = [];

        foreach ($grades as $grade) {
            $totalGrade += $grade->grade;
            $subjectName = $grade->subject->name ?? 'N/A';

            if (!isset($subjectProgress[$subjectName])) {
                $subjectProgress[$subjectName] = [
                    'total_grades' => 0,
                    'sum_grades' => 0,
                    'grade_types' => []
                ];
            }

            $subjectProgress[$subjectName]['total_grades']++;
            $subjectProgress[$subjectName]['sum_grades'] += $grade->grade;

            if (!isset($subjectProgress[$subjectName]['grade_types'][$grade->grade_type])) {
                $subjectProgress[$subjectName]['grade_types'][$grade->grade_type] = [];
            }
            $subjectProgress[$subjectName]['grade_types'][$grade->grade_type][] = $grade->grade;
        }

        foreach ($subjectProgress as $subject => $data) {
            $avg = $data['sum_grades'] / $data['total_grades'];
            $subjectProgress[$subject]['average'] = round($avg, 2);
            $subjectProgress[$subject]['grade_point'] = $this->convertToGradePoint($avg);

            foreach ($data['grade_types'] as $type => $grades) {
                $typeAvg = array_sum($grades) / count($grades);
                $subjectProgress[$subject]['grade_types'][$type] = [
                    'average' => round($typeAvg, 2),
                    'count' => count($grades)
                ];
            }
        }

        return [
            'average_grade' => round($totalGrade / count($grades), 2),
            'total_subjects' => count($subjectProgress),
            'total_grades' => count($grades),
            'progress_by_subject' => $subjectProgress
        ];
    }
}
