<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\ReportSignature;
use App\Models\Grading\ReportTemplate;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use Carbon\Carbon;
use Exception;
use Hypervel\Support\Facades\DB;

class ReportGenerationService
{
    private PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function generateReportCard(
        string $studentId,
        string $classId,
        int $semester,
        string $academicYear,
        ?string $templateId = null,
        ?string $createdBy = null
    ): Report {
        $student = Student::find($studentId);
        if (! $student) {
            throw new Exception('Student not found');
        }

        $class = ClassModel::find($classId);
        if (! $class) {
            throw new Exception('Class not found');
        }

        $template = $this->getTemplate('report_card', $class->grade_level, $templateId);

        $grades = $this->getStudentGrades($studentId, $classId, $semester, $academicYear);
        $competencies = $this->getStudentCompetencies($studentId, $semester, $academicYear);
        $classRank = $this->calculateClassRank($studentId, $classId, $semester, $academicYear);
        $averageGrade = $this->calculateAverageGrade($grades);

        $reportData = [
            'student_id' => $studentId,
            'class_id' => $classId,
            'semester' => $semester,
            'academic_year' => $academicYear,
            'average_grade' => $averageGrade,
            'rank_in_class' => $classRank,
            'template_id' => $template?->id,
            'created_by' => $createdBy,
        ];

        $report = Report::create($reportData);

        $htmlContent = $this->generateReportCardHtml($report, $student, $class, $grades, $competencies, $template);
        $filePath = $this->pdfService->generateAndStore($htmlContent, 'report_cards', $report->id);

        $report->update([
            'file_path' => $filePath,
            'file_type' => 'html',
        ]);

        return $report->fresh(['student', 'class', 'template', 'signatures']);
    }

    public function generateTranscript(string $studentId, ?string $createdBy = null): Report
    {
        $student = Student::find($studentId);
        if (! $student) {
            throw new Exception('Student not found');
        }

        $template = $this->getTemplate('transcript', null, null);

        $academicHistory = $this->getAcademicHistory($studentId);
        $cumulativeGpa = $this->calculateCumulativeGpa($academicHistory);
        $totalCredits = $this->calculateTotalCredits($academicHistory);

        $reportData = [
            'student_id' => $studentId,
            'class_id' => $student->class_id,
            'semester' => 0,
            'academic_year' => 'All',
            'average_grade' => $cumulativeGpa,
            'rank_in_class' => null,
            'template_id' => $template?->id,
            'created_by' => $createdBy,
        ];

        $report = Report::create($reportData);

        $htmlContent = $this->generateTranscriptHtml($report, $student, $academicHistory, $cumulativeGpa, $totalCredits, $template);
        $filePath = $this->pdfService->generateAndStore($htmlContent, 'transcripts', $report->id);

        $report->update([
            'file_path' => $filePath,
            'file_type' => 'html',
        ]);

        return $report->fresh(['student', 'template', 'signatures']);
    }

    public function generateProgressReport(
        string $studentId,
        string $classId,
        int $semester,
        string $academicYear,
        ?string $createdBy = null
    ): Report {
        $student = Student::find($studentId);
        if (! $student) {
            throw new Exception('Student not found');
        }

        $class = ClassModel::find($classId);
        if (! $class) {
            throw new Exception('Class not found');
        }

        $template = $this->getTemplate('progress_report', $class->grade_level, null);

        $currentGrades = $this->getStudentGrades($studentId, $classId, $semester, $academicYear);
        $previousGrades = $this->getPreviousGrades($studentId, $semester, $academicYear);
        $improvementTrends = $this->calculateImprovementTrends($currentGrades, $previousGrades);

        $reportData = [
            'student_id' => $studentId,
            'class_id' => $classId,
            'semester' => $semester,
            'academic_year' => $academicYear,
            'average_grade' => $this->calculateAverageGrade($currentGrades),
            'rank_in_class' => null,
            'template_id' => $template?->id,
            'created_by' => $createdBy,
        ];

        $report = Report::create($reportData);

        $htmlContent = $this->generateProgressReportHtml($report, $student, $class, $currentGrades, $improvementTrends, $template);
        $filePath = $this->pdfService->generateAndStore($htmlContent, 'progress_reports', $report->id);

        $report->update([
            'file_path' => $filePath,
            'file_type' => 'html',
        ]);

        return $report->fresh(['student', 'class', 'template', 'signatures']);
    }

    public function batchGenerateReportCards(
        string $classId,
        int $semester,
        string $academicYear,
        ?string $templateId = null,
        ?string $createdBy = null
    ): array {
        $class = ClassModel::find($classId);
        if (! $class) {
            throw new Exception('Class not found');
        }

        $students = Student::where('class_id', $classId)->get();
        $results = [
            'successful' => [],
            'failed' => [],
        ];

        foreach ($students as $student) {
            try {
                $report = $this->generateReportCard(
                    $student->id,
                    $classId,
                    $semester,
                    $academicYear,
                    $templateId,
                    $createdBy
                );
                $results['successful'][] = [
                    'student_id' => $student->id,
                    'report_id' => $report->id,
                ];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function publishReport(string $reportId): Report
    {
        $report = Report::find($reportId);
        if (! $report) {
            throw new Exception('Report not found');
        }

        $report->update([
            'is_published' => true,
            'published_at' => Carbon::now(),
        ]);

        return $report->fresh();
    }

    public function addSignature(
        string $reportId,
        string $signerName,
        string $signerTitle,
        ?string $signatureImageUrl = null,
        ?string $notes = null,
        ?string $signedBy = null
    ): ReportSignature {
        $report = Report::find($reportId);
        if (! $report) {
            throw new Exception('Report not found');
        }

        return ReportSignature::create([
            'report_id' => $reportId,
            'signer_name' => $signerName,
            'signer_title' => $signerTitle,
            'signature_image_url' => $signatureImageUrl,
            'signed_at' => Carbon::now(),
            'notes' => $notes,
            'signed_by' => $signedBy,
        ]);
    }

    public function getStudentReports(
        string $studentId,
        ?int $semester = null,
        ?string $academicYear = null,
        ?bool $isPublished = null
    ) {
        $query = Report::with(['template', 'signatures'])
            ->where('student_id', $studentId);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        if ($academicYear !== null) {
            $query->where('academic_year', $academicYear);
        }

        if ($isPublished !== null) {
            $query->where('is_published', $isPublished);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getClassReports(
        string $classId,
        ?int $semester = null,
        ?string $academicYear = null
    ) {
        $query = Report::with(['student', 'template'])
            ->where('class_id', $classId);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        if ($academicYear !== null) {
            $query->where('academic_year', $academicYear);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getTemplate(string $type, ?string $gradeLevel, ?string $templateId): ?ReportTemplate
    {
        if ($templateId) {
            return ReportTemplate::find($templateId);
        }

        return ReportTemplate::active()
            ->forType($type)
            ->forGradeLevel($gradeLevel)
            ->default()
            ->first();
    }

    private function getStudentGrades(string $studentId, string $classId, int $semester, string $academicYear)
    {
        return Grade::with('subject')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('semester', $semester)
            ->get()
            ->groupBy('subject_id');
    }

    private function getStudentCompetencies(string $studentId, int $semester, string $academicYear)
    {
        return Competency::where('student_id', $studentId)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->get();
    }

    private function calculateClassRank(string $studentId, string $classId, int $semester, string $academicYear): int
    {
        $studentAverage = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('semester', $semester)
            ->avg('grade') ?? 0;

        $higherAverages = Grade::where('class_id', $classId)
            ->where('semester', $semester)
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw('AVG(grade) > ?', [$studentAverage])
            ->count();

        return $higherAverages + 1;
    }

    private function calculateAverageGrade($grades): float
    {
        $allGrades = collect();
        foreach ($grades as $subjectGrades) {
            $allGrades = $allGrades->merge($subjectGrades->pluck('grade'));
        }

        return $allGrades->avg() ?? 0;
    }

    private function getAcademicHistory(string $studentId): array
    {
        $grades = Grade::with(['subject', 'class'])
            ->where('student_id', $studentId)
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->get();

        $history = [];
        foreach ($grades->groupBy(['academic_year', 'semester']) as $year => $semesters) {
            foreach ($semesters as $semester => $semesterGrades) {
                $history[] = [
                    'academic_year' => $year,
                    'semester' => $semester,
                    'grades' => $semesterGrades,
                    'average' => $semesterGrades->avg('grade'),
                    'credits' => $semesterGrades->count(),
                ];
            }
        }

        return $history;
    }

    private function calculateCumulativeGpa(array $academicHistory): float
    {
        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($academicHistory as $term) {
            $totalPoints += $term['average'] * $term['credits'];
            $totalCredits += $term['credits'];
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
    }

    private function calculateTotalCredits(array $academicHistory): int
    {
        return array_sum(array_column($academicHistory, 'credits'));
    }

    private function getPreviousGrades(string $studentId, int $currentSemester, string $currentAcademicYear)
    {
        return Grade::where('student_id', $studentId)
            ->where(function ($query) use ($currentSemester, $currentAcademicYear) {
                $query->where('academic_year', '<', $currentAcademicYear)
                      ->orWhere(function ($q) use ($currentSemester, $currentAcademicYear) {
                          $q->where('academic_year', $currentAcademicYear)
                            ->where('semester', '<', $currentSemester);
                      });
            })
            ->get()
            ->groupBy('subject_id');
    }

    private function calculateImprovementTrends($currentGrades, $previousGrades): array
    {
        $trends = [];

        foreach ($currentGrades as $subjectId => $subjectGrades) {
            $currentAvg = $subjectGrades->avg('grade');
            $previousAvg = $previousGrades->get($subjectId)?->avg('grade') ?? 0;

            $trends[$subjectId] = [
                'current_average' => $currentAvg,
                'previous_average' => $previousAvg,
                'improvement' => $currentAvg - $previousAvg,
                'trend' => $currentAvg > $previousAvg ? 'improving' : ($currentAvg < $previousAvg ? 'declining' : 'stable'),
            ];
        }

        return $trends;
    }

    private function generateReportCardHtml($report, $student, $class, $grades, $competencies, $template): string
    {
        $placeholders = $this->getReportCardPlaceholders($report, $student, $class, $grades, $competencies);

        $header = $template?->header_template ?? $this->getDefaultReportCardHeader();
        $content = $template?->content_template ?? $this->getDefaultReportCardContent();
        $footer = $template?->footer_template ?? $this->getDefaultReportCardFooter();
        $css = $template?->css_styles ?? $this->getDefaultReportCardCss();

        $html = '<!DOCTYPE html><html><head><style>' . $css . '</style></head><body>';
        $html .= $this->replacePlaceholders($header, $placeholders);
        $html .= $this->replacePlaceholders($content, $placeholders);
        $html .= $this->replacePlaceholders($footer, $placeholders);
        $html .= '</body></html>';

        return $html;
    }

    private function generateTranscriptHtml($report, $student, $academicHistory, $cumulativeGpa, $totalCredits, $template): string
    {
        $placeholders = $this->getTranscriptPlaceholders($report, $student, $academicHistory, $cumulativeGpa, $totalCredits);

        $header = $template?->header_template ?? $this->getDefaultTranscriptHeader();
        $content = $template?->content_template ?? $this->getDefaultTranscriptContent();
        $footer = $template?->footer_template ?? $this->getDefaultTranscriptFooter();
        $css = $template?->css_styles ?? $this->getDefaultTranscriptCss();

        $html = '<!DOCTYPE html><html><head><style>' . $css . '</style></head><body>';
        $html .= $this->replacePlaceholders($header, $placeholders);
        $html .= $this->replacePlaceholders($content, $placeholders);
        $html .= $this->replacePlaceholders($footer, $placeholders);
        $html .= '</body></html>';

        return $html;
    }

    private function generateProgressReportHtml($report, $student, $class, $grades, $improvementTrends, $template): string
    {
        $placeholders = $this->getProgressReportPlaceholders($report, $student, $class, $grades, $improvementTrends);

        $header = $template?->header_template ?? $this->getDefaultProgressReportHeader();
        $content = $template?->content_template ?? $this->getDefaultProgressReportContent();
        $footer = $template?->footer_template ?? $this->getDefaultProgressReportFooter();
        $css = $template?->css_styles ?? $this->getDefaultProgressReportCss();

        $html = '<!DOCTYPE html><html><head><style>' . $css . '</style></head><body>';
        $html .= $this->replacePlaceholders($header, $placeholders);
        $html .= $this->replacePlaceholders($content, $placeholders);
        $html .= $this->replacePlaceholders($footer, $placeholders);
        $html .= '</body></html>';

        return $html;
    }

    private function getReportCardPlaceholders($report, $student, $class, $grades, $competencies): array
    {
        $gradesHtml = $this->generateGradesTableHtml($grades);
        $competenciesHtml = $this->generateCompetenciesTableHtml($competencies);

        return [
            '{{student_name}}' => $student->name ?? 'N/A',
            '{{student_nisn}}' => $student->nisn ?? 'N/A',
            '{{student_nis}}' => $student->nis ?? 'N/A',
            '{{class_name}}' => $class->name ?? 'N/A',
            '{{class_grade_level}}' => $class->grade_level ?? 'N/A',
            '{{semester}}' => $report->semester,
            '{{academic_year}}' => $report->academic_year,
            '{{average_grade}}' => number_format($report->average_grade, 2),
            '{{rank_in_class}}' => $report->rank_in_class ?? 'N/A',
            '{{homeroom_notes}}' => $report->homeroom_notes ?? '',
            '{{principal_notes}}' => $report->principal_notes ?? '',
            '{{grades_table}}' => $gradesHtml,
            '{{competencies_table}}' => $competenciesHtml,
            '{{generation_date}}' => Carbon::now()->format('d F Y'),
        ];
    }

    private function getTranscriptPlaceholders($report, $student, $academicHistory, $cumulativeGpa, $totalCredits): array
    {
        $historyHtml = $this->generateAcademicHistoryHtml($academicHistory);

        return [
            '{{student_name}}' => $student->name ?? 'N/A',
            '{{student_nisn}}' => $student->nisn ?? 'N/A',
            '{{student_nis}}' => $student->nis ?? 'N/A',
            '{{date_of_birth}}' => $student->date_of_birth?->format('d F Y') ?? 'N/A',
            '{{place_of_birth}}' => $student->place_of_birth ?? 'N/A',
            '{{enrollment_date}}' => $student->enrollment_date?->format('d F Y') ?? 'N/A',
            '{{graduation_date}}' => $student->graduation_date?->format('d F Y') ?? 'Not Graduated',
            '{{cumulative_gpa}}' => number_format($cumulativeGpa, 2),
            '{{total_credits}}' => $totalCredits,
            '{{academic_history}}' => $historyHtml,
            '{{generation_date}}' => Carbon::now()->format('d F Y'),
        ];
    }

    private function getProgressReportPlaceholders($report, $student, $class, $grades, $improvementTrends): array
    {
        $gradesHtml = $this->generateGradesTableHtml($grades);
        $trendsHtml = $this->generateTrendsTableHtml($improvementTrends);

        return [
            '{{student_name}}' => $student->name ?? 'N/A',
            '{{student_nisn}}' => $student->nisn ?? 'N/A',
            '{{class_name}}' => $class->name ?? 'N/A',
            '{{semester}}' => $report->semester,
            '{{academic_year}}' => $report->academic_year,
            '{{average_grade}}' => number_format($report->average_grade, 2),
            '{{grades_table}}' => $gradesHtml,
            '{{improvement_trends}}' => $trendsHtml,
            '{{generation_date}}' => Carbon::now()->format('d F Y'),
        ];
    }

    private function replacePlaceholders(string $template, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    private function generateGradesTableHtml($grades): string
    {
        $html = '<table class="grades-table"><thead><tr>';
        $html .= '<th>Subject</th><th>Assignment</th><th>Quiz</th><th>Exam</th><th>Average</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($grades as $subjectId => $subjectGrades) {
            $firstGrade = $subjectGrades->first();
            $subjectName = $firstGrade?->subject?->name ?? 'Unknown Subject';
            $assignmentAvg = $subjectGrades->whereNotNull('assignment_id')->avg('grade') ?? 0;
            $quizAvg = $subjectGrades->whereNotNull('quiz_id')->avg('grade') ?? 0;
            $examAvg = $subjectGrades->whereNotNull('exam_id')->avg('grade') ?? 0;
            $overallAvg = $subjectGrades->avg('grade') ?? 0;

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($subjectName) . '</td>';
            $html .= '<td>' . number_format($assignmentAvg, 2) . '</td>';
            $html .= '<td>' . number_format($quizAvg, 2) . '</td>';
            $html .= '<td>' . number_format($examAvg, 2) . '</td>';
            $html .= '<td><strong>' . number_format($overallAvg, 2) . '</strong></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function generateCompetenciesTableHtml($competencies): string
    {
        $html = '<table class="competencies-table"><thead><tr>';
        $html .= '<th>Competency</th><th>Score</th><th>Description</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($competencies as $competency) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($competency->name) . '</td>';
            $html .= '<td>' . $competency->score . '</td>';
            $html .= '<td>' . htmlspecialchars($competency->description ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function generateAcademicHistoryHtml(array $academicHistory): string
    {
        $html = '';

        foreach ($academicHistory as $term) {
            $html .= '<div class="academic-term">';
            $html .= '<h4>Academic Year ' . $term['academic_year'] . ' - Semester ' . $term['semester'] . '</h4>';
            $html .= '<p><strong>Average:</strong> ' . number_format($term['average'], 2) . '</p>';
            $html .= '<table class="grades-table"><thead><tr>';
            $html .= '<th>Subject</th><th>Grade</th><th>Type</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($term['grades'] as $grade) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($grade->subject?->name ?? 'Unknown') . '</td>';
                $html .= '<td>' . number_format($grade->grade, 2) . '</td>';
                $html .= '<td>' . ucfirst($grade->grade_type ?? 'Assignment') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></div>';
        }

        return $html;
    }

    private function generateTrendsTableHtml(array $trends): string
    {
        $html = '<table class="trends-table"><thead><tr>';
        $html .= '<th>Subject</th><th>Previous</th><th>Current</th><th>Change</th><th>Trend</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($trends as $subjectId => $trend) {
            $html .= '<tr>';
            $html .= '<td>Subject ' . $subjectId . '</td>';
            $html .= '<td>' . number_format($trend['previous_average'], 2) . '</td>';
            $html .= '<td>' . number_format($trend['current_average'], 2) . '</td>';
            $html .= '<td>' . ($trend['improvement'] >= 0 ? '+' : '') . number_format($trend['improvement'], 2) . '</td>';
            $html .= '<td class="trend-' . $trend['trend'] . '">' . ucfirst($trend['trend']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function getDefaultReportCardHeader(): string
    {
        return '<div class="report-header"><h1>REPORT CARD</h1><p>Academic Year {{academic_year}} - Semester {{semester}}</p></div>';
    }

    private function getDefaultReportCardContent(): string
    {
        return '<div class="student-info"><h2>{{student_name}}</h2><p>NISN: {{student_nisn}} | Class: {{class_name}}</p></div><div class="grades-section"><h3>Subject Grades</h3>{{grades_table}}</div><div class="competencies-section"><h3>Competencies</h3>{{competencies_table}}</div><div class="summary-section"><p><strong>Average Grade:</strong> {{average_grade}}</p><p><strong>Class Rank:</strong> {{rank_in_class}}</p></div><div class="notes-section"><h3>Notes</h3><p><strong>Homeroom Teacher:</strong> {{homeroom_notes}}</p><p><strong>Principal:</strong> {{principal_notes}}</p></div>';
    }

    private function getDefaultReportCardFooter(): string
    {
        return '<div class="report-footer"><p>Generated on {{generation_date}}</p></div>';
    }

    private function getDefaultReportCardCss(): string
    {
        return 'body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:800px;margin:0 auto;padding:20px}.report-header{text-align:center;border-bottom:2px solid #333;padding-bottom:20px;margin-bottom:30px}.report-header h1{margin:0;color:#2c5282}.student-info{margin-bottom:30px}.grades-section,.competencies-section,.summary-section,.notes-section{margin-bottom:25px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background-color:#f5f5f5;font-weight:bold}.trend-improving{color:green}.trend-declining{color:red}.trend-stable{color:orange}.report-footer{text-align:center;margin-top:40px;padding-top:20px;border-top:1px solid #ddd;color:#666}';
    }

    private function getDefaultTranscriptHeader(): string
    {
        return '<div class="report-header"><h1>ACADEMIC TRANSCRIPT</h1><p>Official Academic Record</p></div>';
    }

    private function getDefaultTranscriptContent(): string
    {
        return '<div class="student-info"><h2>{{student_name}}</h2><p>NISN: {{student_nisn}}</p><p>Date of Birth: {{date_of_birth}}</p><p>Place of Birth: {{place_of_birth}}</p><p>Enrollment Date: {{enrollment_date}}</p><p>Graduation Date: {{graduation_date}}</p></div><div class="academic-history"><h3>Academic History</h3>{{academic_history}}</div><div class="summary-section"><p><strong>Cumulative GPA:</strong> {{cumulative_gpa}}</p><p><strong>Total Credits:</strong> {{total_credits}}</p></div>';
    }

    private function getDefaultTranscriptFooter(): string
    {
        return '<div class="report-footer"><p>This is an official transcript. Any alteration invalidates this document.</p><p>Generated on {{generation_date}}</p></div>';
    }

    private function getDefaultTranscriptCss(): string
    {
        return 'body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:800px;margin:0 auto;padding:20px}.report-header{text-align:center;border-bottom:3px double #333;padding-bottom:20px;margin-bottom:30px}.report-header h1{margin:0;color:#2c5282}.student-info{margin-bottom:30px;padding:15px;background:#f9f9f9;border:1px solid #ddd}.academic-term{margin-bottom:30px;padding:15px;border:1px solid #eee}.academic-term h4{margin-top:0;color:#2c5282;border-bottom:1px solid #ddd;padding-bottom:10px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background-color:#f5f5f5;font-weight:bold}.summary-section{margin-top:30px;padding:15px;background:#f0f8ff;border:1px solid #b0c4de}.report-footer{text-align:center;margin-top:40px;padding-top:20px;border-top:2px solid #333;color:#666;font-size:0.9em}';
    }

    private function getDefaultProgressReportHeader(): string
    {
        return '<div class="report-header"><h1>PROGRESS REPORT</h1><p>Academic Year {{academic_year}} - Semester {{semester}}</p></div>';
    }

    private function getDefaultProgressReportContent(): string
    {
        return '<div class="student-info"><h2>{{student_name}}</h2><p>NISN: {{student_nisn}} | Class: {{class_name}}</p></div><div class="grades-section"><h3>Current Grades</h3>{{grades_table}}</div><div class="trends-section"><h3>Improvement Trends</h3>{{improvement_trends}}</div><div class="summary-section"><p><strong>Current Average:</strong> {{average_grade}}</p></div>';
    }

    private function getDefaultProgressReportFooter(): string
    {
        return '<div class="report-footer"><p>This is an interim progress report.</p><p>Generated on {{generation_date}}</p></div>';
    }

    private function getDefaultProgressReportCss(): string
    {
        return 'body{font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:800px;margin:0 auto;padding:20px}.report-header{text-align:center;border-bottom:2px solid #666;padding-bottom:20px;margin-bottom:30px}.report-header h1{margin:0;color:#4a5568}.student-info{margin-bottom:30px}.grades-section,.trends-section,.summary-section{margin-bottom:25px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background-color:#f5f5f5;font-weight:bold}.trend-improving{color:green}.trend-declining{color:red}.trend-stable{color:orange}.summary-section{background:#f7fafc;padding:15px;border:1px solid #e2e8f0}.report-footer{text-align:center;margin-top:40px;padding-top:20px;border-top:1px solid #ddd;color:#666}';
    }
}
