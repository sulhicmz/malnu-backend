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
            $data = $request->validated();

            $data = $this->beforeStore($data);

            $this->checkUniqueFields($data, null);

            $model = $this->getModelInstance();
            $result = $model->create($data);

            $this->afterStore($result);

            return $this->successResponse($result, "{$this->resourceName} created successfully", 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id, UpdateStudent $request)
    {
        try {
            $model = $this->getModelInstance()->find($id);

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            $data = $request->validated();

            $data = $this->beforeUpdate($data, $model);

            $this->checkUniqueFields($data, $model);

            $model->update($data);

            $this->afterUpdate($model);

            return $this->successResponse($model, "{$this->resourceName} updated successfully");
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_UPDATE_ERROR', null, 400);
        }
    }
}
