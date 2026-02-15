<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\AssetCategory;
use Exception;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;

class AssetCategoryController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        try {
            $query = AssetCategory::query();

            $search = $this->request->query('search');
            $isActive = $this->request->query('is_active');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }

            $categories = $query->orderBy('name', 'asc')->get();

            return $this->successResponse($categories, 'Asset categories retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $category = AssetCategory::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $this->successResponse($category, 'Asset category created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CATEGORY_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $category = AssetCategory::find($id);

            if (!$category) {
                return $this->notFoundResponse('Asset category not found');
            }

            return $this->successResponse($category, 'Asset category retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $category = AssetCategory::find($id);

            if (!$category) {
                return $this->notFoundResponse('Asset category not found');
            }

            $data = $this->request->all();

            $category->update($data);

            return $this->successResponse($category, 'Asset category updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CATEGORY_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $category = AssetCategory::find($id);

            if (!$category) {
                return $this->notFoundResponse('Asset category not found');
            }

            $category->delete();

            return $this->successResponse(null, 'Asset category deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CATEGORY_DELETION_ERROR', null, 400);
        }
    }
}
