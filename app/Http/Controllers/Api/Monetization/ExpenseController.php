<?php

namespace App\Http\Controllers\Api\Monetization;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\Expense;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Expense",
 *     description="Expense management endpoints"
 * )
 */
class ExpenseController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Expense::class;
    protected string $resourceName = 'Expense';
    protected array $relationships = [];
    protected array $uniqueFields = [];
    protected array $allowedFilters = ['category', 'approval_status', 'expense_date'];
    protected array $searchFields = ['description', 'vendor'];
    protected array $validationRules = [
        'required' => ['category', 'amount', 'expense_date'],
    ];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}
