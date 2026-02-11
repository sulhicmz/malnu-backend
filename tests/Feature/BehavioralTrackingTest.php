<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BehavioralTrackingService;
use App\Models\BehavioralTracking\BehavioralIncident;
use App\Models\BehavioralTracking\PsychologicalAssessment;
use App\Models\BehavioralTracking\CounselorSession;
use App\Models\BehavioralTracking\BehavioralIntervention;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehavioralTrackingTest extends TestCase
{
    private BehavioralTrackingService $behavioralTrackingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->behavioralTrackingService = $this->app->get(BehavioralTrackingService::class);
    }

    public function test_log_incident(): void
    {
        $student = Student::first();
        $user = User::first();

        if (!$student || !$user) {
            $this->markTestSkipped('No student or user data available');
            return;
        }

        $data = [
            'student_id'     => $student->id,
            'reported_by'    => $user->id,
            'incident_type' => 'Disruption',
            'severity'      => 'medium',
            'description'    => 'Disrupted class during lesson',
            'incident_date' => date('Y-m-d'),
        ];

        $incident = $this->behavioralTrackingService->logIncident($data);

        $this->assertDatabaseHas('behavioral_incidents', [
            'id' => $incident['id'],
            'student_id' => $student->id,
            'reported_by' => $user->id,
            'incident_type' => 'Disruption',
            'severity' => 'medium',
            'description' => 'Disrupted class during lesson',
        ]);
    }

    public function test_create_assessment(): void
    {
        $student = Student::first();
        $user = User::first();

        if (!$student || !$user) {
            $this->markTestSkipped('No student or user data available');
            return;
        }

        $data = [
            'student_id'      => $student->id,
            'assessed_by'     => $user->id,
            'assessment_type' => 'Social Emotional',
            'assessment_data' => ['anxiety' => 3, 'depression' => 2],
            'score'           => 15,
            'max_score'       => 20,
            'notes'           => 'Student showing improvement',
        ];

        $assessment = $this->behavioralTrackingService->createAssessment($data);

        $this->assertDatabaseHas('psychological_assessments', [
            'id' => $assessment['id'],
            'student_id' => $student->id,
            'assessed_by' => $user->id,
            'assessment_type' => 'Social Emotional',
            'score' => 15,
            'max_score' => 20,
        ]);
    }

    public function test_schedule_session(): void
    {
        $student = Student::first();
        $counselor = User::where('id', '!=', $student->user_id)->first();

        if (!$student || !$counselor) {
            $this->markTestSkipped('Insufficient data for session test');
            return;
        }

        $data = [
            'student_id'        => $student->id,
            'counselor_id'       => $counselor->id,
            'session_date'       => date('Y-m-d H:i:s'),
            'duration_minutes'    => 45,
            'session_type'       => 'Individual',
            'session_notes'      => 'Discussed academic progress',
        ];

        $session = $this->behavioralTrackingService->scheduleSession($data);

        $this->assertDatabaseHas('counselor_sessions', [
            'id' => $session['id'],
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'session_type' => 'Individual',
            'duration_minutes' => 45,
        ]);
    }

    public function test_record_intervention(): void
    {
        $student = Student::first();
        $user = User::first();

        if (!$student || !$user) {
            $this->markTestSkipped('No student or user data available');
            return;
        }

        $data = [
            'student_id'       => $student->id,
            'intervention_by'  => $user->id,
            'intervention_type' => 'Behavioral Support Plan',
            'description'       => 'Implement structured reward system',
            'status'           => 'planned',
        ];

        $intervention = $this->behavioralTrackingService->recordIntervention($data);

        $this->assertDatabaseHas('behavioral_interventions', [
            'id' => $intervention['id'],
            'student_id' => $student->id,
            'intervention_by' => $user->id,
            'intervention_type' => 'Behavioral Support Plan',
            'description' => 'Implement structured reward system',
            'status' => 'planned',
        ]);
    }

    public function test_resolve_incident(): void
    {
        $student = Student::first();
        $user = User::first();

        if (!$student || !$user) {
            $this->markTestSkipped('No student or user data available');
            return;
        }

        $incidentData = [
            'student_id'     => $student->id,
            'reported_by'    => $user->id,
            'incident_type' => 'Disruption',
            'severity'      => 'medium',
            'description'    => 'Disrupted class',
            'incident_date' => date('Y-m-d'),
        ];

        $incident = $this->behavioralTrackingService->logIncident($incidentData);
        $this->behavioralTrackingService->resolveIncident($incident['id'], $user->id, 'Counselor spoke with student');

        $this->assertDatabaseHas('behavioral_incidents', [
            'id' => $incident['id'],
            'is_resolved' => true,
            'resolved_at' => null,
        ]);
    }

    public function test_get_at_risk_students(): void
    {
        $student1 = Student::first();
        $student2 = Student::skip(1)->first();

        if (!$student1) {
            $this->markTestSkipped('No student data available');
            return;
        }

        BehavioralIncident::create([
            'student_id' => $student1->id,
            'reported_by' => $student1->user_id,
            'incident_type' => 'Disruption',
            'severity' => 'high',
            'description' => 'Frequent disruptions',
            'incident_date' => date('Y-m-d'),
            'is_resolved' => false,
        ]);

        BehavioralIncident::create([
            'student_id' => $student1->id,
            'reported_by' => $student1->user_id,
            'incident_type' => 'Behavioral Issue',
            'severity' => 'high',
            'description' => 'Inappropriate language',
            'incident_date' => date('Y-m-d'),
            'is_resolved' => false,
        ]);

        $atRiskStudents = $this->behavioralTrackingService->getAtRiskStudents(30);

        $this->assertCount(1, $atRiskStudents);
        $this->assertEquals('high', $atRiskStudents[0]['risk_level']);
    }

    public function test_analyze_trends(): void
    {
        $student1 = Student::first();
        $student2 = Student::skip(1)->first();

        if (!$student1 || !$student2) {
            $this->markTestSkipped('Insufficient student data available');
            return;
        }

        BehavioralIncident::create([
            'student_id' => $student1->id,
            'reported_by' => $student1->user_id,
            'incident_type' => 'Disruption',
            'severity' => 'low',
            'description' => 'Minor disruption',
            'incident_date' => date('Y-m-d'),
            'is_resolved' => true,
        ]);

        BehavioralIncident::create([
            'student_id' => $student1->id,
            'reported_by' => $student1->user_id,
            'incident_type' => 'Disruption',
            'severity' => 'medium',
            'description' => 'Major disruption',
            'incident_date' => date('Y-m-d'),
            'is_resolved' => true,
        ]);

        $trends = $this->behavioralTrackingService->analyzeTrends();

        $this->assertIsArray($trends);
        $this->assertArrayHasKey('total_incidents', $trends);
        $this->assertArrayHasKey('severity_counts', $trends);
        $this->assertArrayHasKey('incident_types', $trends);
        $this->assertEquals(2, $trends['total_incidents']);
    }

    public function test_get_student_history(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        BehavioralIncident::create([
            'student_id' => $student->id,
            'reported_by' => $student->user_id,
            'incident_type' => 'Disruption',
            'severity' => 'medium',
            'description' => 'Class disruption',
            'incident_date' => date('Y-m-d'),
            'is_resolved' => true,
        ]);

        PsychologicalAssessment::create([
            'student_id' => $student->id,
            'assessed_by' => $student->user_id,
            'assessment_type' => 'Social Emotional',
            'score' => 15,
            'max_score' => 20,
            'is_confidential' => true,
            'assessment_date' => date('Y-m-d'),
        ]);

        $history = $this->behavioralTrackingService->getStudentHistory($student->id, 20);

        $this->assertIsArray($history);
        $this->assertArrayHasKey('incidents', $history);
        $this->assertArrayHasKey('assessments', $history);
        $this->assertArrayHasKey('sessions', $history);
        $this->assertArrayHasKey('interventions', $history);
    }
}
