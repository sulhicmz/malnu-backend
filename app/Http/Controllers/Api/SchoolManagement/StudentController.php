<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

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
}
