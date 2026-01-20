<?php

namespace App\Http\Controllers\Api\Monetization;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\FeeStructure;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Fee Structure",
 *     description="Fee structure management endpoints"
 * )
 */
class FeeStructureController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = FeeStructure::class;
    protected string $resourceName = 'Fee Structure';
    protected array $relationships = ['feeType'];
    protected array $uniqueFields = [];
    protected array $allowedFilters = ['fee_type_id', 'academic_year', 'student_class', 'student_type', 'is_active'];
    protected array $searchFields = ['name'];
    protected array $validationRules = [
        'required' => ['fee_type_id', 'name', 'amount', 'academic_year'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}
