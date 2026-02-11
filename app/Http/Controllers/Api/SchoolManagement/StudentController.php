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

    /**
     * Store a newly created student in storage.
     */
    public function store(StoreStudent $request)
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
     * Update the specified student in storage.
     */
    public function update(string $id, UpdateStudent $request)
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
