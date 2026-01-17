<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

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
            $data = $this->beforeStore($request->validated());

            $student = Student::create($data);

            $this->afterStore($student);

            return $this->successResponse($student, 'Student created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id, UpdateStudent $request)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->beforeUpdate($request->validated(), $student);

            $student->update($data);

            $this->afterUpdate($student);

            return $this->successResponse($student, 'Student updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_UPDATE_ERROR', null, 400);
        }
    }
}
