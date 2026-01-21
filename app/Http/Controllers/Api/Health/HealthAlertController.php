<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\HealthAlert;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class HealthAlertController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = HealthAlert::class;
    protected string $resourceName = 'Health Alert';
    protected array $relationships = ['student', 'healthRecord', 'createdBy', 'updatedBy'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'alert_type', 'priority', 'status'];
    protected array $searchFields = ['alert_type', 'notes'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'alert_type', 'priority', 'status'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'in' => [
            'alert_type' => 'allergy,medication,condition,immunization,screening,other',
            'priority' => 'low,medium,high,critical',
            'status' => 'active,resolved,dismissed',
        ],
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
            $alerts = HealthAlert::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($alerts, 'Health alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getActive()
    {
        try {
            $active = HealthAlert::with(['student', 'healthRecord'])
                ->where('status', 'active')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($active, 'Active health alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getByPriority(string $priority)
    {
        try {
            $alerts = HealthAlert::with(['student', 'healthRecord'])
                ->where('priority', $priority)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($alerts, "Health alerts with priority $priority retrieved successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getCritical()
    {
        try {
            $critical = HealthAlert::with(['student', 'healthRecord'])
                ->where('priority', 'critical')
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($critical, 'Critical health alerts retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAsResolved(string $id)
    {
        try {
            $alert = HealthAlert::find($id);
            
            if (!$alert) {
                return $this->notFoundResponse('Health alert not found');
            }

            $alert->update([
                'status' => 'resolved',
                'updated_by' => $this->getCurrentUserId(),
            ]);

            return $this->successResponse($alert, 'Health alert marked as resolved');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    private function getCurrentUserId()
    {
        return $this->request->input('user_id') ?? $this->request->getAttribute('user_id');
    }
}