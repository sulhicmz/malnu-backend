<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperations;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentController extends BaseController
{
    use CrudOperations;
    
    protected string $model = Student::class;
    
    protected string $resourceName = 'Student';
    
    protected array $relationships = ['class'];
    
    protected array $requiredFields = ['name', 'nisn', 'class_id', 'enrollment_year', 'status'];
    
    protected array $uniqueFields = ['nisn', 'email'];
    
    protected array $filters = ['class_id', 'status'];
    
    protected array $searchFields = ['name', 'nisn'];
    
    protected string $defaultOrderBy = 'name';
    
    protected string $defaultOrderDirection = 'asc';
    
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }
}