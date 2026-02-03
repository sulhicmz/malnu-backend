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

    /**
     * Store a newly created teacher.
     *
     * @OA\Post(
     *     path="/teachers",
     *     summary="Create a new teacher",
     *     @OA\RequestBody(ref="#/components/schemas/TeacherStoreRequest"),
     *     @OA\Response(response=201, description="Teacher created successfully")
     * )
     */
    public function store(StoreTeacher $request)
    {
        try {
            $validated = $request->validated();

            $teacher = Teacher::create($validated);

            return $this->successResponse($teacher, 'Teacher created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create teacher');
        }
    }

    /**
     * Update a specified teacher.
     *
     * @OA\Put(
     *     path="/teachers/{id}",
     *     summary="Update a teacher",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(ref="#/components/schemas/TeacherUpdateRequest"),
     *     @OA\Response(response=200, description="Teacher updated successfully")
     * )
     */
    public function update(string $id, UpdateTeacher $request)
    {
        try {
            $teacher = Teacher::find($id);

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $validated = $request->validated();

            $teacher->update($validated);

            return $this->successResponse($teacher, 'Teacher updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update teacher');
        }
    }
}
