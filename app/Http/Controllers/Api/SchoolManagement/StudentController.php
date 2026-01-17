<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class StudentController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Student::class;
    protected string $resourceName = 'Student';
    protected array $relationships = ['class'];
    protected array $uniqueFields = ['nisn', 'email'];
    protected array $allowedFilters = ['class_id', 'status'];
    protected array $searchFields = ['name', 'nisn'];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function store(StoreStudent $request)
    {
        try {
            $validated = $request->validated();

            $this->checkUniqueFields($validated, null);

            $result = Student::create($validated);

            return $this->successResponse($result, 'Student created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id, UpdateStudent $request)
    {
        try {
            $model = Student::find($id);

            if (! $model) {
                return $this->notFoundResponse('Student not found');
            }

            $validated = $request->validated();

            $this->checkUniqueFields($validated, $model);

            $model->update($validated);

            return $this->successResponse($model, 'Student updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
