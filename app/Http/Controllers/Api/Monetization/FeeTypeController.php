<?php

namespace App\Http\Controllers\Api\Monetization;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\FeeType;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Fee Type",
 *     description="Fee type management endpoints"
 * )
 */
class FeeTypeController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = FeeType::class;
    protected string $resourceName = 'Fee Type';
    protected array $relationships = [];
    protected array $uniqueFields = ['code'];
    protected array $allowedFilters = ['is_active'];
    protected array $searchFields = ['name', 'code'];
    protected array $validationRules = [
        'required' => ['name', 'code'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}
