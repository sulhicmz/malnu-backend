<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\CacheService;
use Exception;
use Hyperf\Database\Model\Model;
use Throwable;

trait CrudOperationsTrait
{
    protected string $resourceName = 'Resource';

    protected ?string $model = null;

    protected array $relationships = [];

    protected array $validationRules = [];

    protected array $uniqueFields = [];

    protected array $allowedFilters = [];

    protected array $searchFields = [];

    protected string $defaultOrderBy = 'id';

    protected string $defaultOrderDirection = 'asc';

    protected int $defaultPerPage = 15;

    protected ?CacheService $cache = null;

    protected bool $useCache = true;

    protected int $cacheTTL = 300;

    public function index()
    {
        try {
            $cacheService = $this->getCacheService();

            if ($cacheService) {
                $cacheKey = $cacheService->generateKey($this->getCacheKeyPrefix() . ':index', [
                    'page' => $this->request->query('page', 1),
                    'limit' => $this->request->query('limit', $this->defaultPerPage),
                    'filters' => array_intersect_key($this->request->query(), array_flip($this->allowedFilters)),
                    'search' => $this->request->query('search'),
                ]);

                $results = $cacheService->remember($cacheKey, $this->cacheTTL, function () {
                    $query = $this->buildIndexQuery();

                    $page = (int) $this->request->query('page', 1);
                    $limit = (int) $this->request->query('limit', $this->defaultPerPage);

                    return $query->orderBy($this->defaultOrderBy, $this->defaultOrderDirection)
                        ->paginate($limit, ['*'], 'page', $page);
                });
            } else {
                $query = $this->buildIndexQuery();

                $page = (int) $this->request->query('page', 1);
                $limit = (int) $this->request->query('limit', $this->defaultPerPage);

                $results = $query->orderBy($this->defaultOrderBy, $this->defaultOrderDirection)
                    ->paginate($limit, ['*'], 'page', $page);
            }

            return $this->successResponse($results, "{$this->resourceName} retrieved successfully");
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $errors = $this->validateStoreData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $data = $this->beforeStore($data);

            $this->checkUniqueFields($data, null);

            $model = $this->getModelInstance();
            $result = $model->create($data);

            $this->afterStore($result);

            $this->invalidateCache();

            return $this->successResponse($result, "{$this->resourceName} created successfully", 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), strtoupper(str_replace(' ', '_', $this->resourceName)) . '_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $cacheService = $this->getCacheService();

            if ($cacheService) {
                $cacheKey = $cacheService->generateKey($this->getCacheKeyPrefix() . ':show', [
                    'id' => $id,
                ]);

                $model = $cacheService->remember($cacheKey, $this->cacheTTL, function () use ($id) {
                    $query = $this->getModelInstance()->query();

                    if (! empty($this->relationships)) {
                        $query->with($this->relationships);
                    }

                    return $query->find($id);
                });
            } else {
                $query = $this->getModelInstance()->query();

                if (! empty($this->relationships)) {
                    $query->with($this->relationships);
                }

                $model = $query->find($id);
            }

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            return $this->successResponse($model, "{$this->resourceName} retrieved successfully");
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $model = $this->getModelInstance()->find($id);

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            $data = $this->request->all();

            $errors = $this->validateUpdateData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $data = $this->beforeUpdate($data, $model);

            $this->checkUniqueFields($data, $model);

            $model->update($data);

            $this->afterUpdate($model);

            $this->invalidateCache();

            return $this->successResponse($model, "{$this->resourceName} updated successfully");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), strtoupper(str_replace(' ', '_', $this->resourceName)) . '_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $model = $this->getModelInstance()->find($id);

            if (! $model) {
                return $this->notFoundResponse("{$this->resourceName} not found");
            }

            $canDelete = $this->beforeDestroy($model);

            if ($canDelete === false) {
                return $this->errorResponse("Cannot delete {$this->resourceName}", 'CANNOT_DELETE', null, 400);
            }

            $model->delete();

            $this->afterDestroy($model);

            $this->invalidateCache($model);

            return $this->successResponse(null, "{$this->resourceName} deleted successfully");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), strtoupper(str_replace(' ', '_', $this->resourceName)) . '_DELETION_ERROR', null, 400);
        }
    }

    protected function getCacheService(): ?CacheService
    {
        if (! $this->useCache) {
            return null;
        }

        if ($this->cache === null) {
            try {
                $this->cache = \Hyperf\Context\ApplicationContext::getContainer()
                    ->get(CacheService::class);
            } catch (Throwable $e) {
                return null;
            }
        }

        return $this->cache;
    }

    protected function getCacheKeyPrefix(): string
    {
        return strtolower(str_replace('\\', ':', $this->model ?? 'resource'));
    }

    protected function buildIndexQuery()
    {
        $query = $this->getModelInstance()->query();

        if (! empty($this->relationships)) {
            $query->with($this->relationships);
        }

        $this->applyFilters($query);
        $this->applySearch($query);

        return $this->beforeIndex($query);
    }

    protected function applyFilters($query): void
    {
        foreach ($this->allowedFilters as $filter) {
            $value = $this->request->query($filter);
            if ($value !== null) {
                $query->where($filter, $value);
            }
        }
    }

    protected function applySearch($query): void
    {
        $search = $this->request->query('search');
        if ($search && ! empty($this->searchFields)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
    }

    protected function validateStoreData(array $data): array
    {
        $errors = [];

        if (! empty($this->validationRules['required'])) {
            foreach ($this->validationRules['required'] as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }
        }

        if (isset($this->validationRules['email'], $data[$this->validationRules['email']]) && ! filter_var($data[$this->validationRules['email']], FILTER_VALIDATE_EMAIL)) {
            $errors[$this->validationRules['email']] = ['The email must be a valid email address.'];
        }

        return $errors;
    }

    protected function validateUpdateData(array $data): array
    {
        return $this->validateStoreData($data);
    }

    protected function checkUniqueFields(array $data, ?Model $existingModel): void
    {
        foreach ($this->uniqueFields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                $query = $this->getModelInstance()->where($field, $data[$field]);

                if ($existingModel) {
                    $query->where('id', '!=', $existingModel->id);
                }

                $exists = $query->first();
                if ($exists) {
                    throw new Exception("The {$field} has already been taken.");
                }
            }
        }
    }

    protected function beforeIndex($query)
    {
        return $query;
    }

    protected function beforeStore(array $data): array
    {
        return $data;
    }

    protected function afterStore(Model $model): void
    {
    }

    protected function beforeUpdate(array $data, Model $model): array
    {
        return $data;
    }

    protected function afterUpdate(Model $model): void
    {
    }

    protected function beforeDestroy(Model $model)
    {
        return true;
    }

    protected function afterDestroy(Model $model): void
    {
    }

    protected function getModelInstance()
    {
        if (! $this->model) {
            throw new Exception('Model class not specified. Set $model property in your controller.');
        }

        return new $this->model();
    }

    protected function invalidateCache(?Model $model = null): void
    {
        $cacheService = $this->getCacheService();

        if (! $cacheService) {
            return;
        }

        $prefix = $this->getCacheKeyPrefix();

        $cacheService->forgetByPrefix($prefix . ':index');
        $cacheService->forgetByPrefix($prefix . ':show');

        if ($model) {
            $cacheService->forget($cacheService->generateKey($prefix . ':show', ['id' => $model->id]));
        }
    }
}
