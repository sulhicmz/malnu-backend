<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\Grading\ReportController;
use App\Models\Grading\Grade;
use App\Models\Grading\ReportTemplate;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Services\ReportGenerationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use PHPUnit\Framework\TestCase;

class ReportGenerationTest extends TestCase
{
    private RequestInterface $request;
    private ResponseInterface $response;
    private ReportGenerationService $reportService;
    private ReportController $reportController;

    protected function setUp(): void
    {
        parent::setUp();

        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
        $this->reportService = $container->get(ReportGenerationService::class);
        $this->reportController = new ReportController(
            $this->request,
            $this->response,
            $container,
            $this->reportService
        );
    }

    public function testGenerateReportCard()
    {
        $student = Student::factory()->create();
        Grade::factory()->count(5)->create([
            'student_id' => $student->id,
            'grade' => 85.5,
        ]);

        $this->request->shouldReceive('all')
            ->andReturn([
                'student_id' => $student->id,
                'semester' => '1',
                'academic_year' => '2024',
            ]);

        $result = $this->reportService->generateReportCard($student->id, '1', '2024');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('report', $result);
        $this->assertArrayHasKey('download_url', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('student_name', $result['data']);
        $this->assertArrayHasKey('average_grade', $result['data']);
    }

    public function testGenerateTranscript()
    {
        $student = Student::factory()->create();
        Grade::factory()->count(10)->create([
            'student_id' => $student->id,
            'grade' => 88.0,
        ]);

        $result = $this->reportService->generateTranscript($student->id, '2024');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('report', $result);
        $this->assertArrayHasKey('download_url', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('cumulative_gpa', $result['data']);
        $this->assertArrayHasKey('academic_records', $result['data']);
    }

    public function testGenerateProgressReport()
    {
        $student = Student::factory()->create();
        Grade::factory()->count(5)->create([
            'student_id' => $student->id,
            'semester' => '2',
            'grade' => 90.0,
        ]);

        Grade::factory()->count(5)->create([
            'student_id' => $student->id,
            'semester' => '1',
            'grade' => 85.0,
        ]);

        $result = $this->reportService->generateProgressReport($student->id, '2', '2024');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('report', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('current_average', $result['data']);
        $this->assertArrayHasKey('previous_average', $result['data']);
        $this->assertArrayHasKey('improvement', $result['data']);
    }

    public function testTemplateManagement()
    {
        $templateData = [
            'name' => 'Test Report Card Template',
            'type' => 'report_card',
            'html_template' => '<html><body><h1>Test Report</h1></body></html>',
            'variables' => ['student_name', 'average_grade'],
            'is_active' => true,
        ];

        $template = ReportTemplate::create($templateData);

        $this->assertNotNull($template);
        $this->assertEquals($templateData['name'], $template->name);
        $this->assertEquals($templateData['type'], $template->type);
        $this->assertIsArray($template->variables);
    }

    public function testReportRetrieval()
    {
        $student = Student::factory()->create();
        $reports = \App\Models\Grading\GeneratedReport::factory()->count(3)->create([
            'student_id' => $student->id,
            'report_type' => 'report_card',
            'is_published' => true,
        ]);

        $retrieved = \App\Models\Grading\GeneratedReport::where('student_id', $student->id)->get();

        $this->assertEquals(3, $retrieved->count());
    }

    public function testReportGenerationWithMissingStudent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->reportService->generateReportCard('invalid-id', '1', '2024');
    }

    public function testClassRankCalculation()
    {
        $class = ClassModel::factory()->create();
        $students = Student::factory()->count(10)->create(['class_id' => $class->id]);

        foreach ($students as $index => $student) {
            Grade::factory()->create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'grade' => 90.0 - ($index * 2),
            ]);
        }

        $topStudent = $students->first();
        $rank = $this->reportService->generateReportCard($topStudent->id, '1', '2024');

        $this->assertEquals(1, $rank['data']['rank_in_class']);
    }

    public function testReportPublishing()
    {
        $report = \App\Models\Grading\GeneratedReport::factory()->create([
            'is_published' => false,
        ]);

        $report->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $updatedReport = \App\Models\Grading\GeneratedReport::find($report->id);
        $this->assertTrue($updatedReport->is_published);
        $this->assertNotNull($updatedReport->published_at);
    }

    public function testReportDataIntegrity()
    {
        $student = Student::factory()->create();
        $grades = Grade::factory()->count(5)->create([
            'student_id' => $student->id,
            'grade' => 87.5,
        ]);

        $result = $this->reportService->generateReportCard($student->id, '1', '2024');

        $this->assertCount(5, $result['data']['grades']);
        $this->assertEquals($grades->avg('grade'), $result['data']['average_grade']);
        $this->assertEquals($student->name, $result['data']['student_name']);
    }

    public function testTemplateCreationAndRetrieval()
    {
        $template = ReportTemplate::create([
            'name' => 'Custom Report Template',
            'type' => 'transcript',
            'html_template' => '<html><body><h1>{student_name}</h1></body></html>',
            'variables' => ['student_name', 'cumulative_gpa'],
            'is_active' => true,
        ]);

        $retrieved = ReportTemplate::active()->byType('transcript')->first();

        $this->assertNotNull($retrieved);
        $this->assertEquals($template->id, $retrieved->id);
        $this->assertTrue($retrieved->is_active);
    }
}
