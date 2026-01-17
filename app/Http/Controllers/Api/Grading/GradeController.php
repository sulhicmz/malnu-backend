<?php

namespace App\Http\Controllers\Api\Grading;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\Grade;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Grade",
 *     description="Grade management endpoints"
 * )
 */
class GradeController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Grade::class;
    protected string $resourceName = 'Grade';
    protected array $relationships = ['student', 'subject', 'classModel', 'assignment', 'quiz', 'exam', 'creator'];
    protected array $uniqueFields = [];
    protected array $allowedFilters = ['student_id', 'subject_id', 'class_id', 'semester', 'grade_type'];
    protected array $searchFields = ['notes'];
    protected array $validationRules = [
        'required' => ['student_id', 'subject_id', 'class_id', 'grade', 'semester', 'grade_type'],
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
