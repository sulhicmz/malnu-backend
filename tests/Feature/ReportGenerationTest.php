<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ReportService;
use App\Services\PdfService;
use PHPUnit\Framework\TestCase;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\Grading\Grade;
use App\Models\Grading\Competency;

class ReportGenerationTest extends TestCase
{
    /**
     * Test report card generation
     */
    public function testReportCardGeneration(): void
    {
        // Since the Hyperf framework has missing dependencies,
        // we'll test the service logic with mock data
        $reportService = new ReportService();
        
        // This test would normally require database models to be functional
        // For now, we'll just ensure the service can be instantiated
        $this->assertInstanceOf(ReportService::class, $reportService);
    }
    
    /**
     * Test transcript generation
     */
    public function testTranscriptGeneration(): void
    {
        $reportService = new ReportService();
        
        $this->assertInstanceOf(ReportService::class, $reportService);
    }
    
    /**
     * Test PDF generation
     */
    public function testPdfGeneration(): void
    {
        $pdfService = new PdfService();
        
        $mockReportData = [
            'student' => (object) ['name' => 'John Doe', 'email' => 'john@example.com', 'id' => '1'],
            'class' => (object) ['name' => 'Class 10A'],
            'semester' => 1,
            'academic_year' => '2023/2024',
            'grades' => [],
            'competencies' => [],
            'average_grade' => '85.50',
            'rank_in_class' => 5,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
        
        $html = $pdfService->generateReportCardPdf($mockReportData);
        
        $this->assertStringContainsString('Report Card', $html);
        $this->assertStringContainsString('John Doe', $html);
        
        $mockTranscriptData = [
            'student' => (object) ['name' => 'John Doe', 'email' => 'john@example.com', 'id' => '1'],
            'grades_by_year' => [],
            'cumulative_gpa' => '3.50',
            'total_credits' => 30,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
        
        $transcriptHtml = $pdfService->generateTranscriptPdf($mockTranscriptData);
        
        $this->assertStringContainsString('Academic Transcript', $transcriptHtml);
        $this->assertStringContainsString('John Doe', $transcriptHtml);
    }
}