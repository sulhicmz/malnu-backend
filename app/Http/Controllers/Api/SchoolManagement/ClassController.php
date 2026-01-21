<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\ClassModel;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Class",
 *     description="Class management endpoints"
 * )
 */
class ClassController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = ClassModel::class;
    protected string $resourceName = 'Class';
    protected array $relationships = ['homeroomTeacher', 'students', 'classSubjects'];
    protected array $uniqueFields = ['name', 'level', 'academic_year'];
    protected array $allowedFilters = ['level', 'academic_year', 'homeroom_teacher_id', 'status'];
    protected array $searchFields = ['name'];
    protected array $validationRules = [
        'required' => ['name', 'level', 'academic_year'],
        'email' => null,
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}
