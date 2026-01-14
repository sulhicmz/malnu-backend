<?php

declare (strict_types = 1);

namespace Tests\Feature;

use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;
use App\Services\ReportGenerationService;
use App\Models\Grading\Report;
use App\Models\Grading\ReportTemplate;
use App\Models\SchoolManagement\Student;

class ReportGenerationTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function test_report_card_generation_with_valid_data()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $result = $this->container->get(ReportGenerationService::class)
                ->generateReportCard($studentId, $classId, $semester, $academicYear);

            $this->assertArrayHasKey('report_id', $result);
            $this->assertArrayHasKey('file_url', $result);
            $this->assertArrayHasKey('data', $result);

            $report = Report::find($result['report_id']);
            $this->assertNotNull($report);
            $this->assertEquals($studentId, $report->student_id);
            $this->assertEquals($classId, $report->class_id);
            $this->assertEquals($semester, $report->semester);
            $this->assertEquals($academicYear, $report->academic_year);

        } catch (\Exception $e) {
            $this->fail('Report card generation should work with valid data: ' . $e->getMessage());
        }
    }

    public function test_report_card_generation_with_invalid_student()
    {
        $studentId = 'invalid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->container->get(ReportGenerationService::class)
            ->generateReportCard($studentId, $classId, $semester, $academicYear);
    }

    public function test_transcript_generation_with_valid_student()
    {
        $studentId = 'valid-student-id';

        try {
            $result = $this->container->get(ReportGenerationService::class)
                ->generateTranscript($studentId);

            $this->assertArrayHasKey('report_id', $result);
            $this->assertArrayHasKey('file_url', $result);
            $this->assertArrayHasKey('data', $result);

            $report = Report::find($result['report_id']);
            $this->assertNotNull($report);
            $this->assertEquals($studentId, $report->student_id);
            $this->assertEquals('transcript', $report->academic_year);
            $this->assertEquals(0, $report->semester);

        } catch (\Exception $e) {
            $this->fail('Transcript generation should work with valid student: ' . $e->getMessage());
        }
    }

    public function test_transcript_generation_with_invalid_student()
    {
        $studentId = 'invalid-student-id';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->container->get(ReportGenerationService::class)
            ->generateTranscript($studentId);
    }

    public function test_progress_report_generation_with_valid_data()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $result = $this->container->get(ReportGenerationService::class)
                ->generateProgressReport($studentId, $classId, $semester, $academicYear);

            $this->assertArrayHasKey('report_id', $result);
            $this->assertArrayHasKey('file_url', $result);
            $this->assertArrayHasKey('data', $result);

            $report = Report::find($result['report_id']);
            $this->assertNotNull($report);
            $this->assertEquals($studentId, $report->student_id);
            $this->assertEquals($classId, $report->class_id);
            $this->assertEquals($semester, $report->semester);

        } catch (\Exception $e) {
            $this->fail('Progress report generation should work with valid data: ' . $e->getMessage());
        }
    }

    public function test_class_rank_calculation()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $report = $this->container->get(ReportGenerationService::class)
                ->generateReportCard($studentId, $classId, $semester, $academicYear);

            $this->assertArrayHasKey('rank_in_class', $report['data']);
            $this->assertGreaterThanOrEqual(1, $report['data']['rank_in_class']);

        } catch (\Exception $e) {
            $this->fail('Class rank calculation should work: ' . $e->getMessage());
        }
    }

    public function test_cumulative_gpa_calculation()
    {
        $studentId = 'valid-student-id';

        try {
            $result = $this->container->get(ReportGenerationService::class)
                ->generateTranscript($studentId);

            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('cumulative_gpa', $result['data']);
            $this->assertGreaterThanOrEqual(0, $result['data']['cumulative_gpa']);
            $this->assertLessThanOrEqual(100, $result['data']['cumulative_gpa']);

        } catch (\Exception $e) {
            $this->fail('Cumulative GPA calculation should work: ' . $e->getMessage());
        }
    }

    public function test_html_template_generation()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $result = $this->container->get(ReportGenerationService::class)
                ->generateReportCard($studentId, $classId, $semester, $academicYear);

            $this->assertArrayHasKey('file_url', $result);
            $this->assertStringEndsWith('.pdf', $result['file_url']);

        } catch (\Exception $e) {
            $this->fail('HTML template generation should work: ' . $e->getMessage());
        }
    }

    public function test_report_archival()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $this->container->get(ReportGenerationService::class)
                ->generateReportCard($studentId, $classId, $semester, $academicYear);

            $report = Report::where('student_id', $studentId)
                ->orderByDesc('created_at')
                ->first();

            $this->assertNotNull($report);
            $this->assertEquals($studentId, $report->student_id);
            $this->assertFalse($report->is_published);

        } catch (\Exception $e) {
            $this->fail('Report archival should work: ' . $e->getMessage());
        }
    }

    public function test_report_publishing()
    {
        $studentId = 'valid-student-id';
        $classId = 'valid-class-id';
        $semester = 1;
        $academicYear = '2024-2025';

        try {
            $this->container->get(ReportGenerationService::class)
                ->generateReportCard($studentId, $classId, $semester, $academicYear);

            $report = Report::where('student_id', $studentId)
                ->orderByDesc('created_at')
                ->first();

            $report->update([
                'is_published' => true,
                'published_at' => date('Y-m-d H:i:s'),
            ]);

            $this->assertTrue($report->is_published);
            $this->assertNotNull($report->published_at);

        } catch (\Exception $e) {
            $this->fail('Report publishing should work: ' . $e->getMessage());
        }
    }
}
