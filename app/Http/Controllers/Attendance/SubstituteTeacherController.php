<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\SubstituteTeacher;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class SubstituteTeacherController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = SubstituteTeacher::class;
    protected string $resourceName = 'Substitute Teacher';
    protected array $relationships = ['teacher', 'substituteAssignments'];
    protected array $allowedFilters = ['is_active'];
    protected array $searchFields = [];
    protected array $validationRules = [
        'required' => ['teacher_id'],
        'boolean' => ['is_active'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function getAvailable()
    {
        try {
            $query = SubstituteTeacher::with('teacher')->where('is_active', true);

            $subjectId = $this->request->query('subject_id');
            $classId = $this->request->query('class_id');
            $date = $this->request->query('date');

            if ($subjectId) {
                $query->whereJsonContains('available_subjects', $subjectId);
            }

            if ($classId) {
                $query->whereJsonContains('available_classes', $classId);
            }

            $substitutes = $query->orderBy('created_at', 'desc')->paginate(15);

            return $this->successResponse($substitutes, 'Available substitute teachers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve available substitutes');
        }
    }
}
