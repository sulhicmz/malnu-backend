<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FinancialManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\FinancialManagement\FeeType;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class FeeTypeController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = FeeType::class;
    protected string $resourceName = 'Fee Type';
    protected array $relationships = ['feeStructures', 'invoiceItems'];
    protected array $allowedFilters = ['is_active'];
    protected array $searchFields = ['name', 'code'];
    protected array $validationRules = [
        'required' => ['name', 'code'],
        'unique' => ['code'],
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
