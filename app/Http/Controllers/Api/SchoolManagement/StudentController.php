<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Contracts\CacheServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentController extends BaseController
{
    private CacheServiceInterface $cacheService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
        $this->cacheService = $container->get(CacheServiceInterface::class);
    }

    /**
     * Display a listing of the students.
     */
    public function index()
    {
        try {
            $cacheService = $this->cacheService;

            $params = [
                'class_id' => $this->request->query('class_id'),
                'status' => $this->request->query('status'),
                'search' => $this->request->query('search'),
                'page' => (int) $this->request->query('page', 1),
                'limit' => (int) $this->request->query('limit', 15),
            ];

            $cacheKey = $cacheService->generateKey('students:list', $params);

            $students = $cacheService->remember($cacheKey, function () use ($params) {
                $query = Student::with(['class']);

                if ($params['class_id']) {
                    $query->where('class_id', $params['class_id']);
                }

                if ($params['status']) {
                    $query->where('status', $params['status']);
                }

                if ($params['search']) {
                    $query->where(function ($q) use ($params) {
                        $q->where('name', 'like', "%{$params['search']}%")
                            ->orWhere('nisn', 'like', "%{$params['search']}%");
                    });
                }

                return $query->orderBy('name', 'asc')->paginate($params['limit'], ['*'], 'page', $params['page']);
            }, 300);

            return $this->successResponse($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Store a newly created student.
     */
    public function store()
    {
        try {
            $cacheService = $this->cacheService;

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

            $cacheService->forget($cacheService->getPrefix() . ':students:list');

            return $this->successResponse($student, 'Student created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::STUDENT_CREATION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::STUDENT_CREATION_ERROR));
        }
    }

    /**
     * Display the specified student.
     */
    public function show(string $id)
    {
        try {
            $cacheService = $this->cacheService;
            $cacheKey = $cacheService->getPrefix() . ":student:{$id}";

            $student = $cacheService->remember($cacheKey, function () use ($id) {
                return Student::with(['class'])->find($id);
            }, 600);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            return $this->successResponse($student, 'Student retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified student.
     */
    public function update(string $id)
    {
        try {
            $cacheService = $this->cacheService;

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

            $cacheService->forget($cacheService->getPrefix() . ":student:{$id}");
            $cacheService->forget($cacheService->getPrefix() . ':students:list');

            return $this->successResponse($student, 'Student updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::STUDENT_UPDATE_ERROR, null, ErrorCode::getStatusCode(ErrorCode::STUDENT_UPDATE_ERROR));
        }
    }

    /**
     * Remove the specified student.
     */
    public function destroy(string $id)
    {
        try {
            $cacheService = $this->cacheService;

            $student = Student::find($id);

            if (! $student) {
                return $this->notFoundResponse('Student not found');
            }

            $student->delete();

            $cacheService->forget($cacheService->getPrefix() . ":student:{$id}");
            $cacheService->forget($cacheService->getPrefix() . ':students:list');

            return $this->successResponse(null, 'Student deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::STUDENT_DELETION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::STUDENT_DELETION_ERROR));
        }
    }
}
