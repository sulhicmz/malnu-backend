<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Grading\Grade;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use Exception;

class PerformanceAnalyticsService
{
    public function getStudentPerformance(string $studentId, ?int $semester = null): array
    {
        $student = Student::with(['class'])->find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $query = Grade::where('student_id', $studentId);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return [
                'student' => $this->formatStudent($student),
                'has_data' => false,
                'message' => 'No grade data available'
            ];
        }

        $averageGrade = $grades->avg('grade');
        $highestGrade = $grades->max('grade');
        $lowestGrade = $grades->min('grade');

        $subjectPerformance = $grades->groupBy('subject_id')->map(function ($subjectGrades) {
            return [
                'subject_id' => $subjectGrades->first()->subject_id,
                'average' => round($subjectGrades->avg('grade'), 2),
                'highest' => $subjectGrades->max('grade'),
                'lowest' => $subjectGrades->min('grade'),
                'total_assessments' => $subjectGrades->count()
            ];
        })->values();

        return [
            'student' => $this->formatStudent($student),
            'has_data' => true,
            'performance' => [
                'overall_average' => round($averageGrade, 2),
                'highest_grade' => $highestGrade,
                'lowest_grade' => $lowestGrade,
                'total_assessments' => $grades->count()
            ],
            'subject_performance' => $subjectPerformance,
            'semester' => $semester ?? 'all'
        ];
    }

    public function getClassPerformance(string $classId, ?int $semester = null): array
    {
        $class = ClassModel::with(['students'])->find($classId);

        if (!$class) {
            throw new Exception('Class not found');
        }

        $studentIds = $class->students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return [
                'class' => [
                    'id' => $class->id,
                    'name' => $class->name
                ],
                'has_data' => false,
                'message' => 'No students in class'
            ];
        }

        $query = Grade::whereIn('student_id', $studentIds);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return [
                'class' => [
                    'id' => $class->id,
                    'name' => $class->name
                ],
                'has_data' => false,
                'message' => 'No grade data available'
            ];
        }

        $studentsPerformance = [];

        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if (!$student) continue;

            $studentGrades = $grades->where('student_id', $studentId);

            if ($studentGrades->isNotEmpty()) {
                $studentsPerformance[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'average_grade' => round($studentGrades->avg('grade'), 2),
                    'total_grades' => $studentGrades->count(),
                    'highest_grade' => $studentGrades->max('grade'),
                    'lowest_grade' => $studentGrades->min('grade')
                ];
            }
        }

        usort($studentsPerformance, function ($a, $b) {
            return $b['average_grade'] <=> $a['average_grade'];
        });

        return [
            'class' => [
                'id' => $class->id,
                'name' => $class->name
            ],
            'has_data' => true,
            'class_average' => round($grades->avg('grade'), 2),
            'total_students' => count($studentIds),
            'students_with_grades' => count($studentsPerformance),
            'top_performers' => array_slice($studentsPerformance, 0, 5),
            'needs_attention' => array_slice(array_reverse($studentsPerformance), 0, 5),
            'semester' => $semester ?? 'all'
        ];
    }

    public function getComparativeAnalysis(string $studentId, ?int $semester = null): array
    {
        $student = Student::with(['class'])->find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $classPerformance = $this->getClassPerformance($student->class_id, $semester);
        $studentPerformance = $this->getStudentPerformance($studentId, $semester);

        if (!$studentPerformance['has_data'] || !$classPerformance['has_data']) {
            return [
                'student' => $this->formatStudent($student),
                'has_data' => false,
                'message' => 'Insufficient data for comparison'
            ];
        }

        $studentAverage = $studentPerformance['performance']['overall_average'];
        $classAverage = $classPerformance['class_average'];
        $difference = round($studentAverage - $classAverage, 2);

        $percentile = 0;
        if (isset($classPerformance['top_performers'])) {
            $performersCount = count($classPerformance['top_performers']) +
                             count($classPerformance['needs_attention'] ?? []);
            $betterCount = 0;
            foreach ($classPerformance['top_performers'] ?? [] as $performer) {
                if ($performer['average_grade'] > $studentAverage) {
                    $betterCount++;
                }
            }
            if ($performersCount > 0) {
                $percentile = round((($performersCount - $betterCount) / $performersCount) * 100, 2);
            }
        }

        return [
            'student' => $this->formatStudent($student),
            'has_data' => true,
            'student_performance' => $studentPerformance['performance'],
            'class_comparison' => [
                'class_average' => $classAverage,
                'difference' => $difference,
                'above_class_average' => $difference > 0,
                'percentile_rank' => $percentile
            ],
            'recommendation' => $this->generateRecommendation($difference, $percentile)
        ];
    }

    private function formatStudent(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->name,
            'nisn' => $student->nisn,
            'class' => $student->class->name ?? 'N/A',
            'status' => $student->status
        ];
    }

    private function generateRecommendation(float $difference, float $percentile): string
    {
        if ($difference >= 10) {
            return 'Excellent performance! Student is significantly above class average.';
        } elseif ($difference >= 5) {
            return 'Good performance. Student is above class average.';
        } elseif ($difference >= 0) {
            return 'Satisfactory performance. Student is at or near class average.';
        } elseif ($difference >= -5) {
            return 'Student is slightly below class average. Additional support may be beneficial.';
        } else {
            return 'Student is below class average. Consider implementing additional tutoring or support programs.';
        }
    }
}
