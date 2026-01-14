<?php

declare (strict_types = 1);

namespace App\Services;

use App\Models\Grading\Grade;
use App\Models\Grading\Competency;
use App\Models\Grading\Report;
use App\Models\Grading\ReportTemplate;
use App\Models\Grading\ReportSignature;
use App\Models\Grading\StudentPortfolio;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Psr\Log\LoggerInterface;

class ReportGenerationService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function generateReportCard(string $studentId, string $classId, int $semester, string $academicYear, ?string $templateId = null): array
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                throw new \Exception('Student not found');
            }

            $grades = Grade::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->where('semester', $semester)
                ->with(['subject', 'assignment', 'quiz', 'exam'])
                ->get();

            $competencies = Competency::where('student_id', $studentId)
                ->where('semester', $semester)
                ->with('subject')
                ->get();

            $averageGrade = $grades->avg('grade') ?? 0;

            $rankInClass = $this->calculateClassRank($studentId, $classId, $semester, $academicYear);

            $reportData = [
                'student' => $student->toArray(),
                'class' => ClassModel::find($classId)->toArray(),
                'grades' => $grades->toArray(),
                'competencies' => $competencies->toArray(),
                'average_grade' => round($averageGrade, 2),
                'rank_in_class' => $rankInClass,
                'semester' => $semester,
                'academic_year' => $academicYear,
            ];

            $report = Report::create([
                'student_id' => $studentId,
                'class_id' => $classId,
                'semester' => $semester,
                'academic_year' => $academicYear,
                'average_grade' => $averageGrade,
                'rank_in_class' => $rankInClass,
                'is_published' => false,
            ]);

            $htmlContent = $this->renderReportCardHtml($reportData, $templateId);
            $fileUrl = $this->generatePdfFromHtml($htmlContent, "report_card_{$studentId}_{$semester}");

            return [
                'report_id' => $report->id,
                'file_url' => $fileUrl,
                'data' => $reportData,
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate report card', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function generateTranscript(string $studentId): array
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                throw new \Exception('Student not found');
            }

            $allGrades = Grade::where('student_id', $studentId)
                ->with(['subject', 'class'])
                ->orderBy('semester')
                ->get()
                ->groupBy(['academic_year', 'semester']);

            $cumulativeGpa = $this->calculateCumulativeGpa($studentId);
            $totalCredits = $this->calculateTotalCredits($studentId);

            $transcriptData = [
                'student' => $student->toArray(),
                'academic_years' => $this->groupGradesByYear($allGrades),
                'cumulative_gpa' => $cumulativeGpa,
                'total_credits' => $totalCredits,
            ];

            $report = Report::create([
                'student_id' => $studentId,
                'semester' => 0,
                'academic_year' => 'transcript',
                'average_grade' => $cumulativeGpa,
                'is_published' => false,
            ]);

            $htmlContent = $this->renderTranscriptHtml($transcriptData);
            $fileUrl = $this->generatePdfFromHtml($htmlContent, "transcript_{$studentId}");

            return [
                'report_id' => $report->id,
                'file_url' => $fileUrl,
                'data' => $transcriptData,
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate transcript', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function generateProgressReport(string $studentId, string $classId, int $semester, string $academicYear): array
    {
        try {
            $student = Student::find($studentId);
            if (!$student) {
                throw new \Exception('Student not found');
            }

            $grades = Grade::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->where('semester', $semester)
                ->with('subject')
                ->get();

            $portfolios = StudentPortfolio::where('student_id', $studentId)
                ->whereDate('date_added', '>=', $academicYear . '-01-01')
                ->get();

            $reportData = [
                'student' => $student->toArray(),
                'class' => ClassModel::find($classId)->toArray(),
                'grades' => $grades->toArray(),
                'portfolios' => $portfolios->toArray(),
                'semester' => $semester,
                'academic_year' => $academicYear,
            ];

            $report = Report::create([
                'student_id' => $studentId,
                'class_id' => $classId,
                'semester' => $semester,
                'academic_year' => $academicYear,
                'average_grade' => $grades->avg('grade') ?? 0,
                'is_published' => false,
            ]);

            $htmlContent = $this->renderProgressReportHtml($reportData);
            $fileUrl = $this->generatePdfFromHtml($htmlContent, "progress_report_{$studentId}_{$semester}");

            return [
                'report_id' => $report->id,
                'file_url' => $fileUrl,
                'data' => $reportData,
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate progress report', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function generateBatchReportCards(string $classId, int $semester, string $academicYear): array
    {
        $students = Student::where('class_id', $classId)->get();
        $reports = [];

        foreach ($students as $student) {
            try {
                $report = $this->generateReportCard($student->id, $classId, $semester, $academicYear);
                $reports[] = $report;
            } catch (\Exception $e) {
                $this->logger->warning("Failed to generate report card for student {$student->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $reports;
    }

    private function calculateClassRank(string $studentId, string $classId, int $semester, string $academicYear): int
    {
        $classAverages = Grade::select('student_id')
            ->selectRaw('AVG(grade) as avg_grade')
            ->where('class_id', $classId)
            ->where('semester', $semester)
            ->groupBy('student_id')
            ->orderByDesc('avg_grade')
            ->get()
            ->pluck('student_id')
            ->values()
            ->toArray();

        $rank = array_search($studentId, $classAverages);
        return $rank !== false ? $rank + 1 : 0;
    }

    private function calculateCumulativeGpa(string $studentId): float
    {
        $grades = Grade::where('student_id', $studentId)->get();
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalGrade = $grades->sum('grade');
        return round($totalGrade / $grades->count(), 2);
    }

    private function calculateTotalCredits(string $studentId): int
    {
        $subjects = Grade::where('student_id', $studentId)
            ->distinct()
            ->pluck('subject_id')
            ->count();
        return $subjects * 3;
    }

    private function groupGradesByYear($grades): array
    {
        return $grades->groupBy('academic_year')->map(function ($yearGrades) {
            return $yearGrades->groupBy('semester')->map(function ($semesterGrades) {
                return [
                    'semester' => $semesterGrades->first()->semester,
                    'subjects' => $semesterGrades->groupBy('subject_id')->map(function ($subjectGrades) {
                        return [
                            'subject' => $subjectGrades->first()->subject->toArray(),
                            'grades' => $subjectGrades->pluck('grade')->toArray(),
                            'average' => round($subjectGrades->avg('grade'), 2),
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();
        })->toArray();
    }

    private function renderReportCardHtml(array $data, ?string $templateId = null): string
    {
        $template = $templateId ? ReportTemplate::find($templateId) : null;

        if ($template && $template->content) {
            return $this->renderTemplate($template->content, $data);
        }

        return $this->getDefaultReportCardTemplate($data);
    }

    private function renderTranscriptHtml(array $data): string
    {
        return $this->getDefaultTranscriptTemplate($data);
    }

    private function renderProgressReportHtml(array $data): string
    {
        return $this->getDefaultProgressReportTemplate($data);
    }

    private function renderTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $template = str_replace("{{$key}}", json_encode($value), $template);
            } else {
                $template = str_replace("{{$key}}", $value, $template);
            }
        }
        return $template;
    }

    private function getDefaultReportCardTemplate(array $data): string
    {
        $student = $data['student'];
        $class = $data['class'];

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report Card - ' . htmlspecialchars($student['name'] ?? 'N/A') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .student-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .grades-table th, .grades-table td { border: 1px solid #333; padding: 10px; text-align: left; }
        .grades-table th { background-color: #f0f0f0; }
        .footer { margin-top: 50px; border-top: 2px solid #333; padding-top: 20px; }
        .signature-section { margin-top: 50px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>School Report Card</h1>
        <h2>Academic Year: ' . htmlspecialchars($data['academic_year']) . ' | Semester: ' . htmlspecialchars($data['semester']) . '</h2>
    </div>

    <div class="student-info">
        <p><strong>Student Name:</strong> ' . htmlspecialchars($student['name'] ?? 'N/A') . '</p>
        <p><strong>Class:</strong> ' . htmlspecialchars($class['name'] ?? 'N/A') . '</p>
        <p><strong>NISN:</strong> ' . htmlspecialchars($student['nisn'] ?? 'N/A') . '</p>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Grade</th>
                <th>Grade Type</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($data['grades'] as $grade) {
            $html .= '<tr>
                <td>' . htmlspecialchars($grade['subject']['name'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($grade['grade']) . '</td>
                <td>' . htmlspecialchars($grade['grade_type'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($grade['notes'] ?? 'N/A') . '</td>
            </tr>';
        }

        $html .= '        </tbody>
    </table>

    <div style="margin: 30px 0;">
        <p><strong>Average Grade:</strong> ' . htmlspecialchars($data['average_grade']) . '</p>
        <p><strong>Rank in Class:</strong> ' . htmlspecialchars($data['rank_in_class']) . '</p>
    </div>

    <div class="footer">
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>

    <div class="signature-section">
        <p>__________________________</p>
        <p>Teacher Signature</p>
        <p>__________________________</p>
        <p>Parent/Guardian Signature</p>
    </div>
</body>
</html>';

        return $html;
    }

    private function getDefaultTranscriptTemplate(array $data): string
    {
        $student = $data['student'];

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Official Transcript - ' . htmlspecialchars($student['name'] ?? 'N/A') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; background: #f0f0f0; padding: 20px; }
        .academic-year { margin-bottom: 30px; page-break-inside: avoid; }
        .year-header { background-color: #333; color: white; padding: 10px; font-weight: bold; }
        .grades-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .grades-table th, .grades-table td { border: 1px solid #333; padding: 10px; text-align: left; }
        .grades-table th { background-color: #ddd; }
        .footer { margin-top: 50px; border-top: 2px solid #333; padding-top: 20px; }
        .signature-section { margin-top: 50px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Official Academic Transcript</h1>
    </div>

    <div class="summary">
        <p><strong>Student Name:</strong> ' . htmlspecialchars($student['name'] ?? 'N/A') . '</p>
        <p><strong>NISN:</strong> ' . htmlspecialchars($student['nisn'] ?? 'N/A') . '</p>
        <p><strong>Cumulative GPA:</strong> ' . htmlspecialchars($data['cumulative_gpa']) . '</p>
        <p><strong>Total Credits:</strong> ' . htmlspecialchars($data['total_credits']) . '</p>
    </div>';

        foreach ($data['academic_years'] as $year => $yearData) {
            $html .= '    <div class="academic-year">
        <div class="year-header">Academic Year: ' . htmlspecialchars($year) . '</div>';

            foreach ($yearData as $semesterData) {
                $html .= '        <h3>Semester ' . htmlspecialchars($semesterData['semester']) . '</h3>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Grades</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>';

                foreach ($semesterData['subjects'] as $subjectData) {
                    $grades = implode(', ', $subjectData['grades']);
                    $html .= '                <tr>
                    <td>' . htmlspecialchars($subjectData['subject']['name'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($grades) . '</td>
                    <td>' . htmlspecialchars($subjectData['average']) . '</td>
                </tr>';
                }

                $html .= '            </tbody>
        </table>';
            }

            $html .= '    </div>';
        }

        $html .= '    <div class="footer">
        <p><strong>Certification:</strong> This is a true and complete transcript of the academic record of the named student.</p>
        <p><strong>Issued Date:</strong> ' . date('Y-m-d') . '</p>
    </div>

    <div class="signature-section">
        <p>__________________________</p>
        <p>Principal Signature</p>
    </div>
</body>
</html>';

        return $html;
    }

    private function getDefaultProgressReportTemplate(array $data): string
    {
        $student = $data['student'];
        $class = $data['class'];

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Progress Report - ' . htmlspecialchars($student['name'] ?? 'N/A') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .student-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .grades-table th, .grades-table td { border: 1px solid #333; padding: 10px; text-align: left; }
        .grades-table th { background-color: #f0f0f0; }
        .portfolios { margin-bottom: 30px; }
        .portfolio-item { padding: 10px; background: #f9f9f9; margin-bottom: 10px; }
        .footer { margin-top: 50px; border-top: 2px solid #333; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Progress Report</h1>
        <h2>Academic Year: ' . htmlspecialchars($data['academic_year']) . ' | Semester: ' . htmlspecialchars($data['semester']) . '</h2>
    </div>

    <div class="student-info">
        <p><strong>Student Name:</strong> ' . htmlspecialchars($student['name'] ?? 'N/A') . '</p>
        <p><strong>Class:</strong> ' . htmlspecialchars($class['name'] ?? 'N/A') . '</p>
    </div>

    <h2>Current Grades</h2>
    <table class="grades-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Grade</th>
                <th>Grade Type</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($data['grades'] as $grade) {
            $html .= '            <tr>
                <td>' . htmlspecialchars($grade['subject']['name'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($grade['grade']) . '</td>
                <td>' . htmlspecialchars($grade['grade_type'] ?? 'N/A') . '</td>
            </tr>';
        }

        $html .= '        </tbody>
    </table>

    <h2>Student Portfolios</h2>
    <div class="portfolios">';

        foreach ($data['portfolios'] as $portfolio) {
            $html .= '        <div class="portfolio-item">
            <p><strong>' . htmlspecialchars($portfolio['title'] ?? 'N/A') . '</strong></p>
            <p>' . htmlspecialchars($portfolio['description'] ?? 'N/A') . '</p>
            <p><em>Added: ' . htmlspecialchars($portfolio['date_added']) . '</em></p>
        </div>';
        }

        $html .= '    </div>

    <div class="footer">
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>

    <div style="margin-top: 50px; text-align: center;">
        <p>__________________________</p>
        <p>Teacher Signature</p>
    </div>
</body>
</html>';

        return $html;
    }

    private function generatePdfFromHtml(string $html, string $filename): string
    {
        $filePath = storage_path('app/reports/' . $filename . '.pdf');
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $html);

        return '/storage/reports/' . $filename . '.pdf';
    }
}
