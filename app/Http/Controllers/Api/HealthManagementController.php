<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\HealthManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class HealthManagementController extends BaseController
{
    private HealthManagementService $healthService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        HealthManagementService $healthService
    ) {
        parent::__construct($request, $response, $container);
        $this->healthService = $healthService;
    }

    public function getHealthRecord(string $studentId)
    {
        try {
            $record = $this->healthService->getHealthRecord($studentId);
            
            if (!$record) {
                return $this->notFoundResponse('Health record not found');
            }

            return $this->successResponse($record, 'Health record retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createHealthRecord()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $record = $this->healthService->createHealthRecord($data);
            return $this->successResponse($record, 'Health record created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'HEALTH_RECORD_CREATION_ERROR', null, 400);
        }
    }

    public function getMedications(string $studentId)
    {
        try {
            $status = $this->request->query('status');
            $medications = $this->healthService->getStudentMedications($studentId, $status);
            return $this->successResponse($medications, 'Medications retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createMedication()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id', 'medication_name', 'dosage', 'frequency', 'start_date'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $medication = $this->healthService->createMedication($data);
            return $this->successResponse($medication, 'Medication created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEDICATION_CREATION_ERROR', null, 400);
        }
    }

    public function updateMedication(string $id)
    {
        try {
            $data = $this->request->all();
            $medication = $this->healthService->updateMedication($id, $data);
            return $this->successResponse($medication, 'Medication updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEDICATION_UPDATE_ERROR', null, 400);
        }
    }

    public function deleteMedication(string $id)
    {
        try {
            $this->healthService->deleteMedication($id);
            return $this->successResponse(null, 'Medication deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEDICATION_DELETION_ERROR', null, 400);
        }
    }

    public function getImmunizations(string $studentId)
    {
        try {
            $status = $this->request->query('status');
            $immunizations = $this->healthService->getStudentImmunizations($studentId, $status);
            return $this->successResponse($immunizations, 'Immunizations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getImmunizationCompliance(string $studentId)
    {
        try {
            $compliance = $this->healthService->getImmunizationCompliance($studentId);
            return $this->successResponse($compliance, 'Immunization compliance retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createImmunization()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id', 'vaccine_name', 'administration_date'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $immunization = $this->healthService->createImmunization($data);
            return $this->successResponse($immunization, 'Immunization created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'IMMUNIZATION_CREATION_ERROR', null, 400);
        }
    }

    public function getAllergies(string $studentId)
    {
        try {
            $severeOnly = $this->request->query('severe_only', 'false') === 'true';
            $allergies = $this->healthService->getStudentAllergies($studentId, $severeOnly);
            return $this->successResponse($allergies, 'Allergies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getSevereAllergiesAlert(string $studentId)
    {
        try {
            $severeAllergies = $this->healthService->getSevereAllergiesAlert($studentId);
            return $this->successResponse($severeAllergies, 'Severe allergies alert retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createAllergy()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id', 'allergen'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $allergy = $this->healthService->createAllergy($data);
            return $this->successResponse($allergy, 'Allergy created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ALLERGY_CREATION_ERROR', null, 400);
        }
    }

    public function getEmergencyContacts(string $studentId)
    {
        try {
            $contacts = $this->healthService->getEmergencyContacts($studentId);
            return $this->successResponse($contacts, 'Emergency contacts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createEmergencyContact()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id', 'full_name', 'relationship', 'phone'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $contact = $this->healthService->createEmergencyContact($data);
            return $this->successResponse($contact, 'Emergency contact created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EMERGENCY_CONTACT_CREATION_ERROR', null, 400);
        }
    }

    public function createMedicalIncident()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['student_id', 'incident_date', 'incident_type', 'description', 'severity', 'status'];
            $errors = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $incident = $this->healthService->createMedicalIncident($data);
            return $this->successResponse($incident, 'Medical incident created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEDICAL_INCIDENT_CREATION_ERROR', null, 400);
        }
    }

    public function getMedicalIncidents(string $studentId = null)
    {
        try {
            $studentId = $studentId ?: $this->request->query('student_id');
            $severity = $this->request->query('severity');
            $status = $this->request->query('status');
            $incidents = $this->healthService->getMedicalIncidents($studentId, $severity, $status);
            return $this->successResponse($incidents, 'Medical incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getHealthReport(string $studentId)
    {
        try {
            $report = $this->healthService->generateHealthReport($studentId);
            return $this->successResponse($report, 'Health report generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getHealthSummary()
    {
        try {
            $summary = $this->healthService->getHealthSummary();
            return $this->successResponse($summary, 'Health summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
