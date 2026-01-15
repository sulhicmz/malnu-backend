<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Student;

class GPACalculationService
{
    private array $gradeScale;

    public function __construct()
    {
        $this->gradeScale = $this->getDefaultGradeScale();
    }

    public function setGradeScale(array $scale): void
    {
        $this->gradeScale = $scale;
    }

    public function calculateStudentGPA(string $studentId, ?string $academicYear = null, ?int $semester = null): float
    {
        $query = Grade::where('student_id', $studentId)
            ->with(['subject', 'class']);

        if ($academicYear) {
            $query->whereHas('class', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $numericGrade = $this->convertToNumeric($grade->grade);
            $creditHours = $grade->subject->credit_hours ?? 1;

            $totalGradePoints += $numericGrade * $creditHours;
            $totalCredits += $creditHours;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.0;
    }

    public function calculateCumulativeGPA(string $studentId): float
    {
        return $this->calculateStudentGPA($studentId);
    }

    public function getSemesterGPA(string $studentId, int $semester, string $academicYear): float
    {
        return $this->calculateStudentGPA($studentId, $academicYear, $semester);
    }

    public function getSubjectGPA(string $studentId, string $subjectId, ?string $academicYear = null): float
    {
        $query = Grade::where('student_id', $studentId)
            ->where('subject_id', $subjectId);

        if ($academicYear) {
            $query->whereHas('class', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $total = $grades->sum(function ($grade) {
            return $this->convertToNumeric($grade->grade);
        });

        return round($total / $grades->count(), 2);
    }

    public function convertToNumeric(float $grade): float
    {
        if ($grade >= 90) {
            return 4.0;
        }
        if ($grade >= 85) {
            return 3.7;
        }
        if ($grade >= 80) {
            return 3.3;
        }
        if ($grade >= 75) {
            return 3.0;
        }
        if ($grade >= 70) {
            return 2.7;
        }
        if ($grade >= 65) {
            return 2.3;
        }
        if ($grade >= 60) {
            return 2.0;
        }
        if ($grade >= 55) {
            return 1.7;
        }
        if ($grade >= 50) {
            return 1.3;
        }
        if ($grade >= 45) {
            return 1.0;
        }

        return 0.0;
    }

    public function convertLetterToNumeric(string $letterGrade): float
    {
        return $this->gradeScale[strtoupper($letterGrade)] ?? 0.0;
    }

    public function convertNumericToLetter(float $numericGPA): string
    {
        if ($numericGPA >= 4.0) {
            return 'A';
        }
        if ($numericGPA >= 3.7) {
            return 'A-';
        }
        if ($numericGPA >= 3.3) {
            return 'B+';
        }
        if ($numericGPA >= 3.0) {
            return 'B';
        }
        if ($numericGPA >= 2.7) {
            return 'B-';
        }
        if ($numericGPA >= 2.3) {
            return 'C+';
        }
        if ($numericGPA >= 2.0) {
            return 'C';
        }
        if ($numericGPA >= 1.7) {
            return 'C-';
        }
        if ($numericGPA >= 1.3) {
            return 'D+';
        }
        if ($numericGPA >= 1.0) {
            return 'D';
        }

        return 'F';
    }

    public function getClassRank(string $studentId, string $classId, ?int $semester = null, ?string $academicYear = null): ?int
    {
        $query = Grade::query()
            ->select('student_id')
            ->with('student')
            ->where('class_id', $classId)
            ->groupBy('student_id');

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($academicYear) {
            $query->whereHas('class', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        $studentGPAs = [];

        foreach ($query->get() as $studentGrade) {
            $gpa = $this->calculateStudentGPA(
                $studentGrade->student_id,
                $academicYear,
                $semester
            );
            $studentGPAs[$studentGrade->student_id] = $gpa;
        }

        arsort($studentGPAs);
        $rank = array_search($studentId, array_keys($studentGPAs));

        return $rank !== false ? $rank + 1 : null;
    }

    public function getAcademicPerformanceSummary(string $studentId): array
    {
        $student = Student::find($studentId);

        if (! $student) {
            return [];
        }

        $grades = Grade::where('student_id', $studentId)
            ->with(['subject', 'class'])
            ->get();

        $summary = [
            'student_id' => $studentId,
            'student_name' => $student->user->name ?? 'Unknown',
            'class_name' => $student->class->name ?? 'Not Assigned',
            'cumulative_gpa' => $this->calculateCumulativeGPA($studentId),
            'total_credits' => 0,
            'subjects_taken' => 0,
            'semesters' => [],
            'subject_performance' => [],
        ];

        $semesterData = [];

        foreach ($grades as $grade) {
            $creditHours = $grade->subject->credit_hours ?? 1;
            $summary['total_credits'] += $creditHours;
            ++$summary['subjects_taken'];

            $semesterKey = $grade->semester . '-' . ($grade->class->academic_year ?? 'N/A');

            if (! isset($semesterData[$semesterKey])) {
                $semesterData[$semesterKey] = [
                    'semester' => $grade->semester,
                    'academic_year' => $grade->class->academic_year ?? 'N/A',
                    'gpa' => 0.0,
                    'credits' => 0,
                    'subjects_count' => 0,
                ];
            }

            $semesterData[$semesterKey]['gpa'] += $this->convertToNumeric($grade->grade) * $creditHours;
            $semesterData[$semesterKey]['credits'] += $creditHours;
            ++$semesterData[$semesterKey]['subjects_count'];

            $subjectName = $grade->subject->name ?? 'Unknown';

            if (! isset($summary['subject_performance'][$subjectName])) {
                $summary['subject_performance'][$subjectName] = [
                    'subject_name' => $subjectName,
                    'grades' => [],
                    'average_grade' => 0.0,
                    'grade_count' => 0,
                ];
            }

            $summary['subject_performance'][$subjectName]['grades'][] = [
                'grade' => $grade->grade,
                'grade_type' => $grade->grade_type,
                'semester' => $grade->semester,
                'academic_year' => $grade->class->academic_year ?? 'N/A',
            ];

            ++$summary['subject_performance'][$subjectName]['grade_count'];
            $summary['subject_performance'][$subjectName]['average_grade'] += $grade->grade;
        }

        foreach ($semesterData as $data) {
            if ($data['credits'] > 0) {
                $data['gpa'] = round($data['gpa'] / $data['credits'], 2);
            }
            $summary['semesters'][] = $data;
        }

        foreach ($summary['subject_performance'] as $subject => $data) {
            if ($data['grade_count'] > 0) {
                $summary['subject_performance'][$subject]['average_grade'] = round(
                    $data['average_grade'] / $data['grade_count'],
                    2
                );
            }
        }

        $summary['subject_performance'] = array_values($summary['subject_performance']);

        return $summary;
    }

    private function getDefaultGradeScale(): array
    {
        return [
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'F' => 0.0,
        ];
    }
}
