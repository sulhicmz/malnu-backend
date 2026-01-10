<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BehaviorManagementService;
use App\Models\Behavior\Incident;
use App\Models\Behavior\DisciplineAction;
use App\Models\Behavior\InterventionPlan;
use App\Models\Behavior\BehaviorNote;
use App\Models\Behavior\BehaviorCategory;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehaviorManagementTest extends TestCase
{
    private BehaviorManagementService $behaviorService;
    private string $testStudentId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->behaviorService = $this->app->get(BehaviorManagementService::class);
        $this->testStudentId = 'test-student-uuid';
    }

    public function test_create_incident(): void
    {
        $data = [
            'student_id' => $this->testStudentId,
            'category_id' => 'test-category-id',
            'reported_by' => 'test-user-id',
            'title' => 'Disruption in class',
            'description' => 'Student disrupted classroom activities',
            'incident_date' => '2026-01-10',
            'incident_time' => '09:30:00',
            'location' => 'Classroom 3A',
            'severity' => 'medium',
            'evidence' => 'Photos attached',
        ];

        $incident = $this->behaviorService->createIncident($data);

        $this->assertIsArray($incident);
        $this->assertArrayHasKey('id', $incident);
        $this->assertArrayHasKey('student_id', $incident);
        $this->assertEquals('Disruption in class', $incident['title']);
        $this->assertEquals('reported', $incident['status']);
    }

    public function test_create_incident_validation(): void
    {
        $response = $this->post('/api/behavior/incidents', [
            'title' => 'Test incident',
        ]);

        $response->assertStatus(422);
    }

    public function test_get_incidents(): void
    {
        $this->createTestIncident();

        $response = $this->get('/api/behavior/incidents');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'student_id',
                'category_id',
                'reported_by',
                'title',
                'description',
                'incident_date',
                'incident_time',
                'location',
                'severity',
                'status',
                'evidence',
            ],
        ]);
    }

    public function test_get_incidents_with_filters(): void
    {
        $this->createTestIncident();

        $response = $this->get('/api/behavior/incidents', [
            'student_id' => $this->testStudentId,
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'severity' => 'high',
            'status' => 'reported',
        ]);

        $response->assertStatus(200);
    }

    public function test_update_incident(): void
    {
        $incident = $this->createTestIncident();

        $response = $this->put('/api/behavior/incidents/' . $incident['id'], [
            'title' => 'Updated title',
            'severity' => 'high',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated title', $response->json('data')['title']);
        $this->assertEquals('high', $response->json('data')['severity']);
    }

    public function test_create_discipline_action(): void
    {
        $incident = $this->createTestIncident();

        $data = [
            'incident_id' => $incident['id'],
            'assigned_by' => 'test-user-id',
            'action_type' => 'Detention',
            'description' => 'Student assigned detention',
            'action_date' => '2026-01-11',
        ];

        $action = $this->behaviorService->createDisciplineAction($data);

        $this->assertIsArray($action);
        $this->assertArrayHasKey('id', $action);
        $this->assertEquals('Detention', $action['action_type']);
        $this->assertEquals('pending', $action['status']);
    }

    public function test_get_discipline_actions(): void
    {
        $incident = $this->createTestIncident();

        $response = $this->get('/api/behavior/discipline-actions');

        $response->assertStatus(200);
        $this->assertJsonStructure([
            '*' => [
                'id',
                'incident_id',
                'assigned_by',
                'action_type',
                'description',
                'action_date',
                'status',
                'outcome',
            ],
        ]);
    }

    public function test_update_discipline_action(): void
    {
        $incident = $this->createTestIncident();
        $action = $this->behaviorService->createDisciplineAction([
            'incident_id' => $incident['id'],
            'assigned_by' => 'test-user-id',
            'action_type' => 'Detention',
            'action_date' => '2026-01-11',
        ]);

        $response = $this->put('/api/behavior/discipline-actions/' . $action['id'], [
            'status' => 'completed',
            'outcome' => 'Student attended detention',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $response->json('data')['status']);
    }

    public function test_create_intervention_plan(): void
    {
        $data = [
            'student_id' => $this->testStudentId,
            'goals' => 'Improve classroom behavior',
            'strategies' => 'Daily check-ins',
            'timeline' => 'Week 1: Initial meeting',
            'start_date' => '2026-01-15',
            'end_date' => '2026-02-15',
        ];

        $plan = $this->behaviorService->createInterventionPlan($data);

        $this->assertIsArray($plan);
        $this->assertArrayHasKey('id', $plan);
        $this->assertEquals('active', $plan['status']);
        $this->assertEquals('Improve classroom behavior', $plan['goals']);
    }

    public function test_get_intervention_plans(): void
    {
        $this->createTestIncident();
        $this->createTestInterventionPlan();

        $response = $this->get('/api/behavior/intervention-plans');

        $response->assertStatus(200);
        $this->assertJsonStructure([
            '*' => [
                'id',
                'student_id',
                'incident_id',
                'goals',
                'strategies',
                'timeline',
                'start_date',
                'end_date',
                'status',
            ],
        ]);
    }

    public function test_update_intervention_plan(): void
    {
        $plan = $this->createTestInterventionPlan();

        $response = $this->put('/api/behavior/intervention-plans/' . $plan['id'], [
            'status' => 'completed',
            'evaluation' => 'Student showing improvement',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $response->json('data')['status']);
    }

    public function test_create_behavior_note(): void
    {
        $data = [
            'student_id' => $this->testStudentId,
            'note_type' => 'observation',
            'content' => 'Student participated actively',
            'note_date' => '2026-01-10',
            'is_positive' => true,
        ];

        $note = $this->behaviorService->createBehaviorNote($data);

        $this->assertIsArray($note);
        $this->assertArrayHasKey('id', $note);
        $this->assertTrue($note['is_positive']);
    }

    public function test_get_behavior_notes(): void
    {
        $response = $this->get('/api/behavior/notes');

        $response->assertStatus(200);
        $this->assertJsonStructure([
            '*' => [
                'id',
                'student_id',
                'noted_by',
                'note_type',
                'content',
                'note_date',
                'is_positive',
            ],
        ]);
    }

    public function test_get_behavior_categories(): void
    {
        $response = $this->get('/api/behavior/categories');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    private function createTestIncident(): array
    {
        return $this->behaviorService->createIncident([
            'student_id' => $this->testStudentId,
            'category_id' => 'test-category-id',
            'reported_by' => 'test-user-id',
            'title' => 'Test incident',
            'description' => 'Test description',
            'incident_date' => '2026-01-10',
            'incident_time' => '09:30:00',
            'location' => 'Test location',
            'severity' => 'low',
        ]);
    }

    private function createTestInterventionPlan(): array
    {
        $incident = $this->createTestIncident();

        return $this->behaviorService->createInterventionPlan([
            'student_id' => $this->testStudentId,
            'incident_id' => $incident['id'],
            'goals' => 'Test goals',
            'strategies' => 'Test strategies',
            'timeline' => 'Test timeline',
            'start_date' => '2026-01-15',
            'end_date' => '2026-02-15',
        ]);
    }
}
