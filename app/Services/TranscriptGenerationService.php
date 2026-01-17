<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\StudentPortfolio;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\System\SystemSetting;
use Exception;

class TranscriptGenerationService
{
    private GPACalculationService $gpaService;

    public function __construct(GPACalculationService $gpaService)
    {
        $this->gpaService = $gpaService;
    }

    public function generateTranscript(string $studentId, ?string $academicYear = null): array
    {
        $student = Student::find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $grades = $this->getGrades($studentId, $academicYear);

        if ($grades->isEmpty()) {
            throw new Exception('No grades found for this student');
        }

        $transcript = [
            'transcript_info' => $this->getTranscriptInfo($student),
            'student_info' => $this->getStudentInfo($student),
            'academic_summary' => $this->getAcademicSummary($studentId, $academicYear),
            'grades_by_semester' => $this->groupGradesBySemester($grades),
            'cumulative_statistics' => $this->getCumulativeStatistics($studentId),
            'competencies' => $this->getStudentCompetencies($studentId, $academicYear),
            'awards_and_achievements' => $this->getStudentAchievements($studentId),
            'signatures' => $this->getSignatures(),
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        $this->saveTranscriptRecord($studentId, $transcript);

        return $transcript;
    }

    private function getGrades(string $studentId, ?string $academicYear = null)
    {
        $query = Grade::where('student_id', $studentId)
            ->with(['subject', 'class'])
            ->orderBy('semester')
            ->orderBy('subject_id');

        if ($academicYear) {
            $query->whereHas('class', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        return $query->get();
    }

    private function getTranscriptInfo(Student $student): array
    {
        $report = Report::where('student_id', $student->id)
            ->where('is_published', true)
            ->latest()
            ->first();

        return [
            'transcript_number' => 'TRX-' . date('Ymd') . '-' . substr($student->id, -6),
            'academic_year' => $report->academic_year ?? '2024/2025',
            'issue_date' => date('Y-m-d'),
            'school_name' => 'SMK Negeri 1 Malang',
            'school_address' => 'Jl. Ki Ageng Gribig No.28, Malang',
            'school_phone' => '(0341) 719424',
        ];
    }

    private function getStudentInfo(Student $student): array
    {
        return [
            'student_id' => $student->id,
            'nisn' => $student->nisn,
            'name' => $student->user->name ?? 'Unknown',
            'birth_place' => $student->birth_place ?? '-',
            'birth_date' => $student->birth_date ? $student->birth_date->format('d F Y') : '-',
            'class' => $student->class->name ?? 'Not Assigned',
            'enrollment_date' => $student->enrollment_date ? $student->enrollment_date->format('F Y') : '-',
            'status' => $student->status,
            'address' => $student->address ?? '-',
        ];
    }

    private function getAcademicSummary(string $studentId, ?string $academicYear = null): array
    {
        $cumulativeGPA = $this->gpaService->calculateCumulativeGPA($studentId);
        $totalCredits = Grade::where('student_id', $studentId)
            ->with('subject')
            ->get()
            ->sum(function ($grade) {
                return $grade->subject->credit_hours ?? 1;
            });

        $subjectsCount = Grade::where('student_id', $studentId)
            ->distinct('subject_id')
            ->count();

        return [
            'cumulative_gpa' => $cumulativeGPA,
            'total_credits_earned' => $totalCredits,
            'total_subjects_completed' => $subjectsCount,
            'gpa_scale' => '4.0',
            'academic_standing' => $this->getAcademicStanding($cumulativeGPA),
        ];
    }

    private function groupGradesBySemester($grades): array
    {
        $grouped = [];

        foreach ($grades as $grade) {
            $semester = $grade->semester;
            $academicYear = $grade->class->academic_year ?? 'N/A';
            $key = "Semester {$semester} - {$academicYear}";

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'semester' => $semester,
                    'academic_year' => $academicYear,
                    'semester_gpa' => 0.0,
                    'credits' => 0,
                    'subjects' => [],
                ];
            }

            $creditHours = $grade->subject->credit_hours ?? 1;
            $numericGrade = $this->gpaService->convertToNumeric($grade->grade);

            $grouped[$key]['semester_gpa'] += $numericGrade * $creditHours;
            $grouped[$key]['credits'] += $creditHours;
            $grouped[$key]['subjects'][] = [
                'subject_code' => $grade->subject->code ?? '-',
                'subject_name' => $grade->subject->name ?? 'Unknown',
                'credit_hours' => $creditHours,
                'grade' => $grade->grade,
                'grade_point' => $numericGrade,
                'grade_type' => $grade->grade_type,
                'remarks' => $this->getGradeRemarks($grade->grade),
            ];
        }

        foreach ($grouped as $key => $semester) {
            if ($semester['credits'] > 0) {
                $grouped[$key]['semester_gpa'] = round($semester['semester_gpa'] / $semester['credits'], 2);
            }
        }

        return array_values($grouped);
    }

    private function getCumulativeStatistics(string $studentId): array
    {
        $grades = Grade::where('student_id', $studentId)->get();

        $passedCount = $grades->filter(function ($grade) {
            return $this->gpaService->convertToNumeric($grade->grade) >= 2.0;
        })->count();

        $failedCount = $grades->count() - $passedCount;

        return [
            'total_grades' => $grades->count(),
            'grades_above_90' => $grades->where('grade', '>=', 90)->count(),
            'grades_80_to_89' => $grades->whereBetween('grade', [80, 89])->count(),
            'grades_70_to_79' => $grades->whereBetween('grade', [70, 79])->count(),
            'grades_60_to_69' => $grades->whereBetween('grade', [60, 69])->count(),
            'grades_below_60' => $grades->where('grade', '<', 60)->count(),
            'passed_subjects' => $passedCount,
            'failed_subjects' => $failedCount,
            'pass_rate' => $grades->count() > 0 ? round(($passedCount / $grades->count()) * 100, 2) : 0,
        ];
    }

    private function getStudentCompetencies(string $studentId, ?string $academicYear = null): array
    {
        $query = Competency::where('student_id', $studentId)
            ->with('subject');

        if ($academicYear) {
            $query->whereHas('subject', function ($q) use ($academicYear) {
                $q->whereHas('classSubjects', function ($q) use ($academicYear) {
                    $q->whereHas('class', function ($q) use ($academicYear) {
                        $q->where('academic_year', $academicYear);
                    });
                });
            });
        }

        $competencies = $query->get();

        return $competencies->map(function ($competency) {
            return [
                'subject' => $competency->subject->name ?? 'Unknown',
                'competency_code' => $competency->competency_code,
                'competency_name' => $competency->competency_name,
                'achievement_level' => $competency->achievement_level,
                'semester' => $competency->semester,
                'notes' => $competency->notes,
            ];
        })->toArray();
    }

    private function getStudentAchievements(string $studentId): array
    {
        $portfolios = StudentPortfolio::where('student_id', $studentId)
            ->where('is_public', true)
            ->orderBy('date_added', 'desc')
            ->get();

        return $portfolios->map(function ($portfolio) {
            return [
                'title' => $portfolio->title,
                'description' => $portfolio->description,
                'type' => $portfolio->portfolio_type,
                'date' => $portfolio->date_added ? $portfolio->date_added->format('F Y') : '-',
                'file_url' => $portfolio->file_url,
            ];
        })->toArray();
    }

    private function getSignatures(): array
    {
        return [
            'homeroom_teacher' => [
                'name' => SystemSetting::getValue('homeroom_teacher_name') ?? 'Guru Wali Kelas',
                'nip' => SystemSetting::getValue('homeroom_teacher_nip') ?? 'NIP. -',
                'title' => 'Guru Wali Kelas',
            ],
            'principal' => [
                'name' => SystemSetting::getValue('principal_name') ?? 'Kepala Sekolah',
                'nip' => SystemSetting::getValue('principal_nip') ?? 'NIP. -',
                'title' => 'Kepala Sekolah',
            ],
        ];
    }

    private function getGradeRemarks(float $grade): string
    {
        if ($grade >= 90) return 'Excellent';
        if ($grade >= 80) return 'Very Good';
        if ($grade >= 70) return 'Good';
        if ($grade >= 60) return 'Satisfactory';
        if ($grade >= 50) return 'Fair';

        return 'Needs Improvement';
    }

    private function getAcademicStanding(float $gpa): string
    {
        if ($gpa >= 3.5) return 'High Distinction';
        if ($gpa >= 3.0) return 'Distinction';
        if ($gpa >= 2.5) return 'Credit';
        if ($gpa >= 2.0) return 'Pass';

        return 'Probation';
    }

    private function saveTranscriptRecord(string $studentId, array $transcriptData): void
    {
        $academicYear = $transcriptData['grades_by_semester'][0]['academic_year'] ?? '2024/2025';
        $semester = $transcriptData['grades_by_semester'][0]['semester'] ?? 1;
        $gpa = $transcriptData['academic_summary']['cumulative_gpa'];

        Report::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year' => $academicYear,
                'semester' => $semester,
            ],
            [
                'average_grade' => $gpa * 25,
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }

    public function generateReportCard(string $studentId, int $semester, string $academicYear): array
    {
        $student = Student::find($studentId);

        if (!$student) {
            throw new Exception('Student not found');
        }

        $grades = Grade::where('student_id', $studentId)
            ->where('semester', $semester)
            ->whereHas('class', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            })
            ->with(['subject', 'class'])
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception('No grades found for the specified semester and academic year');
        }

        $reportCard = [
            'report_card_info' => [
                'report_number' => 'RPT-' . date('Ymd') . '-' . substr($studentId, -6),
                'semester' => $semester,
                'academic_year' => $academicYear,
                'issue_date' => date('Y-m-d'),
            ],
            'student_info' => $this->getStudentInfo($student),
            'grades' => $this->formatGrades($grades),
            'semester_summary' => [
                'semester_gpa' => $this->gpaService->getSemesterGPA($studentId, $semester, $academicYear),
                'total_credits' => $grades->sum(function ($grade) {
                    return $grade->subject->credit_hours ?? 1;
                }),
                'total_subjects' => $grades->count(),
            ],
            'remarks' => $this->getReportCardRemarks($studentId, $semester, $academicYear),
            'signatures' => $this->getSignatures(),
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        return $reportCard;
    }

    private function formatGrades($grades): array
    {
        return $grades->map(function ($grade) {
            return [
                'subject_code' => $grade->subject->code ?? '-',
                'subject_name' => $grade->subject->name ?? 'Unknown',
                'credit_hours' => $grade->subject->credit_hours ?? 1,
                'grade' => $grade->grade,
                'grade_point' => $this->gpaService->convertToNumeric($grade->grade),
                'remarks' => $this->getGradeRemarks($grade->grade),
            ];
        })->toArray();
    }

    private function getReportCardRemarks(string $studentId, int $semester, string $academicYear): array
    {
        $report = Report::where('student_id', $studentId)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->first();

        return [
            'homeroom_notes' => $report->homeroom_notes ?? 'Keep up the good work!',
            'principal_notes' => $report->principal_notes ?? null,
        ];
    }
}