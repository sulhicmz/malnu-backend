<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;
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
    protected array $validationRules = [
        'required' => ['name', 'nisn', 'class_id', 'enrollment_year', 'status'],
        'email' => 'email',
    ];

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

            $student = Student::create($validated);

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

            $validated = $request->validated();

            $this->checkUniqueFields($validated, $student);

            $student->update($validated);

            return $this->successResponse($student, 'Student updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_UPDATE_ERROR', null, 400);
        }
    }
}
