<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Subject;
use App\Traits\CrudOperationsTrait;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Subject",
 *     description="Subject management endpoints"
 * )
 */
class SubjectController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Subject::class;
    protected string $resourceName = 'Subject';
    protected array $relationships = ['classSubjects'];
    protected array $uniqueFields = ['code'];
    protected array $allowedFilters = ['credit_hours'];
    protected array $searchFields = ['name', 'code', 'description'];
    protected array $validationRules = [
        'required' => ['code', 'name'],
        'email' => null,
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}
