<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\Immunization;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class ImmunizationController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Immunization::class;
    protected string $resourceName = 'Immunization';
    protected array $relationships = ['student', 'healthRecord'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'vaccine_name'];
    protected array $searchFields = ['vaccine_name', 'batch_number', 'administering_facility'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'vaccine_name', 'administration_date'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'date' => ['administration_date', 'due_date'],
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
            $immunizations = Immunization::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('administration_date', 'desc')
                ->get();

            return $this->successResponse($immunizations, 'Immunizations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getOverdue()
    {
        try {
            $overdue = Immunization::with(['student', 'healthRecord'])
                ->overdue()
                ->get();

            return $this->successResponse($overdue, 'Overdue immunizations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getDueSoon(int $days = 30)
    {
        try {
            $dueSoon = Immunization::with(['student', 'healthRecord'])
                ->dueSoon($days)
                ->get();

            return $this->successResponse($dueSoon, 'Immunizations due soon retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getComplianceReport(string $studentId)
    {
        try {
            $immunizations = Immunization::where('student_id', $studentId)->get();

            $total = $immunizations->count();
            $completed = $immunizations->whereNotNull('administration_date')->count();
            $overdue = $immunizations->overdue()->count();
            $dueSoon = $immunizations->dueSoon()->count();

            $compliance = $total > 0 ? round(($completed / $total) * 100, 2) : 100;

            return $this->successResponse([
                'total_immunizations' => $total,
                'completed' => $completed,
                'overdue' => $overdue,
                'due_soon' => $dueSoon,
                'compliance_rate' => $compliance,
            ], 'Immunization compliance report retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}