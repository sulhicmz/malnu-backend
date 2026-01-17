<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\DbConnection\Db;
use Hyperf\Testing\Client;
use Hyperf\Testing\TestCase;
use App\Models\Behavior\BehaviorIncident;
use App\Models\Behavior\BehaviorCategory;
use App\Models\Behavior\DisciplineAction;
use App\Models\Behavior\InterventionPlan;
use App\Models\Behavior\BehaviorNote;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehaviorManagementTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function test_create_behavior_incident()
    {
        $category = BehaviorCategory::create([
            'name' => 'Disruptive Behavior',
            'description' => 'Disruptive in class',
            'type' => 'negative',
            'severity_level' => 3,
            'is_active' => true,
        ]);

        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $response = $this->client->post('/api/behavior/incidents', [
            'json' => [
                'student_id' => $student->id,
                'behavior_category_id' => $category->id,
                'severity' => 'moderate',
                'description' => 'Student was disruptive during math class',
                'incident_date' => '2026-01-17',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('success', json_decode($response->getBody(), true));
    }

    public function test_get_behavior_incidents()
    {
        $response = $this->client->get('/api/behavior/incidents');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_get_incidents_by_student()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567891',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $response = $this->client->get('/api/behavior/incidents', [
            'query' => ['student_id' => $student->id],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_create_discipline_action()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567892',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $incident = BehaviorIncident::create([
            'student_id' => $student->id,
            'behavior_category_id' => Db::raw('(UUID())'),
            'severity' => 'severe',
            'description' => 'Fighting in class',
            'incident_date' => '2026-01-17',
            'is_resolved' => false,
            'created_by' => Db::raw('(UUID())'),
        ]);

        $response = $this->client->post('/api/behavior/discipline-actions', [
            'json' => [
                'incident_id' => $incident->id,
                'action_type' => 'detention',
                'duration_days' => 3,
                'start_date' => '2026-01-20',
                'end_date' => '2026-01-23',
                'description' => '3-day detention for fighting',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('success', json_decode($response->getBody(), true));
    }

    public function test_create_intervention_plan()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567893',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $response = $this->client->post('/api/behavior/intervention-plans', [
            'json' => [
                'student_id' => $student->id,
                'title' => 'Behavioral Improvement Plan',
                'description' => 'Weekly counseling sessions for 3 months',
                'goals' => 'Improve classroom behavior',
                'strategies' => 'Positive reinforcement, clear expectations',
                'start_date' => '2026-01-20',
                'end_date' => '2026-04-20',
            ],
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('success', json_decode($response->getBody(), true));
    }

    public function test_get_student_behavior_history()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567894',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $response = $this->client->get('/api/behavior/student/' . $student->id . '/history');

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('incidents', $data);
        $this->assertArrayHasKey('discipline_actions', $data);
        $this->assertArrayHasKey('intervention_plans', $data);
    }

    public function test_get_behavior_reports()
    {
        $response = $this->client->get('/api/behavior/reports', [
            'query' => [
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-31',
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('total_incidents', $data);
        $this->assertArrayHasKey('resolution_rate', $data);
    }

    public function test_incident_requires_student_id()
    {
        $category = BehaviorCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'type' => 'negative',
            'severity_level' => 2,
            'is_active' => true,
        ]);

        $response = $this->client->post('/api/behavior/incidents', [
            'json' => [
                'behavior_category_id' => $category->id,
                'severity' => 'moderate',
                'description' => 'Test incident',
                'incident_date' => '2026-01-17',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_incident_requires_valid_severity()
    {
        $student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567895',
            'class_id' => Db::raw('(UUID())'),
            'enrollment_year' => '2026',
            'status' => 'active',
        ]);

        $category = BehaviorCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'type' => 'negative',
            'severity_level' => 2,
            'is_active' => true,
        ]);

        $response = $this->client->post('/api/behavior/incidents', [
            'json' => [
                'student_id' => $student->id,
                'behavior_category_id' => $category->id,
                'severity' => 'invalid_severity',
                'description' => 'Test incident',
                'incident_date' => '2026-01-17',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_discipline_action_requires_incident_id()
    {
        $response = $this->client->post('/api/behavior/discipline-actions', [
            'json' => [
                'action_type' => 'detention',
                'duration_days' => 3,
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_intervention_plan_requires_student_id()
    {
        $response = $this->client->post('/api/behavior/intervention-plans', [
            'json' => [
                'title' => 'Test Plan',
                'description' => 'Test description',
            ],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}
