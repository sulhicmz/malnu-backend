<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\HealthEmergency;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class HealthEmergencyController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = HealthEmergency::class;
    protected string $resourceName = 'Health Emergency';
    protected array $relationships = ['student', 'healthRecord'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'emergency_type', 'severity'];
    protected array $searchFields = ['emergency_type', 'description', 'actions_taken'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'emergency_type', 'emergency_date', 'description'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'date' => ['emergency_date'],
        'in' => ['emergency_type' => 'severe_allergic_reaction,seizure,breathing_difficulty,fainting,injury,fever,medication_emergency,other', 'severity' => 'mild,moderate,severe,critical'],
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
            $emergencies = HealthEmergency::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('emergency_date', 'desc')
                ->get();

            return $this->successResponse($emergencies, 'Health emergencies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getRecent(int $days = 30)
    {
        try {
            $recent = HealthEmergency::with(['student', 'healthRecord'])
                ->where('emergency_date', '>=', date('Y-m-d', strtotime("-$days days")))
                ->orderBy('emergency_date', 'desc')
                ->get();

            return $this->successResponse($recent, 'Recent health emergencies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getCritical()
    {
        try {
            $critical = HealthEmergency::with(['student', 'healthRecord'])
                ->where('severity', 'critical')
                ->orderBy('emergency_date', 'desc')
                ->get();

            return $this->successResponse($critical, 'Critical health emergencies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}