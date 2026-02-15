<?php

declare(strict_types=1);

namespace HyperfTest\Feature;

use App\Models\Analytics\EarlyWarning;
use App\Models\Analytics\KnowledgeGap;
use App\Models\Analytics\LearningActivity;
use App\Models\Analytics\StudentPerformanceMetric;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

class LearningAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRecordLearningActivity()
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->post('/api/analytics/learning/activities', [
            'student_id' => $student->id,
            'activity_type' => 'assignment',
            'activity_name' => 'Math Homework',
            'subject_id' => $subject->id,
            'score' => 85,
            'max_score' => 100,
            'duration_minutes' => 45,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Learning activity recorded successfully',
            ]);

        $this->assertDatabaseHas('learning_activities', [
            'student_id' => $student->id,
            'activity_type' => 'assignment',
            'activity_name' => 'Math Homework',
        ]);
    }

    public function testGetStudentPerformanceSummary()
    {
        $student = Student::factory()->create();
        StudentPerformanceMetric::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'metric_type' => 'gpa',
            'value' => 3.5,
            'period_type' => 'monthly',
            'period_start' => now()->subMonth(),
            'period_end' => now(),
        ]);

        $response = $this->get("/api/analytics/learning/students/{$student->id}/performance");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.student_id', $student->id);
    }

    public function testGetClassPerformanceMetrics()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create(['class_id' => $class->id]);

        StudentPerformanceMetric::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'metric_type' => 'gpa',
            'value' => 3.5,
            'period_type' => 'monthly',
            'period_start' => now()->subMonth(),
            'period_end' => now(),
        ]);

        $response = $this->get("/api/analytics/learning/classes/{$class->id}/performance");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.class_id', $class->id);
    }

    public function testDetectAtRiskStudents()
    {
        $student = Student::factory()->create();

        EarlyWarning::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'warning_type' => 'low_attendance',
            'severity' => 'high',
            'description' => 'Low attendance rate',
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        $response = $this->get('/api/analytics/learning/at-risk-students');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.count', 1);
    }

    public function testIdentifyKnowledgeGaps()
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        LearningActivity::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'activity_type' => 'quiz',
            'activity_name' => 'Algebra Quiz',
            'score' => 50,
            'max_score' => 100,
            'activity_date' => now(),
        ]);

        $response = $this->get("/api/analytics/learning/students/{$student->id}/knowledge-gaps?subject_id={$subject->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.student_id', $student->id);
    }

    public function testKnowledgeGapsRequiresSubjectId()
    {
        $student = Student::factory()->create();

        $response = $this->get("/api/analytics/learning/students/{$student->id}/knowledge-gaps");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'subject_id is required',
            ]);
    }

    public function testAcknowledgeWarning()
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();

        $warning = EarlyWarning::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'warning_type' => 'performance_decline',
            'severity' => 'medium',
            'description' => 'Performance decline detected',
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        $response = $this->post("/api/analytics/learning/warnings/{$warning->id}/acknowledge");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Warning acknowledged successfully',
            ]);

        $this->assertDatabaseHas('early_warnings', [
            'id' => $warning->id,
            'status' => 'acknowledged',
        ]);
    }

    public function testResolveWarning()
    {
        $student = Student::factory()->create();

        $warning = EarlyWarning::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'warning_type' => 'low_engagement',
            'severity' => 'medium',
            'description' => 'Low engagement detected',
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        $response = $this->post("/api/analytics/learning/warnings/{$warning->id}/resolve", [
            'resolution_notes' => 'Student has improved after intervention',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Warning resolved successfully',
            ]);

        $this->assertDatabaseHas('early_warnings', [
            'id' => $warning->id,
            'status' => 'resolved',
        ]);
    }

    public function testCalculateMetricsRequiresPeriodDates()
    {
        $student = Student::factory()->create();

        $response = $this->post("/api/analytics/learning/students/{$student->id}/calculate-metrics");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'period_start and period_end are required',
            ]);
    }

    public function testStudentPerformanceMetricRelationships()
    {
        $student = Student::factory()->create();
        $metric = StudentPerformanceMetric::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'metric_type' => 'gpa',
            'value' => 3.5,
            'period_type' => 'monthly',
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'trend_percentage' => 5.0,
        ]);

        $this->assertTrue($metric->isImproving());
        $this->assertFalse($metric->isDeclining());
        $this->assertEquals($student->id, $metric->student->id);
    }

    public function testKnowledgeGapCalculations()
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();

        $gap = KnowledgeGap::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'topic_area' => 'Algebra',
            'mastery_level' => 45.0,
            'target_mastery_level' => 70.0,
            'gap_status' => 'identified',
        ]);

        $this->assertEquals(25.0, $gap->gap_percentage);
        $this->assertTrue($gap->isCritical());
        $this->assertFalse($gap->isResolved());
    }

    public function testEarlyWarningSeverityChecks()
    {
        $student = Student::factory()->create();

        $highWarning = EarlyWarning::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $student->id,
            'warning_type' => 'performance_decline',
            'severity' => 'high',
            'description' => 'High severity warning',
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        $this->assertTrue($highWarning->isHighSeverity());
        $this->assertTrue($highWarning->isActive());
    }
}
