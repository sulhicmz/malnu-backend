<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HealthRecord;
use App\Models\Immunization;
use App\Models\Medication;
use App\Models\Allergy;
use App\Models\HealthScreening;
use App\Models\HealthEmergency;
use App\Models\MedicalIncident;
use App\Models\NurseVisit;
use App\Models\HealthAlert;

class HealthManagementService
{

    public function createRecord(array $data): HealthRecord
    {
        return HealthRecord::create([
            'student_id' => $data['student_id'],
            'blood_type' => $data['blood_type'] ?? null,
            'chronic_conditions' => $data['chronic_conditions'] ?? null,
            'dietary_restrictions' => $data['dietary_restrictions'] ?? null,
            'family_medical_history' => $data['family_medical_history'] ?? null,
            'physical_disabilities' => $data['physical_disabilities'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? $this->getCurrentUserId(),
        ]);
    }

    public function updateRecord(int $id, array $data): HealthRecord
    {
        $record = HealthRecord::findOrFail($id);
        
        $record->fill([
            'blood_type' => $data['blood_type'] ?? $record->blood_type,
            'chronic_conditions' => $data['chronic_conditions'] ?? $record->chronic_conditions,
            'dietary_restrictions' => $data['dietary_restrictions'] ?? $record->dietary_restrictions,
            'family_medical_history' => $data['family_medical_history'] ?? $record->family_medical_history,
            'physical_disabilities' => $data['physical_disabilities'] ?? $record->physical_disabilities,
            'notes' => $data['notes'] ?? $record->notes,
            'updated_by' => $data['updated_by'] ?? $this->getCurrentUserId(),
        ])->save();

        return $record;
    }

    public function getStudentRecords(int $studentId): array
    {
        return HealthRecord::where('student_id', $studentId)
            ->with(['immunizations', 'medications', 'allergies', 'healthScreenings', 'emergencyContacts', 'medicalIncidents', 'nurseVisits', 'healthAlerts'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function createImmunization(array $data): Immunization
    {
        return Immunization::create([
            'health_record_id' => $data['health_record_id'],
            'vaccine_name' => $data['vaccine_name'],
            'date_administered' => $data['date_administered'],
            'next_due_date' => $data['next_due_date'] ?? null,
            'administered_by' => $data['administered_by'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getStudentImmunizations(int $studentId): array
    {
        return Immunization::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->orderBy('date_administered', 'desc')
            ->get()
            ->toArray();
    }

    public function createEmergency(array $data): HealthEmergency
    {
        return HealthEmergency::create([
            'health_record_id' => $data['health_record_id'],
            'contact_name' => $data['contact_name'],
            'relationship' => $data['relationship'],
            'phone' => $data['phone'],
            'is_primary' => $data['is_primary'] ?? false,
        ]);
    }

    public function getStudentEmergencies(int $studentId): array
    {
        return HealthEmergency::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function createMedication(array $data): Medication
    {
        return Medication::create([
            'health_record_id' => $data['health_record_id'],
            'medication_name' => $data['medication_name'],
            'dosage' => $data['dosage'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'administered_by' => $data['administered_by'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function getStudentMedications(int $studentId): array
    {
        return Medication::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->orderBy('start_date', 'desc')
            ->get()
            ->toArray();
    }

    public function createScreening(array $data): HealthScreening
    {
        return HealthScreening::create([
            'health_record_id' => $data['health_record_id'],
            'screening_type' => $data['screening_type'],
            'screening_date' => $data['screening_date'],
            'results' => $data['results'] ?? null,
            'notes' => $data['notes'] ?? null,
            'conducted_by' => $data['conducted_by'] ?? null,
        ]);
    }

    public function getStudentScreenings(int $studentId): array
    {
        return HealthScreening::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->orderBy('screening_date', 'desc')
            ->get()
            ->toArray();
    }

    public function createIncident(array $data): MedicalIncident
    {
        return MedicalIncident::create([
            'health_record_id' => $data['health_record_id'] ?? null,
            'incident_date' => $data['incident_date'],
            'incident_type' => $data['incident_type'],
            'description' => $data['description'],
            'severity' => $data['severity'] ?? 'moderate',
            'reported_by' => $data['reported_by'] ?? $this->getCurrentUserId(),
            'action_taken' => $data['action_taken'] ?? null,
            'status' => $data['status'] ?? 'open',
        ]);
    }

    public function getStudentIncidents(int $studentId): array
    {
        return MedicalIncident::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->orderBy('incident_date', 'desc')
            ->get()
            ->toArray();
    }

    public function createAlert(array $data): HealthAlert
    {
        return HealthAlert::create([
            'health_record_id' => $data['health_record_id'],
            'alert_type' => $data['alert_type'],
            'description' => $data['description'],
            'severity' => $data['severity'] ?? 'moderate',
            'is_active' => $data['is_active'] ?? true,
            'notified_parent' => $data['notified_parent'] ?? false,
            'created_by' => $data['created_by'] ?? $this->getCurrentUserId(),
        ]);
    }

    public function updateAlert(int $id, array $data): HealthAlert
    {
        $alert = HealthAlert::findOrFail($id);
        
        $alert->fill([
            'alert_type' => $data['alert_type'] ?? $alert->alert_type,
            'description' => $data['description'] ?? $alert->description,
            'severity' => $data['severity'] ?? $alert->severity,
            'is_active' => $data['is_active'] ?? $alert->is_active,
            'notified_parent' => $data['notified_parent'] ?? $alert->notified_parent,
        ])->save();

        return $alert;
    }

    public function deactivateAlert(int $id): HealthAlert
    {
        $alert = HealthAlert::findOrFail($id);
        $alert->update(['is_active' => false]);
        
        return $alert;
    }

    public function getStudentAlerts(int $studentId): array
    {
        return HealthAlert::whereHas('healthRecord', fn($q) => $q->where('student_id', $studentId))
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
