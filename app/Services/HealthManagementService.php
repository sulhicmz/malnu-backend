<?php

declare(strict_types=1);

namespace App\Services;

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
use App\Models\User;
use Hyperf\DbConnection\Db;

class HealthManagementService
{
    public function getHealthRecord(string $studentId): ?HealthRecord
    {
        return HealthRecord::with(['medications', 'immunizations', 'allergies', 'healthScreenings'])
            ->where('student_id', $studentId)
            ->first();
    }

    public function createHealthRecord(array $data): HealthRecord
    {
        return HealthRecord::create($data);
    }

    public function updateHealthRecord(string $id, array $data): HealthRecord
    {
        $record = HealthRecord::findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function deleteHealthRecord(string $id): bool
    {
        return HealthRecord::findOrFail($id)->delete();
    }

    public function createMedication(array $data): Medication
    {
        return Medication::create($data);
    }

    public function updateMedication(string $id, array $data): Medication
    {
        $medication = Medication::findOrFail($id);
        $medication->update($data);
        return $medication->fresh();
    }

    public function deleteMedication(string $id): bool
    {
        return Medication::findOrFail($id)->delete();
    }

    public function getStudentMedications(string $studentId, ?string $status = null)
    {
        $query = Medication::where('student_id', $studentId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function createImmunization(array $data): Immunization
    {
        return Immunization::create($data);
    }

    public function updateImmunization(string $id, array $data): Immunization
    {
        $immunization = Immunization::findOrFail($id);
        $immunization->update($data);
        return $immunization->fresh();
    }

    public function deleteImmunization(string $id): bool
    {
        return Immunization::findOrFail($id)->delete();
    }

    public function getStudentImmunizations(string $studentId, ?string $status = null)
    {
        $query = Immunization::where('student_id', $studentId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('administration_date', 'desc')->get();
    }

    public function getImmunizationCompliance(string $studentId): array
    {
        $allImmunizations = Immunization::where('student_id', $studentId)->get();
        
        $overdue = $allImmunizations->filter(function ($imm) {
            return $imm->isOverdue();
        });

        $completed = $allImmunizations->filter(function ($imm) {
            return $imm->status === 'completed';
        });

        $due = $allImmunizations->filter(function ($imm) {
            return $imm->status === 'due';
        });

        return [
            'total' => $allImmunizations->count(),
            'completed' => $completed->count(),
            'overdue' => $overdue->count(),
            'due' => $due->count(),
            'compliance_rate' => $allImmunizations->count() > 0 
                ? round(($completed->count() / $allImmunizations->count()) * 100, 2) 
                : 100,
            'overdue_immunizations' => $overdue->values(),
        ];
    }

    public function createAllergy(array $data): Allergy
    {
        return Allergy::create($data);
    }

    public function updateAllergy(string $id, array $data): Allergy
    {
        $allergy = Allergy::findOrFail($id);
        $allergy->update($data);
        return $allergy->fresh();
    }

    public function deleteAllergy(string $id): bool
    {
        return Allergy::findOrFail($id)->delete();
    }

    public function getStudentAllergies(string $studentId, bool $severeOnly = false)
    {
        $query = Allergy::where('student_id', $studentId);

        if ($severeOnly) {
            $query->whereIn('severity', ['severe', 'life_threatening']);
        }

        return $query->orderBy('severity', 'desc')->get();
    }

    public function getSevereAllergiesAlert(string $studentId): array
    {
        $severeAllergies = $this->getStudentAllergies($studentId, true);
        
        return $severeAllergies->map(function ($allergy) {
            return [
                'allergen' => $allergy->allergen,
                'severity' => $allergy->severity,
                'reactions' => $allergy->reactions,
                'emergency_protocol' => $allergy->emergency_protocol,
                'requires_epipen' => $allergy->requires_epipen,
            ];
        })->toArray();
    }

    public function createHealthScreening(array $data): HealthScreening
    {
        return HealthScreening::create($data);
    }

    public function updateHealthScreening(string $id, array $data): HealthScreening
    {
        $screening = HealthScreening::findOrFail($id);
        $screening->update($data);
        return $screening->fresh();
    }

    public function deleteHealthScreening(string $id): bool
    {
        return HealthScreening::findOrFail($id)->delete();
    }

    public function getStudentScreenings(string $studentId, ?string $screeningType = null, ?string $status = null)
    {
        $query = HealthScreening::where('student_id', $studentId);

        if ($screeningType) {
            $query->where('screening_type', $screeningType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('screening_date', 'desc')->get();
    }

    public function getScreeningsNeedingFollowUp(string $studentId = null)
    {
        $query = HealthScreening::where('status', 'needs_follow_up');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        return $query->orderBy('follow_up_date', 'asc')->get();
    }

    public function createEmergencyContact(array $data): EmergencyContact
    {
        if ($data['primary_contact'] ?? false) {
            EmergencyContact::where('student_id', $data['student_id'])
                ->update(['primary_contact' => false]);
        }

        return EmergencyContact::create($data);
    }

    public function updateEmergencyContact(string $id, array $data): EmergencyContact
    {
        $contact = EmergencyContact::findOrFail($id);
        
        if (($data['primary_contact'] ?? false) && !$contact->primary_contact) {
            EmergencyContact::where('student_id', $contact->student_id)
                ->where('id', '!=', $id)
                ->update(['primary_contact' => false]);
        }

        $contact->update($data);
        return $contact->fresh();
    }

    public function deleteEmergencyContact(string $id): bool
    {
        return EmergencyContact::findOrFail($id)->delete();
    }

    public function getEmergencyContacts(string $studentId)
    {
        return EmergencyContact::where('student_id', $studentId)
            ->orderBy('primary_contact', 'desc')
            ->orderBy('full_name', 'asc')
            ->get();
    }

    public function getPrimaryEmergencyContact(string $studentId): ?EmergencyContact
    {
        return EmergencyContact::where('student_id', $studentId)
            ->primary()
            ->first();
    }

    public function createMedicalIncident(array $data): MedicalIncident
    {
        return Db::transaction(function () use ($data) {
            $incident = MedicalIncident::create($data);

            if ($incident->isSevere()) {
                $this->createHealthAlert([
                    'student_id' => $data['student_id'],
                    'alert_type' => 'medical_incident',
                    'title' => 'Severe Medical Incident Reported',
                    'message' => "A severe medical incident has been reported on {$data['incident_date']}",
                    'priority' => 'critical',
                    'alert_date' => now(),
                    'status' => 'pending',
                    'created_by' => $data['created_by'] ?? null,
                ]);
            }

            return $incident;
        });
    }

    public function updateMedicalIncident(string $id, array $data): MedicalIncident
    {
        $incident = MedicalIncident::findOrFail($id);
        $incident->update($data);
        return $incident->fresh();
    }

    public function deleteMedicalIncident(string $id): bool
    {
        return MedicalIncident::findOrFail($id)->delete();
    }

    public function getMedicalIncidents(?string $studentId = null, ?string $severity = null, ?string $status = null)
    {
        $query = MedicalIncident::with(['reportedBy', 'treatedBy']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('incident_date', 'desc')->get();
    }

    public function getOpenIncidents(?string $studentId = null)
    {
        $query = MedicalIncident::where('status', 'open');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        return $query->orderBy('incident_date', 'desc')->get();
    }

    public function createNurseVisit(array $data): NurseVisit
    {
        return NurseVisit::create($data);
    }

    public function updateNurseVisit(string $id, array $data): NurseVisit
    {
        $visit = NurseVisit::findOrFail($id);
        $visit->update($data);
        return $visit->fresh();
    }

    public function deleteNurseVisit(string $id): bool
    {
        return NurseVisit::findOrFail($id)->delete();
    }

    public function getNurseVisits(?string $studentId = null, ?string $nurseId = null, ?string $startDate = null, ?string $endDate = null)
    {
        $query = NurseVisit::with(['nurse', 'student']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($nurseId) {
            $query->where('nurse_id', $nurseId);
        }

        if ($startDate) {
            $query->where('visit_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('visit_date', '<=', $endDate);
        }

        return $query->orderBy('visit_date', 'desc')->get();
    }

    public function createHealthAlert(array $data): HealthAlert
    {
        return HealthAlert::create($data);
    }

    public function updateHealthAlert(string $id, array $data): HealthAlert
    {
        $alert = HealthAlert::findOrFail($id);
        $alert->update($data);
        return $alert->fresh();
    }

    public function deleteHealthAlert(string $id): bool
    {
        return HealthAlert::findOrFail($id)->delete();
    }

    public function getHealthAlerts(?string $studentId = null, ?string $status = null, ?string $priority = null)
    {
        $query = HealthAlert::with(['student']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        return $query->orderBy('alert_date', 'desc')->get();
    }

    public function getPendingAlerts(): array
    {
        return HealthAlert::where('status', 'pending')
            ->where('due_date', '<', now())
            ->with(['student'])
            ->get()
            ->toArray();
    }

    public function getCriticalAlerts(): array
    {
        return HealthAlert::where('priority', 'critical')
            ->where('status', '!=', 'resolved')
            ->with(['student'])
            ->get()
            ->toArray();
    }

    public function generateHealthReport(string $studentId): array
    {
        $student = Student::with('user')->findOrFail($studentId);
        $healthRecord = $this->getHealthRecord($studentId);
        
        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? '',
                'nisn' => $student->nisn,
            ],
            'health_record' => $healthRecord,
            'medications' => $this->getStudentMedications($studentId),
            'immunizations' => $this->getStudentImmunizations($studentId),
            'immunization_compliance' => $this->getImmunizationCompliance($studentId),
            'allergies' => $this->getStudentAllergies($studentId),
            'severe_allergies' => $this->getSevereAllergiesAlert($studentId),
            'emergency_contacts' => $this->getEmergencyContacts($studentId),
            'health_screenings' => $this->getStudentScreenings($studentId),
            'medical_incidents' => $this->getMedicalIncidents($studentId),
            'nurse_visits' => $this->getNurseVisits($studentId),
        ];
    }

    public function getHealthSummary(): array
    {
        return [
            'total_students' => Student::count(),
            'students_with_health_records' => HealthRecord::distinct('student_id')->count(),
            'active_medications' => Medication::active()->count(),
            'completed_immunizations' => Immunization::where('status', 'completed')->count(),
            'overdue_immunizations' => Immunization::where('status', 'overdue')->count(),
            'students_with_allergies' => Allergy::distinct('student_id')->count(),
            'severe_allergies' => Allergy::whereIn('severity', ['severe', 'life_threatening'])->count(),
            'medical_incidents_this_month' => MedicalIncident::where('incident_date', '>=', now()->startOfMonth())->count(),
            'open_incidents' => MedicalIncident::where('status', 'open')->count(),
            'nurse_visits_this_month' => NurseVisit::where('visit_date', '>=', now()->startOfMonth())->count(),
            'pending_health_alerts' => HealthAlert::where('status', 'pending')->count(),
            'critical_health_alerts' => HealthAlert::where('priority', 'critical')->where('status', '!=', 'resolved')->count(),
        ];
    }
}
