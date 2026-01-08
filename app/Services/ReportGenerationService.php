<?php

declare (strict_types = 1);

namespace App\Services;

use App\Models\Grading\Competency;
use App\Models\Grading\GeneratedReport;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\ReportSignature;
use App\Models\Grading\ReportTemplate;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;

class ReportGenerationService
{
    private PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generateReportCard(string $studentId, ?string $semester = null, ?string $academicYear = null): array
    {
        $student = Student::with(['user', 'class', 'grades.subject'])->find($studentId);
        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::with('subject')
            ->where('student_id', $studentId)
            ->when($semester, fn($q) => $q->where('semester', $semester))
            ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
            ->get();

        $competencies = Competency::where('student_id', $studentId)
            ->when($semester, fn($q) => $q->where('semester', $semester))
            ->get();

        $averageGrade = $grades->avg('grade');
        $rank = $this->calculateClassRank($studentId, $student->class_id, $semester, $academicYear);

        $reportData = [
            'student_name' => $student->name,
            'student_nisn' => $student->nisn,
            'class_name' => $student->class->name ?? 'N/A',
            'semester' => $semester ?? 'All',
            'academic_year' => $academicYear ?? date('Y'),
            'average_grade' => number_format($averageGrade, 2),
            'rank_in_class' => $rank,
            'grades' => $grades->map(fn($g) => [
                'subject' => $g->subject->name ?? 'N/A',
                'grade' => number_format($g->grade, 2),
                'grade_type' => $g->grade_type,
                'notes' => $g->notes ?? '',
            ])->toArray(),
            'competencies' => $competencies->map(fn($c) => [
                'competency' => $c->name ?? 'N/A',
                'level' => $c->level ?? 'N/A',
                'remarks' => $c->remarks ?? '',
            ])->toArray(),
            'generated_date' => date('F j, Y'),
        ];

        $template = $this->getTemplate('report_card', $student->class_id);
        $html = $this->pdfService->generateReportHtml($reportData, $template['html_template']);

        $filename = $this->generateFilename($student, 'report_card', $semester);
        $filepath = $this->pdfService->getFilePath($filename);

        $this->pdfService->generateFromHtml($html, $filepath);

        $report = GeneratedReport::create([
            'student_id' => $studentId,
            'report_type' => 'report_card',
            'semester' => $semester,
            'academic_year' => $academicYear,
            'template_id' => $template['id'],
            'file_path' => $this->pdfService->getPublicPath($filename),
            'file_format' => 'pdf',
            'file_size' => $this->pdfService->getFileSize($filepath . '.html'),
            'status' => 'generated',
            'generation_data' => $reportData,
            'created_by' => $this->getCurrentUserId(),
        ]);

        return [
            'report' => $report,
            'download_url' => $report->file_path,
            'data' => $reportData,
        ];
    }

    public function generateTranscript(string $studentId, ?string $academicYear = null): array
    {
        $student = Student::with(['user', 'class', 'grades.subject'])->find($studentId);
        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::with('subject')
            ->where('student_id', $studentId)
            ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->get();

        $gradesByYearSemester = $grades->groupBy(['academic_year', 'semester']);

        $cumulativeGpa = $this->calculateCumulativeGpa($grades);

        $transcriptData = [
            'student_name' => $student->name,
            'student_nisn' => $student->nisn,
            'enrollment_date' => $student->enrollment_date->format('F Y'),
            'cumulative_gpa' => number_format($cumulativeGpa, 2),
            'total_credits' => $grades->count(),
            'academic_records' => $gradesByYearSemester->map(function ($years) {
                return [
                    'academic_year' => $years->first()->academic_year,
                    'semesters' => $years->map(function ($semesters) {
                        return [
                            'semester' => $semesters->first()->semester,
                            'subjects' => $semesters->map(fn($g) => [
                                'subject' => $g->subject->name ?? 'N/A',
                                'grade' => number_format($g->grade, 2),
                                'credits' => 1,
                            ])->toArray(),
                            'gpa' => number_format($semesters->avg('grade'), 2),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'generated_date' => date('F j, Y'),
        ];

        $template = $this->getTemplate('transcript', null);
        $html = $this->pdfService->generateReportHtml($transcriptData, $template['html_template']);

        $filename = $this->generateFilename($student, 'transcript', null);
        $filepath = $this->pdfService->getFilePath($filename);

        $this->pdfService->generateFromHtml($html, $filepath);

        $report = GeneratedReport::create([
            'student_id' => $studentId,
            'report_type' => 'transcript',
            'academic_year' => $academicYear,
            'template_id' => $template['id'],
            'file_path' => $this->pdfService->getPublicPath($filename),
            'file_format' => 'pdf',
            'file_size' => $this->pdfService->getFileSize($filepath . '.html'),
            'status' => 'generated',
            'generation_data' => $transcriptData,
            'created_by' => $this->getCurrentUserId(),
        ]);

        return [
            'report' => $report,
            'download_url' => $report->file_path,
            'data' => $transcriptData,
        ];
    }

    public function generateProgressReport(string $studentId, string $semester, string $academicYear): array
    {
        $student = Student::with(['user', 'class', 'grades.subject'])->find($studentId);
        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::with('subject')
            ->where('student_id', $studentId)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->get();

        $competencies = Competency::where('student_id', $studentId)
            ->where('semester', $semester)
            ->get();

        $previousSemesterGrades = $this->getPreviousSemesterGrades($studentId, $semester, $academicYear);

        $progressData = [
            'student_name' => $student->name,
            'class_name' => $student->class->name ?? 'N/A',
            'semester' => $semester,
            'academic_year' => $academicYear,
            'current_average' => number_format($grades->avg('grade'), 2),
            'previous_average' => number_format($previousSemesterGrades->avg('grade') ?? 0, 2),
            'improvement' => number_format($grades->avg('grade') - ($previousSemesterGrades->avg('grade') ?? 0), 2),
            'subjects' => $grades->map(fn($g) => [
                'subject' => $g->subject->name ?? 'N/A',
                'current_grade' => number_format($g->grade, 2),
                'previous_grade' => $this->getPreviousGrade($previousSemesterGrades, $g->subject_id),
                'trend' => $this->calculateTrend($g->grade, $this->getPreviousGrade($previousSemesterGrades, $g->subject_id)),
            ])->toArray(),
            'competencies' => $competencies->map(fn($c) => [
                'competency' => $c->name ?? 'N/A',
                'level' => $c->level ?? 'N/A',
                'remarks' => $c->remarks ?? '',
            ])->toArray(),
            'generated_date' => date('F j, Y'),
        ];

        $template = $this->getTemplate('progress_report', $student->class_id);
        $html = $this->pdfService->generateReportHtml($progressData, $template['html_template']);

        $filename = $this->generateFilename($student, 'progress_report', $semester);
        $filepath = $this->pdfService->getFilePath($filename);

        $this->pdfService->generateFromHtml($html, $filepath);

        $report = GeneratedReport::create([
            'student_id' => $studentId,
            'report_type' => 'progress_report',
            'semester' => $semester,
            'academic_year' => $academicYear,
            'template_id' => $template['id'],
            'file_path' => $this->pdfService->getPublicPath($filename),
            'file_format' => 'pdf',
            'file_size' => $this->pdfService->getFileSize($filepath . '.html'),
            'status' => 'generated',
            'generation_data' => $progressData,
            'created_by' => $this->getCurrentUserId(),
        ]);

        return [
            'report' => $report,
            'download_url' => $report->file_path,
            'data' => $progressData,
        ];
    }

    public function generateClassReports(string $classId, string $semester, string $academicYear): array
    {
        $class = ClassModel::find($classId);
        if (!$class) {
            throw new \Exception('Class not found');
        }

        $students = Student::where('class_id', $classId)->get();
        $reports = [];

        foreach ($students as $student) {
            try {
                $reports[] = $this->generateReportCard($student->id, $semester, $academicYear);
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'class' => $class->name,
            'total_students' => count($students),
            'generated_reports' => count($reports),
            'reports' => $reports,
        ];
    }

    private function getTemplate(string $type, ?string $classId): array
    {
        $template = ReportTemplate::active()
            ->byType($type)
            ->byGradeLevel($classId)
            ->first();

        if (!$template) {
            $template = ReportTemplate::active()
                ->byType($type)
                ->whereNull('grade_level')
                ->first();
        }

        if (!$template) {
            $template = ReportTemplate::create([
                'name' => ucfirst(str_replace('_', ' ', $type)),
                'type' => $type,
                'html_template' => $this->getDefaultTemplate($type),
                'variables' => json_encode(array_keys($this->getDefaultTemplateData($type))),
                'is_active' => true,
                'created_by' => $this->getCurrentUserId(),
            ]);
        }

        return $template->toArray();
    }

    private function getDefaultTemplate(string $type): string
    {
        $templates = [
            'report_card' => '<html><head><title>Report Card</title></head><body><h1>Report Card</h1><p>Student: {student_name}</p><p>NISN: {student_nisn}</p><p>Class: {class_name}</p><p>Semester: {semester}</p><p>Academic Year: {academic_year}</p><p>Average Grade: {average_grade}</p><p>Rank: {rank_in_class}</p><h2>Grades</h2><table>{grades}</table><h2>Competencies</h2><ul>{competencies}</ul><p>Generated: {generated_date}</p></body></html>',
            'transcript' => '<html><head><title>Academic Transcript</title></head><body><h1>Academic Transcript</h1><p>Student: {student_name}</p><p>NISN: {student_nisn}</p><p>Enrollment Date: {enrollment_date}</p><p>Cumulative GPA: {cumulative_gpa}</p><p>Total Credits: {total_credits}</p><h2>Academic Records</h2><div>{academic_records}</div><p>Generated: {generated_date}</p></body></html>',
            'progress_report' => '<html><head><title>Progress Report</title></head><body><h1>Progress Report</h1><p>Student: {student_name}</p><p>Class: {class_name}</p><p>Semester: {semester}</p><p>Academic Year: {academic_year}</p><p>Current Average: {current_average}</p><p>Previous Average: {previous_average}</p><p>Improvement: {improvement}</p><h2>Subjects</h2><table>{subjects}</table><h2>Competencies</h2><ul>{competencies}</ul><p>Generated: {generated_date}</p></body></html>',
        ];

        return $templates[$type] ?? '<html><body><h1>Report</h1></body></html>';
    }

    private function getDefaultTemplateData(string $type): array
    {
        return [
            'student_name' => 'John Doe',
            'student_nisn' => '12345',
            'class_name' => 'Grade 10A',
            'semester' => '1',
            'academic_year' => '2024',
        ];
    }

    private function calculateClassRank(string $studentId, string $classId, ?string $semester, ?string $academicYear): int
    {
        $grades = Grade::where('class_id', $classId)
            ->when($semester, fn($q) => $q->where('semester', $semester))
            ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
            ->selectRaw('student_id, AVG(grade) as avg_grade')
            ->groupBy('student_id')
            ->orderBy('avg_grade', 'desc')
            ->get();

        $rank = 1;
        foreach ($grades as $grade) {
            if ($grade->student_id === $studentId) {
                return $rank;
            }
            $rank++;
        }

        return $rank;
    }

    private function calculateCumulativeGpa($grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $total = 0;
        $count = 0;

        foreach ($grades as $grade) {
            $total += $grade->grade;
            $count++;
        }

        return $count > 0 ? $total / $count : 0.0;
    }

    private function getPreviousSemesterGrades(string $studentId, int $currentSemester, string $academicYear)
    {
        $previousSemester = $currentSemester - 1;
        if ($previousSemester < 1) {
            return collect();
        }

        return Grade::where('student_id', $studentId)
            ->where('semester', $previousSemester)
            ->where('academic_year', $academicYear)
            ->get();
    }

    private function getPreviousGrade($previousGrades, $subjectId): ?float
    {
        $previousGrade = $previousGrades->firstWhere('subject_id', $subjectId);
        return $previousGrade ? $previousGrade->grade : null;
    }

    private function calculateTrend(float $current, ?float $previous): string
    {
        if ($previous === null) {
            return 'No data';
        }

        $diff = $current - $previous;
        if ($diff > 0) {
            return 'Improving';
        } elseif ($diff < 0) {
            return 'Declining';
        }
        return 'Stable';
    }

    private function generateFilename(Student $student, string $reportType, ?string $semester): string
    {
        $semPart = $semester ? '_sem' . $semester : '';
        return $reportType . '_' . $student->nisn . '_' . date('Y-m-d') . $semPart;
    }

    private function getCurrentUserId(): ?string
    {
        $request = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
        $token = $request->getHeaderLine('Authorization');
        if ($token && strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
            $payload = \App\Services\JWTService::decode($token);
            return $payload['sub'] ?? null;
        }
        return null;
    }
}
