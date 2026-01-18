<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Health;

use App\Http\Controllers\Api\BaseController;
use App\Models\Allergy;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AllergyController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Allergy::class;
    protected string $resourceName = 'Allergy';
    protected array $relationships = ['student', 'healthRecord'];
    protected array $allowedFilters = ['student_id', 'health_record_id', 'severity', 'epipen_required'];
    protected array $searchFields = ['allergen', 'symptoms'];
    protected array $validationRules = [
        'required' => ['student_id', 'health_record_id', 'allergen', 'severity'],
        'exists' => [
            'student_id' => 'students,id',
            'health_record_id' => 'health_records,id',
        ],
        'in' => ['severity' => 'mild,moderate,severe,life_threatening'],
        'boolean' => ['epipen_required'],
        'date' => ['diagnosed_date'],
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
            $allergies = Allergy::with($this->relationships)
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($allergies, 'Allergies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getSevere()
    {
        try {
            $severe = Allergy::with(['student', 'healthRecord'])
                ->severe()
                ->orderBy('severity', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($severe, 'Severe allergies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getRequiringEpipen()
    {
        try {
            $epipen = Allergy::with(['student', 'healthRecord'])
                ->requiresEpipen()
                ->orderBy('severity', 'desc')
                ->get();

            return $this->successResponse($epipen, 'Epipen-requiring allergies retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}