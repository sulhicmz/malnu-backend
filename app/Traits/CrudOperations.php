<?php

declare(strict_types=1);

namespace App\Traits;

use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

trait CrudOperations
{
    protected string $model = '';
    
    protected string $resourceName = '';
    
    protected array $relationships = [];
    
    protected array $requiredFields = [];
    
    protected array $uniqueFields = [];
    
    protected array $filters = [];
    
    protected array $searchFields = [];
    
    protected int $defaultLimit = 15;
    
    protected string $defaultOrderBy = 'id';
    
    protected string $defaultOrderDirection = 'desc';

    public function index()
    {
        try {
            $modelClass = $this->getModelClass();
            
            $query = $modelClass::with($this->relationships);
            
            $query = $this->beforeIndex($query);
            
            foreach ($this->filters as $filter) {
                if ($this->request->query($filter)) {
                    $query->where($filter, $this->request->query($filter));
                }
            }
            
            if ($search = $this->request->query('search')) {
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchFields as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            }
            
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', $this->defaultLimit);
            
            $results = $query->orderBy($this->defaultOrderBy, $this->defaultOrderDirection)
                ->paginate($limit, ['*'], 'page', $page);
            
            $results = $this->afterIndex($results);
            
            $message = $this->resourceName 
                ? ucfirst($this->resourceName) . ' retrieved successfully' 
                : 'Records retrieved successfully';
            
            return $this->successResponse($results, $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $modelClass = $this->getModelClass();
            $data = $this->request->all();
            
            $errors = [];
            
            foreach ($this->requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }
            
            if (isset($data['email']) && $data['email'] !== '') {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = ['The email must be a valid email address.'];
                }
            }
            
            foreach ($this->uniqueFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '') {
                    $existing = $modelClass::where($field, $data[$field])->first();
                    if ($existing) {
                        $errors[$field] = ["The {$field} has already been taken."];
                    }
                }
            }
            
            $data = $this->beforeStore($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }
            
            $record = $modelClass::create($data);
            
            $record = $this->afterStore($record);
            
            $message = $this->resourceName 
                ? ucfirst($this->resourceName) . ' created successfully' 
                : 'Record created successfully';
            
            return $this->successResponse($record, $message, 201);
        } catch (\Exception $e) {
            $errorCode = $this->resourceName 
                ? strtoupper($this->resourceName) . '_CREATION_ERROR' 
                : 'CREATION_ERROR';
            return $this->errorResponse($e->getMessage(), $errorCode, null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $modelClass = $this->getModelClass();
            
            $query = $modelClass::with($this->relationships);
            
            $query = $this->beforeShow($query);
            
            $record = $query->find($id);
            
            $record = $this->afterShow($record);
            
            if (!$record) {
                $message = $this->resourceName 
                    ? ucfirst($this->resourceName) . ' not found' 
                    : 'Record not found';
                return $this->notFoundResponse($message);
            }
            
            $message = $this->resourceName 
                ? ucfirst($this->resourceName) . ' retrieved successfully' 
                : 'Record retrieved successfully';
            
            return $this->successResponse($record, $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $modelClass = $this->getModelClass();
            
            $query = $modelClass::with($this->relationships);
            
            $record = $query->find($id);
            
            $record = $this->beforeUpdate($record);
            
            if (!$record) {
                $message = $this->resourceName 
                    ? ucfirst($this->resourceName) . ' not found' 
                    : 'Record not found';
                return $this->notFoundResponse($message);
            }
            
            $data = $this->request->all();
            $errors = [];
            
            foreach ($this->uniqueFields as $field) {
                if (isset($data[$field]) && $data[$field] !== '' && $data[$field] !== $record->{$field}) {
                    $existing = $modelClass::where($field, $data[$field])->first();
                    if ($existing && $existing->id !== $record->id) {
                        $errors[$field] = ["The {$field} has already been taken."];
                    }
                }
            }
            
            $data = $this->beforeUpdate($data, $record);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }
            
            $record->update($data);
            
            $record = $this->afterUpdate($record);
            
            $message = $this->resourceName 
                ? ucfirst($this->resourceName) . ' updated successfully' 
                : 'Record updated successfully';
            
            return $this->successResponse($record, $message);
        } catch (\Exception $e) {
            $errorCode = $this->resourceName 
                ? strtoupper($this->resourceName) . '_UPDATE_ERROR' 
                : 'UPDATE_ERROR';
            return $this->errorResponse($e->getMessage(), $errorCode, null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $modelClass = $this->getModelClass();
            
            $query = $modelClass::with($this->relationships);
            
            $record = $query->find($id);
            
            $record = $this->beforeDestroy($record);
            
            if (!$record) {
                $message = $this->resourceName 
                    ? ucfirst($this->resourceName) . ' not found' 
                    : 'Record not found';
                return $this->notFoundResponse($message);
            }
            
            $record->delete();
            
            $this->afterDestroy($record);
            
            $message = $this->resourceName 
                ? ucfirst($this->resourceName) . ' deleted successfully' 
                : 'Record deleted successfully';
            
            return $this->successResponse(null, $message);
        } catch (\Exception $e) {
            $errorCode = $this->resourceName 
                ? strtoupper($this->resourceName) . '_DELETION_ERROR' 
                : 'DELETION_ERROR';
            return $this->errorResponse($e->getMessage(), $errorCode, null, 400);
        }
    }

    protected function getModelClass(): string
    {
        if (empty($this->model)) {
            throw new \RuntimeException('$model property must be set in controller');
        }
        
        return $this->model;
    }

    protected function beforeIndex($query)
    {
        return $query;
    }

    protected function afterIndex($results)
    {
        return $results;
    }

    protected function beforeStore(array $data): array
    {
        return $data;
    }

    protected function afterStore($record)
    {
        return $record;
    }

    protected function beforeShow($query)
    {
        return $query;
    }

    protected function afterShow($record)
    {
        return $record;
    }

    protected function beforeUpdate($record)
    {
        return $record;
    }

    protected function beforeUpdateData(array $data, $record): array
    {
        return $data;
    }

    protected function afterUpdate($record)
    {
        return $record;
    }

    protected function beforeDestroy($record)
    {
        return $record;
    }

    protected function afterDestroy($record): void
    {
    }
}
