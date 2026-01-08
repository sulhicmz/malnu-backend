<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HealthManagement\Allergy;
use App\Models\HealthManagement\EmergencyContact;
use App\Models\HealthManagement\HealthAlert;
use App\Models\HealthManagement\HealthRecord;
use App\Models\HealthManagement\HealthScreening;
use App\Models\HealthManagement\Immunization;
use App\Models\HealthManagement\Medication;
use App\Models\HealthManagement\MedicalIncident;
use App\Models\HealthManagement\NurseVisit;
use App\Models\SchoolManagement\Student;
use App\Services\HealthManagementService;
use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

class HealthManagementTest extends TestCase
{
    private Client $client;

    private HealthManagementService $healthService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
        $this->healthService = $this->getContainer()->get(HealthManagementService::class);
    }

    public function test_create_health_record()
    {
        $student = Student::factory()->create();
        
        $data = [
            'student_id' => $student->id,
            'blood_type' => 'A+',
            'medical_history' => 'No significant history',
        ];

        $record = $this->healthService->createHealthRecord($data);

        $this->assertIsArray($record);
        $this->assertEquals($student->id, $record['student_id']);
        $this->assertEquals('A+', $record['blood_type']);
    }

    public function test_get_health_record()
    {
        $student = Student::factory()->create();
        $this->healthService->createHealthRecord([
            'student_id' => $student->id,
            'blood_type' => 'B+',
        ]);

        $record = $this->healthService->getHealthRecord($student->id);

        $this->assertNotNull($record);
        $this->assertEquals('B+', $record->blood_type);
    }

    public function test_create_medication()
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'medication_name' => 'Advil',
            'dosage' => '10mg',
            'frequency' => 'Every 6 hours',
            'administration_method' => 'oral',
            'start_date' => '2024-01-01',
            'parent_consent' => true,
        ];

        $medication = $this->healthService->createMedication($data);

        $this->assertIsArray($medication);
        $this->assertEquals('Advil', $medication['medication_name']);
        $this->assertEquals('active', $medication['status']);
    }

    public function test_get_student_medications()
    {
        $student = Student::factory()->create();

        $this->healthService->createMedication([
            'student_id' => $student->id,
            'medication_name' => 'Tylenol',
            'dosage' => '500mg',
            'frequency' => 'Every 4 hours',
            'start_date' => '2024-01-01',
        ]);

        $medications = $this->healthService->getStudentMedications($student->id);

        $this->assertIsArray($medications);
        $this->assertCount(1, $medications);
    }

    public function test_create_immunization()
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'vaccine_name' => 'MMR',
            'vaccine_type' => 'Live Attenuated',
            'manufacturer' => 'Merck',
            'administration_date' => '2024-01-01',
            'status' => 'completed',
        ];

        $immunization = $this->healthService->createImmunization($data);

        $this->assertIsArray($immunization);
        $this->assertEquals('MMR', $immunization['vaccine_name']);
        $this->assertEquals('completed', $immunization['status']);
    }

    public function test_get_immunization_compliance()
    {
        $student = Student::factory()->create();

        $this->healthService->createImmunization([
            'student_id' => $student->id,
            'vaccine_name' => 'MMR',
            'administration_date' => '2024-01-01',
            'status' => 'completed',
        ]);

        $compliance = $this->healthService->getImmunizationCompliance($student->id);

        $this->assertIsArray($compliance);
        $this->assertArrayHasKey('total', $compliance);
        $this->assertArrayHasKey('compliance_rate', $compliance);
        $this->assertEquals(100, $compliance['compliance_rate']);
    }

    public function test_create_allergy()
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'allergen' => 'Peanuts',
            'allergy_type' => 'food',
            'severity' => 'life_threatening',
            'reactions' => 'Anaphylaxis',
            'requires_epipen' => true,
        ];

        $allergy = $this->healthService->createAllergy($data);

        $this->assertIsArray($allergy);
        $this->assertEquals('Peanuts', $allergy['allergen']);
        $this->assertEquals('life_threatening', $allergy['severity']);
        $this->assertTrue($allergy['requires_epipen']);
    }

    public function test_get_student_allergies()
    {
        $student = Student::factory()->create();

        $this->healthService->createAllergy([
            'student_id' => $student->id,
            'allergen' => 'Dust',
            'allergy_type' => 'environmental',
            'severity' => 'moderate',
        ]);

        $allergies = $this->healthService->getStudentAllergies($student->id);

        $this->assertIsArray($allergies);
        $this->assertCount(1, $allergies);
    }

    public function test_get_severe_allergies_alert()
    {
        $student = Student::factory()->create();

        $this->healthService->createAllergy([
            'student_id' => $student->id,
            'allergen' => 'Peanuts',
            'allergy_type' => 'food',
            'severity' => 'severe',
            'reactions' => 'Hives, swelling',
            'emergency_protocol' => 'Administer EpiPen',
            'requires_epipen' => true,
        ]);

        $severeAllergies = $this->healthService->getSevereAllergiesAlert($student->id);

        $this->assertIsArray($severeAllergies);
        $this->assertCount(1, $severeAllergies);
        $this->assertEquals('Peanuts', $severeAllergies[0]['allergen']);
        $this->assertArrayHasKey('emergency_protocol', $severeAllergies[0]);
    }

    public function test_create_emergency_contact()
    {
        $student = Student::factory()->create();

        $data = [
            'student_id' => $student->id,
            'full_name' => 'John Doe',
            'relationship' => 'Father',
            'phone' => '5551234567',
            'primary_contact' => true,
            'authorized_pickup' => true,
            'medical_consent' => true,
        ];

        $contact = $this->healthService->createEmergencyContact($data);

        $this->assertIsArray($contact);
        $this->assertEquals('John Doe', $contact['full_name']);
        $this->assertTrue($contact['primary_contact']);
    }

    public function test_get_emergency_contacts()
    {
        $student = Student::factory()->create();

        $this->healthService->createEmergencyContact([
            'student_id' => $student->id,
            'full_name' => 'Jane Doe',
            'relationship' => 'Mother',
            'phone' => '5559876543',
        ]);

        $contacts = $this->healthService->getEmergencyContacts($student->id);

        $this->assertIsArray($contacts);
        $this->assertCount(1, $contacts);
    }

    public function test_create_medical_incident()
    {
        $student = Student::factory()->create();
        $user = \App\Models\User::factory()->create();

        $data = [
            'student_id' => $student->id,
            'incident_date' => now()->toDateTimeString(),
            'incident_type' => 'Fall',
            'description' => 'Student fell on playground',
            'severity' => 'moderate',
            'status' => 'open',
            'created_by' => $user->id,
        ];

        $incident = $this->healthService->createMedicalIncident($data);

        $this->assertIsArray($incident);
        $this->assertEquals('Fall', $incident['incident_type']);
        $this->assertEquals('moderate', $incident['severity']);
    }

    public function test_get_medical_incidents()
    {
        $student = Student::factory()->create();

        $this->healthService->createMedicalIncident([
            'student_id' => $student->id,
            'incident_date' => now()->toDateTimeString(),
            'incident_type' => 'Illness',
            'description' => 'Student reported feeling unwell',
            'severity' => 'mild',
            'status' => 'resolved',
        ]);

        $incidents = $this->healthService->getMedicalIncidents($student->id);

        $this->assertIsArray($incidents);
        $this->assertCount(1, $incidents);
    }

    public function test_get_health_summary()
    {
        $summary = $this->healthService->getHealthSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_students', $summary);
        $this->assertArrayHasKey('active_medications', $summary);
        $this->assertArrayHasKey('completed_immunizations', $summary);
        $this->assertArrayHasKey('students_with_allergies', $summary);
    }

    public function test_generate_health_report()
    {
        $student = Student::factory()->create();
        $this->healthService->createHealthRecord([
            'student_id' => $student->id,
            'blood_type' => 'O+',
        ]);

        $report = $this->healthService->generateHealthReport($student->id);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('student', $report);
        $this->assertArrayHasKey('health_record', $report);
        $this->assertArrayHasKey('medications', $report);
        $this->assertArrayHasKey('immunizations', $report);
        $this->assertArrayHasKey('allergies', $report);
        $this->assertArrayHasKey('emergency_contacts', $report);
    }
}
