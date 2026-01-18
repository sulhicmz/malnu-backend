<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\HealthRecord;
use App\Models\Student;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class HealthRecordController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = HealthRecord::class;
    protected string $resourceName = 'Health Record';
    protected array $relationships = ['student', 'immunizations', 'medications', 'allergies', 'healthScreenings', 'emergencyContacts', 'medicalIncidents', 'healthAlerts'];
    protected array $allowedFilters = ['student_id', 'blood_type'];
    protected array $searchFields = ['notes', 'chronic_conditions'];
    protected array $validationRules = [
        'required' => ['student_id'],
        'exists' => ['student_id' => 'students,id'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function getByStudent(string $studentId)
    {
        try {
            $student = Student::find($studentId);
            
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $healthRecord = HealthRecord::with($this->relationships)
                ->where('student_id', $studentId)
                ->first();

            if (!$healthRecord) {
                return $this->successResponse(null, 'No health record found for this student', 200);
            }

            return $this->successResponse($healthRecord, 'Health record retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getMedicalAlerts(string $studentId)
    {
        try {
            $student = Student::find($studentId);
            
            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $healthRecord = HealthRecord::with(['allergies', 'medications', 'healthAlerts'])
                ->where('student_id', $studentId)
                ->first();

            if (!$healthRecord) {
                return $this->successResponse(null, 'No health record found for this student', 200);
            }

            $alerts = [];

            foreach ($healthRecord->allergies as $allergy) {
                if ($allergy->severity === 'severe' || $allergy->severity === 'life_threatening') {
                    $alerts[] = [
                        'type' => 'allergy',
                        'severity' => $allergy->severity,
                        'description' => $allergy->allergen,
                        'emergency_protocol' => $allergy->emergency_protocol,
                        'epipen_required' => $allergy->epipen_required,
                    ];
                }
            }

            foreach ($healthRecord->medications as $medication) {
                if ($medication->status === 'active') {
                    $alerts[] = [
                        'type' => 'medication',
                        'severity' => 'medium',
                        'description' => $medication->medication_name,
                        'dosage' => $medication->dosage,
                        'frequency' => $medication->frequency,
                    ];
                }
            }

            foreach ($healthRecord->healthAlerts as $alert) {
                if ($alert->status === 'active') {
                    $alerts[] = [
                        'type' => 'health_alert',
                        'severity' => $alert->priority,
                        'description' => $alert->alert_type,
                        'notes' => $alert->notes,
                    ];
                }
            }

            return $this->successResponse($alerts, 'Medical alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}