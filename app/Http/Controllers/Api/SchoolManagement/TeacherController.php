<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Teacher;
use App\Services\CacheService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherController extends BaseController
{
    private CacheService $cache;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
        $this->cache = new CacheService($container);
    }

    public function index()
    {
        try {
            $subjectId = $this->request->query('subject_id');
            $classId = $this->request->query('class_id');
            $status = $this->request->query('status');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            $cacheKey = "teachers:index:{$subjectId}:{$classId}:{$status}:{$search}:{$page}:{$limit}";

            $teachers = $this->cache->remember($cacheKey, CacheService::TTL_SHORT, function () use ($subjectId, $classId, $status, $search, $page, $limit) {
                $query = Teacher::with(['subject', 'class']);

                if ($subjectId) {
                    $query->where('subject_id', $subjectId);
                }

                if ($classId) {
                    $query->where('class_id', $classId);
                }

                if ($status) {
                    $query->where('status', $status);
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
                }

                return $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);
            });

            return $this->successResponse($teachers, 'Teachers retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'nip', 'subject_id', 'join_date'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (isset($data['nip'])) {
                $existingTeacher = Teacher::where('nip', $data['nip'])->first();
                if ($existingTeacher) {
                    $errors['nip'] = ['The NIP has already been taken.'];
                }
            }

            if (isset($data['email']) && $data['email']) {
                $existingTeacher = Teacher::where('email', $data['email'])->first();
                if ($existingTeacher) {
                    $errors['email'] = ['The email has already been taken.'];
                }
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $teacher = Teacher::create($data);

            $this->cache->forgetModel('Teacher', $teacher->id);
            $this->cache->forgetByPattern('teachers:index:*');

            return $this->successResponse($teacher, 'Teacher created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.CREATION_FAILED', 'RES_002'), null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $cacheKey = "teacher:{$id}";

            $teacher = $this->cache->remember($cacheKey, CacheService::TTL_LONG, function () use ($id) {
                return Teacher::with(['subject', 'class'])->find($id);
            });

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            return $this->successResponse($teacher, 'Teacher retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $teacher = Teacher::find($id);

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $data = $this->request->all();

            if (isset($data['nip']) && $data['nip'] !== $teacher->nip) {
                $existingTeacher = Teacher::where('nip', $data['nip'])->first();
                if ($existingTeacher) {
                    return $this->validationErrorResponse(['nip' => ['The NIP has already been taken.']]);
                }
            }

            if (isset($data['email']) && $data['email'] && $data['email'] !== $teacher->email) {
                $existingTeacher = Teacher::where('email', $data['email'])->first();
                if ($existingTeacher) {
                    return $this->validationErrorResponse(['email' => ['The email has already been taken.']]);
                }
            }

            $teacher->update($data);

            $this->cache->forgetModel('Teacher', $id);
            $this->cache->forgetByPattern('teachers:index:*');

            return $this->successResponse($teacher, 'Teacher updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.UPDATE_FAILED', 'RES_003'), null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $teacher = Teacher::find($id);

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $teacher->delete();

            $this->cache->forgetModel('Teacher', $id);
            $this->cache->forgetByPattern('teachers:index:*');

            return $this->successResponse(null, 'Teacher deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.DELETION_FAILED', 'RES_004'), null, 400);
        }
    }
}
