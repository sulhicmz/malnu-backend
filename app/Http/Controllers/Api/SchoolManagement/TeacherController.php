<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use App\Models\SchoolManagement\Teacher;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

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
            $validated = $request->validated();

            $this->checkUniqueFields($validated, null);

            $result = Teacher::create($validated);

            return $this->successResponse($result, 'Teacher created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id, UpdateTeacher $request)
    {
        try {
            $model = Teacher::find($id);

            if (! $model) {
                return $this->notFoundResponse('Teacher not found');
            }

            $validated = $request->validated();

            $this->checkUniqueFields($validated, $model);

            $model->update($validated);

            return $this->successResponse($model, 'Teacher updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
