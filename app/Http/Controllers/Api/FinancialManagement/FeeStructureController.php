<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FinancialManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\FinancialManagement\FeeStructure;
use App\Traits\CrudOperationsTrait;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class FeeStructureController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = FeeStructure::class;
    protected string $resourceName = 'Fee Structure';
    protected array $relationships = ['feeType', 'invoices'];
    protected array $allowedFilters = ['fee_type_id', 'is_active', 'academic_year', 'student_type'];
    protected array $searchFields = ['name'];
    protected array $validationRules = [
        'required' => ['fee_type_id', 'name', 'amount', 'frequency'],
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
