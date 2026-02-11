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

    /**
     * Store a newly created teacher in storage.
     */
    public function store(StoreTeacher $request)
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
     * Update the specified teacher in storage.
     */
    public function update(string $id, UpdateTeacher $request)
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
