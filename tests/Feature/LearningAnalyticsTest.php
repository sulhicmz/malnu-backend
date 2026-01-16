<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\HttpTestCase;
use App\Models\Analytics\LearningActivity;
use App\Models\Analytics\StudentPerformanceMetric;

class LearningAnalyticsTest extends HttpTestCase
{
    public function testLearningActivityCreation()
    {
        $activity = LearningActivity::create([
            'student_id' => 'test-student-id',
            'activity_type' => 'quiz',
            'title' => 'Test Quiz',
            'activity_date' => now(),
        ]);

        $this->assertNotNull($activity);
        $this->assertEquals('quiz', $activity->activity_type);
    }

    public function testStudentPerformanceMetricCreation()
    {
        $metric = StudentPerformanceMetric::create([
            'student_id' => 'test-student-id',
            'semester' => '2024-2025-1',
            'gpa' => 3.5,
            'attendance_rate' => 85.5,
            'engagement_score' => 75.0,
        ]);

        $this->assertNotNull($metric);
        $this->assertEquals('excellent', $metric->getOverallPerformanceAttribute());
    }

    public function testGetStudentPerformanceEndpoint()
    {
        $studentId = 'test-student-id';

        StudentPerformanceMetric::create([
            'student_id' => $studentId,
            'semester' => '2024-2025-1',
            'gpa' => 3.2,
        ]);

        $response = $this->get("/api/analytics/student/{$studentId}/performance");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('gpa', $data);
        $this->assertEquals('3.2', $data['gpa']);
    }
}
