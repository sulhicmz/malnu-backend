<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Teacher;
use App\Traits\CrudOperations;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherController extends BaseController
{
    use CrudOperations;
    
    protected string $model = Teacher::class;
    
    protected string $resourceName = 'Teacher';
    
    protected array $relationships = ['subject', 'class'];
    
    protected array $requiredFields = ['name', 'nip', 'subject_id', 'join_date'];
    
    protected array $uniqueFields = ['nip', 'email'];
    
    protected array $filters = ['subject_id', 'class_id', 'status'];
    
    protected array $searchFields = ['name', 'nip'];
    
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