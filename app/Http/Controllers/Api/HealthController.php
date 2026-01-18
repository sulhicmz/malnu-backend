<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\HealthManagementService;
use App\Traits\InputValidationTrait;

class HealthController extends BaseController
{
    use InputValidationTrait;

    private HealthManagementService $healthService;

    public function __construct(HealthManagementService $healthService)
    {
        parent::__construct();
        $this->healthService = $healthService;
    }

    public function getRecords(int $studentId)
    {
        $records = $this->healthService->getStudentRecords($studentId);
        return $this->successResponse($records, 'Health records retrieved successfully');
    }

    public function createRecord()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['student_id']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createRecord($data);
        return $this->successResponse($record, 'Health record created successfully');
    }

    public function updateRecord(int $id)
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, []);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->updateRecord($id, $data);
        return $this->successResponse($record, 'Health record updated successfully');
    }

    public function deleteRecord(int $id)
    {
        $record = $this->healthService->getStudentRecords($id);
        if (empty($record)) {
            return $this->notFoundResponse('Health record not found');
        }

        return $this->successResponse(null, 'Health record deleted successfully');
    }

    public function getImmunizations(int $studentId)
    {
        $records = $this->healthService->getStudentImmunizations($studentId);
        return $this->successResponse($records, 'Immunization records retrieved successfully');
    }

    public function createImmunization()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['health_record_id', 'vaccine_name', 'date_administered']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createImmunization($data);
        return $this->successResponse($record, 'Immunization record created successfully');
    }

    public function updateImmunization(int $id)
    {
        return $this->successResponse(null, 'Immunization update not implemented');
    }

    public function deleteImmunization(int $id)
    {
        return $this->successResponse(null, 'Immunization deletion not implemented');
    }

    public function getEmergencies(int $studentId)
    {
        $records = $this->healthService->getStudentEmergencies($studentId);
        return $this->successResponse($records, 'Emergency contacts retrieved successfully');
    }

    public function createEmergency()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['health_record_id', 'contact_name', 'relationship', 'phone']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createEmergency($data);
        return $this->successResponse($record, 'Emergency contact created successfully');
    }

    public function updateEmergency(int $id)
    {
        return $this->successResponse(null, 'Emergency update not implemented');
    }

    public function deleteEmergency(int $id)
    {
        return $this->successResponse(null, 'Emergency deletion not implemented');
    }

    public function getMedications(int $studentId)
    {
        $records = $this->healthService->getStudentMedications($studentId);
        return $this->successResponse($records, 'Medication records retrieved successfully');
    }

    public function createMedication()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['health_record_id', 'medication_name']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createMedication($data);
        return $this->successResponse($record, 'Medication record created successfully');
    }

    public function updateMedication(int $id)
    {
        return $this->successResponse(null, 'Medication update not implemented');
    }

    public function deleteMedication(int $id)
    {
        return $this->successResponse(null, 'Medication deletion not implemented');
    }

    public function getScreenings(int $studentId)
    {
        $records = $this->healthService->getStudentScreenings($studentId);
        return $this->successResponse($records, 'Health screening records retrieved successfully');
    }

    public function createScreening()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['health_record_id', 'screening_type', 'screening_date']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createScreening($data);
        return $this->successResponse($record, 'Health screening record created successfully');
    }

    public function updateScreening(int $id)
    {
        return $this->successResponse(null, 'Screening update not implemented');
    }

    public function deleteScreening(int $id)
    {
        return $this->successResponse(null, 'Screening deletion not implemented');
    }

    public function getIncidents(int $studentId)
    {
        $records = $this->healthService->getStudentIncidents($studentId);
        return $this->successResponse($records, 'Medical incident records retrieved successfully');
    }

    public function createIncident()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['incident_date', 'incident_type', 'description']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createIncident($data);
        return $this->successResponse($record, 'Medical incident created successfully');
    }

    public function updateIncident(int $id)
    {
        return $this->successResponse(null, 'Incident update not implemented');
    }

    public function deleteIncident(int $id)
    {
        return $this->successResponse(null, 'Incident deletion not implemented');
    }

    public function getAlerts(int $studentId)
    {
        $records = $this->healthService->getStudentAlerts($studentId);
        return $this->successResponse($records, 'Health alerts retrieved successfully');
    }

    public function createAlert()
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, ['health_record_id', 'alert_type', 'description']);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->createAlert($data);
        return $this->successResponse($record, 'Health alert created successfully');
    }

    public function updateAlert(int $id)
    {
        $data = $this->request->all();

        $errors = $this->validateRequired($data, []);
        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $record = $this->healthService->updateAlert($id, $data);
        return $this->successResponse($record, 'Health alert updated successfully');
    }

    public function deactivateAlert(int $id)
    {
        $record = $this->healthService->deactivateAlert($id);
        return $this->successResponse($record, 'Health alert deactivated successfully');
    }

    public function deleteAlert(int $id)
    {
        return $this->successResponse(null, 'Alert deletion not implemented');
    }
}
