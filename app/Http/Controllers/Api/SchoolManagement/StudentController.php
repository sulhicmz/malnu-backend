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

/**
 * @OA\Tag(
 *     name="Student",
 *     description="Student management endpoints"
 * )
 */
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

    /**
     * Store a newly created student.
     *
     * @OA\Post(
     *     path="/students",
     *     summary="Create a new student",
     *     @OA\RequestBody(ref="#/components/schemas/StudentStoreRequest"),
     *     @OA\Response(response=201, description="Student created successfully")
     * )
     */
    public function store(StoreStudent $request)
    {
        try {
            $validated = $request->validated();

            $student = Student::create($validated);

            return $this->successResponse($student, 'Student created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create student');
        }
    }

    /**
     * Update the specified student.
     *
     * @OA\Put(
     *     path="/students/{id}",
     *     summary="Update a student",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(ref="#/components/schemas/StudentUpdateRequest"),
     *     @OA\Response(response=200, description="Student updated successfully")
     * )
     */
    public function update(string $id, UpdateStudent $request)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $validated = $request->validated();

            $student->update($validated);

            return $this->successResponse($student, 'Student updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update student');
        }
    }
}
