<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;
use App\Services\HealthManagementService;

class HealthManagementTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function testCreateHealthRecord()
    {
        $data = [
            'student_id' => 'test-student-id',
            'blood_type' => 'A+',
            'chronic_conditions' => 'Asthma',
            'dietary_restrictions' => 'Gluten free',
            'family_medical_history' => 'Father has diabetes',
            'physical_disabilities' => null,
            'notes' => 'Initial health record',
        ];

        $response = $this->client->post('/api/health/records', $data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->json('data'));
    }

    public function testGetStudentRecords()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/records/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->json('data'));
    }

    public function testCreateImmunization()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'vaccine_name' => 'MMR',
            'date_administered' => '2026-01-15',
            'next_due_date' => '2027-01-15',
            'administered_by' => 'Dr. Smith',
            'notes' => 'No adverse reactions',
        ];

        $response = $this->client->post('/api/health/immunizations', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentImmunizations()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/immunizations/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateEmergency()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'contact_name' => 'John Doe',
            'relationship' => 'Father',
            'phone' => '+1234567890',
            'is_primary' => true,
        ];

        $response = $this->client->post('/api/health/emergencies', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentEmergencies()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/emergencies/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateMedication()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'medication_name' => 'Albuterol',
            'dosage' => '10mg',
            'frequency' => 'Twice daily',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-01',
            'administered_by' => 'School Nurse',
            'notes' => 'Take with food',
        ];

        $response = $this->client->post('/api/health/medications', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentMedications()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/medications/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateScreening()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'screening_type' => 'Vision',
            'screening_date' => '2026-01-15',
            'results' => '20/20 vision',
            'notes' => 'No glasses needed',
            'conducted_by' => 'School Nurse',
        ];

        $response = $this->client->post('/api/health/screenings', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentScreenings()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/screenings/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateIncident()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'incident_date' => '2026-01-15',
            'incident_type' => 'Fall',
            'description' => 'Student fell during recess',
            'severity' => 'moderate',
            'action_taken' => 'Nurse examined, no injuries',
            'status' => 'closed',
        ];

        $response = $this->client->post('/api/health/incidents', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentIncidents()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/incidents/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateAlert()
    {
        $data = [
            'health_record_id' => 'test-health-record-id',
            'alert_type' => 'Allergy',
            'description' => 'Peanut allergy - severe',
            'severity' => 'critical',
            'is_active' => true,
            'notified_parent' => false,
        ];

        $response = $this->client->post('/api/health/alerts', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateAlert()
    {
        $alertId = 1;
        $data = [
            'is_active' => false,
            'notified_parent' => true,
        ];

        $response = $this->client->put("/api/health/alerts/$alertId", $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeactivateAlert()
    {
        $alertId = 1;

        $response = $this->client->post("/api/health/alerts/$alertId/deactivate");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentAlerts()
    {
        $studentId = 'test-student-id';

        $response = $this->client->get("/api/health/alerts/$studentId");

        $this->assertEquals(200, $response->getStatusCode());
    }
}
