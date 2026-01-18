<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use App\Models\SchoolManagement\Teacher;
use App\Traits\CrudOperationsTrait;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use OpenApi\Annotations as OA;
use Psr\Container\ContainerInterface;

/**
 * @OA\Tag(
 *     name="Teacher",
 *     description="Teacher management endpoints"
 * )
 */
class TeacherController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Teacher::class;

    protected string $resourceName = 'Teacher';

    protected array $relationships = ['subject', 'class'];

    protected array $uniqueFields = ['nip', 'email'];

    protected array $allowedFilters = ['subject_id', 'class_id', 'status'];

    protected array $searchFields = ['name', 'nip'];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function store(StoreTeacher $request)
    {
        try {
            $data = $request->validated();

            $data = $this->beforeStore($data);

            $model = $this->getModelInstance();
            $result = $model->create($data);

            $this->afterStore($result);

            return $this->successResponse($result, "{$this->resourceName} created successfully", 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), strtoupper(str_replace(' ', '_', $this->resourceName)) . '_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id, UpdateTeacher $request)
    {
        try {
            $model = $this->getModelInstance()->find($id);

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            $data = $request->validated();

            $data = $this->beforeUpdate($data, $model);

            $model->update($data);

            $this->afterUpdate($model);

            return $this->successResponse($model, "{$this->resourceName} updated successfully");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), strtoupper(str_replace(' ', '_', $this->resourceName)) . '_UPDATE_ERROR', null, 400);
        }
    }
}
