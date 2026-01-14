<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use App\Models\SchoolManagement\Teacher;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Teacher::class;
    protected string $resourceName = 'Teacher';
    protected array $relationships = ['subject', 'class'];
    protected array $uniqueFields = ['nip', 'email'];
    protected array $allowedFilters = ['subject_id', 'class_id', 'status'];
    protected array $searchFields = ['name', 'nip'];
    protected array $validationRules = [
        'required' => ['name', 'nip', 'subject_id', 'join_date'],
        'email' => 'email',
    ];

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

            $teacher = Teacher::create($validated);

            return $this->successResponse($teacher, 'Teacher created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id, UpdateTeacher $request)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $validated = $request->validated();

            $this->checkUniqueFields($validated, $teacher);

            $teacher->update($validated);

            return $this->successResponse($teacher, 'Teacher updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_UPDATE_ERROR', null, 400);
        }
    }
}
