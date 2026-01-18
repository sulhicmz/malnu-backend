<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\MedicalIncident;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class MedicalIncidentController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = MedicalIncident::class;
    protected string $resourceName = 'Medical Incident';
    protected array $relationships = ['student', 'healthRecord', 'createdBy', 'updatedBy'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'incident_type', 'severity'];
    protected array $searchFields = ['incident_type', 'description', 'treatment'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'incident_type', 'incident_date', 'description', 'reported_by'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'date' => ['incident_date', 'resolved_date'],
        'in' => ['incident_type' => 'injury,illness,allergic_reaction,medication_error,other', 'severity' => 'mild,moderate,severe,critical'],
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
            $incidents = MedicalIncident::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('incident_date', 'desc')
                ->get();

            return $this->successResponse($incidents, 'Medical incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getRecent(int $days = 7)
    {
        try {
            $recent = MedicalIncident::with(['student', 'healthRecord'])
                ->where('incident_date', '>=', date('Y-m-d', strtotime("-$days days")))
                ->orderBy('incident_date', 'desc')
                ->get();

            return $this->successResponse($recent, 'Recent medical incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getUnresolved()
    {
        try {
            $unresolved = MedicalIncident::with(['student', 'healthRecord'])
                ->whereNull('resolved_date')
                ->orderBy('severity', 'desc')
                ->orderBy('incident_date', 'desc')
                ->get();

            return $this->successResponse($unresolved, 'Unresolved medical incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAsResolved(string $id)
    {
        try {
            $incident = MedicalIncident::find($id);
            
            if (!$incident) {
                return $this->notFoundResponse('Medical incident not found');
            }

            $incident->update([
                'resolved_date' => date('Y-m-d H:i:s'),
                'updated_by' => $this->getCurrentUserId(),
            ]);

            return $this->successResponse($incident, 'Medical incident marked as resolved');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    private function getCurrentUserId()
    {
        return $this->request->input('user_id') ?? $this->request->getAttribute('user_id');
    }
}