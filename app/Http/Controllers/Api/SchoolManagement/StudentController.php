<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Services\CacheService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentController extends BaseController
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
            $classId = $this->request->query('class_id');
            $status = $this->request->query('status');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            $cacheKey = "students:index:{$classId}:{$status}:{$search}:{$page}:{$limit}";

            $students = $this->cache->remember($cacheKey, CacheService::TTL_SHORT, function () use ($classId, $status, $search, $page, $limit) {
                $query = Student::with(['class']);

                if ($classId) {
                    $query->where('class_id', $classId);
                }

                if ($status) {
                    $query->where('status', $status);
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
                }

                return $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);
            });

            return $this->successResponse($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'nisn', 'class_id', 'enrollment_year', 'status'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (isset($data['nisn'])) {
                $existingStudent = Student::where('nisn', $data['nisn'])->first();
                if ($existingStudent) {
                    $errors['nisn'] = ['The NISN has already been taken.'];
                }
            }

            if (isset($data['email']) && $data['email']) {
                $existingStudent = Student::where('email', $data['email'])->first();
                if ($existingStudent) {
                    $errors['email'] = ['The email has already been taken.'];
                }
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $student = Student::create($data);

            $this->cache->forgetModel('Student', $student->id);
            $this->cache->forgetByPattern('students:index:*');

            return $this->successResponse($student, 'Student created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.CREATION_FAILED', 'RES_002'), null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $cacheKey = "student:{$id}";

            $student = $this->cache->remember($cacheKey, CacheService::TTL_LONG, function () use ($id) {
                return Student::with(['class'])->find($id);
            });

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            return $this->successResponse($student, 'Student retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();

            if (isset($data['nisn']) && $data['nisn'] !== $student->nisn) {
                $existingStudent = Student::where('nisn', $data['nisn'])->first();
                if ($existingStudent) {
                    return $this->validationErrorResponse(['nisn' => ['The NISN has already been taken.']]);
                }
            }

            if (isset($data['email']) && $data['email'] && $data['email'] !== $student->email) {
                $existingStudent = Student::where('email', $data['email'])->first();
                if ($existingStudent) {
                    return $this->validationErrorResponse(['email' => ['The email has already been taken.']]);
                }
            }

            $student->update($data);

            $this->cache->forgetModel('Student', $id);
            $this->cache->forgetByPattern('students:index:*');

            return $this->successResponse($student, 'Student updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.UPDATE_FAILED', 'RES_003'), null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $student = Student::find($id);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $student->delete();

            $this->cache->forgetModel('Student', $id);
            $this->cache->forgetByPattern('students:index:*');

            return $this->successResponse(null, 'Student deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.DELETION_FAILED', 'RES_004'), null, 400);
        }
    }
}
