<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\HealthScreening;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class HealthScreeningController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = HealthScreening::class;
    protected string $resourceName = 'Health Screening';
    protected array $relationships = ['student', 'healthRecord', 'createdBy', 'updatedBy'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'screening_type'];
    protected array $searchFields = ['screening_type', 'notes'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'screening_type', 'screening_date', 'results'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'date' => ['screening_date', 'follow_up_date'],
        'in' => ['screening_type' => 'vision,hearing,height,weight,blood_pressure,general_physical,dental'],
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
            $screenings = HealthScreening::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('screening_date', 'desc')
                ->get();

            return $this->successResponse($screenings, 'Health screenings retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getByType(string $studentId, string $type)
    {
        try {
            $screenings = HealthScreening::with($this->relationships)
                ->where('student_id', $studentId)
                ->where('screening_type', $type)
                ->orderBy('screening_date', 'desc')
                ->get();

            return $this->successResponse($screenings, 'Health screenings by type retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAbnormalResults()
    {
        try {
            $abnormal = HealthScreening::with(['student', 'healthRecord'])
                ->where('results', 'like', '%abnormal%')
                ->orWhere('follow_up_needed', true)
                ->orderBy('screening_date', 'desc')
                ->get();

            return $this->successResponse($abnormal, 'Abnormal screening results retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}