<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreClass;
use App\Http\Requests\SchoolManagement\UpdateClass;
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

    /**
     * Store a newly created class in storage.
     */
    public function store(StoreClass $request)
    {
        try {
            $validated = $request->validated();

            $model = $this->getModelInstance();
            $result = $model->create($validated);

            $this->afterStore($result);
            $this->invalidateCache();

            return $this->successResponse($result, "{$this->resourceName} created successfully", 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified class in storage.
     */
    public function update(string $id, UpdateClass $request)
    {
        try {
            $model = $this->getModelInstance()->find($id);

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            $validated = $request->validated();

            $model->update($validated);

            $this->afterUpdate($model);
            $this->invalidateCache();

            return $this->successResponse($model, "{$this->resourceName} updated successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
