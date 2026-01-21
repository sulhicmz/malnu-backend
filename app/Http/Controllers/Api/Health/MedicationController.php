<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\HealthMedication;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class MedicationController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = HealthMedication::class;
    protected string $resourceName = 'Medication';
    protected array $relationships = ['student', 'healthRecord', 'createdBy', 'updatedBy'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'status'];
    protected array $searchFields = ['medication_name', 'dosage', 'frequency'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'medication_name', 'dosage', 'frequency', 'start_date', 'status'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'date' => ['start_date', 'end_date'],
        'in' => ['status' => 'active,completed,discontinued,on_hold'],
        'boolean' => ['refrigeration_required', 'parent_consent'],
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
            $medications = HealthMedication::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($medications, 'Medications retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getActive()
    {
        try {
            $active = HealthMedication::with(['student', 'healthRecord'])
                ->active()
                ->orderBy('start_date', 'desc')
                ->get();

            return $this->successResponse($active, 'Active medications retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getRequiringRefrigeration()
    {
        try {
            $refrigerated = HealthMedication::with(['student', 'healthRecord'])
                ->requiresRefrigeration()
                ->active()
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($refrigerated, 'Refrigerated medications retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAsCompleted(string $id)
    {
        try {
            $medication = HealthMedication::find($id);
            
            if (!$medication) {
                return $this->notFoundResponse('Medication not found');
            }

            $medication->update([
                'status' => 'completed',
                'end_date' => date('Y-m-d'),
                'updated_by' => $this->getCurrentUserId(),
            ]);

            return $this->successResponse($medication, 'Medication marked as completed');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    private function getCurrentUserId()
    {
        return $this->request->input('user_id') ?? $this->request->getAttribute('user_id');
    }
}