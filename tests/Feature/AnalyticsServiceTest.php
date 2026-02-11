<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\Http\Message\ServerRequest;
use HyperfTest\Http\Message\ServerRequestFactory;
use HyperfTest\Http\Message\UploadedFile;
use App\Services\AnalyticsService;
use App\Models\Analytics\AnalyticsData;
use App\Models\SchoolManagement\Student;
use PHPUnit\Framework\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
    }

    public function test_get_dashboard_overview()
    {
        $overview = $this->analyticsService->getDashboardOverview();

        $this->assertIsArray($overview);
        $this->assertArrayHasKey('students_count', $overview);
        $this->assertArrayHasKey('teachers_count', $overview);
        $this->assertArrayHasKey('classes_count', $overview);
        $this->assertArrayHasKey('average_attendance', $overview);
        $this->assertArrayHasKey('average_gpa', $overview);
        $this->assertArrayHasKey('total_assessments', $overview);
        $this->assertIsNumeric($overview['students_count']);
        $this->assertIsNumeric($overview['average_attendance']);
    }

    public function test_record_metric()
    {
        $data = [
            'user_id' => 'test-user-id',
            'data_type' => 'performance',
            'metric_name' => 'test_metric',
            'metric_value' => 85.5,
            'metadata' => ['test' => 'data'],
            'period' => 'daily',
        ];

        $metric = $this->analyticsService->recordMetric($data);

        $this->assertInstanceOf(AnalyticsData::class, $metric);
        $this->assertEquals('performance', $metric->data_type);
        $this->assertEquals('test_metric', $metric->metric_name);
        $this->assertEquals(85.5, $metric->metric_value);
    }

    public function test_get_student_performance_throws_exception_for_invalid_student()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->analyticsService->getStudentPerformance('invalid-student-id');
    }

    public function test_get_class_performance_throws_exception_for_invalid_class()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class not found');

        $this->analyticsService->getClassMetrics('invalid-class-id');
    }

    public function test_generate_report_throws_exception_for_invalid_type()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid report type');

        $this->analyticsService->generateReport('invalid_type');
    }

    public function test_generate_student_performance_report()
    {
        $data = $this->analyticsService->generateReport('student_performance');

        $this->assertIsArray($data);
    }

    public function test_generate_attendance_report()
    {
        $data = $this->analyticsService->generateReport('attendance');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('period', $data);
        $this->assertArrayHasKey('total_records', $data);
        $this->assertArrayHasKey('present', $data);
        $this->assertArrayHasKey('absent', $data);
    }
}
