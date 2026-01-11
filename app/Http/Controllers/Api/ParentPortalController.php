<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ParentPortal\ParentOrtu;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class ParentPortalController extends BaseController
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
            $query = ParentOrtu::query();

            $studentId = $this->request->query('student_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            $parents = $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($parents, 'Parents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'name', 'email', 'phone'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $parent = ParentOrtu::create($data);

            return $this->successResponse($parent, 'Parent created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PARENT_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $parent = ParentOrtu::with(['student'])->find($id);

            if (!$parent) {
                return $this->notFoundResponse('Parent not found');
            }

            return $this->successResponse($parent, 'Parent retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $parent = ParentOrtu::find($id);

            if (!$parent) {
                return $this->notFoundResponse('Parent not found');
            }

            $data = $this->request->all();
            $parent->update($data);

            return $this->successResponse($parent, 'Parent updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PARENT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $parent = ParentOrtu::find($id);

            if (!$parent) {
                return $this->notFoundResponse('Parent not found');
            }

            $parent->delete();

            return $this->successResponse(null, 'Parent deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PARENT_DELETION_ERROR', null, 400);
        }
    }
}
