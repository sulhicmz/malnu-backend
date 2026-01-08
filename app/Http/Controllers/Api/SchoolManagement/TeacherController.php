<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Contracts\CacheServiceInterface;
use App\Enums\ErrorCode;
use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Teacher;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherController extends BaseController
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
     * Display a listing of the teachers.
     */
    public function index()
    {
        try {
            $cacheService = $this->cacheService;

            $params = [
                'subject_id' => $this->request->query('subject_id'),
                'class_id' => $this->request->query('class_id'),
                'status' => $this->request->query('status'),
                'search' => $this->request->query('search'),
                'page' => (int) $this->request->query('page', 1),
                'limit' => (int) $this->request->query('limit', 15),
            ];

            $cacheKey = $cacheService->generateKey('teachers:list', $params);

            $teachers = $cacheService->remember($cacheKey, function () use ($params) {
                $query = Teacher::with(['subject', 'class']);

                if ($params['subject_id']) {
                    $query->where('subject_id', $params['subject_id']);
                }

                if ($params['class_id']) {
                    $query->where('class_id', $params['class_id']);
                }

                if ($params['status']) {
                    $query->where('status', $params['status']);
                }

                if ($params['search']) {
                    $query->where(function ($q) use ($params) {
                        $q->where('name', 'like', "%{$params['search']}%")
                            ->orWhere('nip', 'like', "%{$params['search']}%");
                    });
                }

                return $query->orderBy('name', 'asc')->paginate($params['limit'], ['*'], 'page', $params['page']);
            }, 300);

            return $this->successResponse($teachers, 'Teachers retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Store a newly created teacher.
     */
    public function store()
    {
        try {
            $cacheService = $this->cacheService;
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

            $cacheService->forget($cacheService->getPrefix() . ':teachers:list');

            return $this->successResponse($teacher, 'Teacher created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::TEACHER_CREATION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::TEACHER_CREATION_ERROR));
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(string $id)
    {
        try {
            $cacheService = $this->cacheService;
            $cacheKey = $cacheService->getPrefix() . ":teacher:{$id}";

            $teacher = $cacheService->remember($cacheKey, function () use ($id) {
                return Teacher::with(['subject', 'class'])->find($id);
            }, 600);

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            return $this->successResponse($teacher, 'Teacher retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified teacher.
     */
    public function update(string $id)
    {
        try {
            $cacheService = $this->cacheService;

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

            $cacheService->forget($cacheService->getPrefix() . ":teacher:{$id}");
            $cacheService->forget($cacheService->getPrefix() . ':teachers:list');

            return $this->successResponse($teacher, 'Teacher updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::TEACHER_UPDATE_ERROR, null, ErrorCode::getStatusCode(ErrorCode::TEACHER_UPDATE_ERROR));
        }
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(string $id)
    {
        try {
            $cacheService = $this->cacheService;

            $teacher = Teacher::find($id);

            if (! $teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $teacher->delete();

            $cacheService->forget($cacheService->getPrefix() . ":teacher:{$id}");
            $cacheService->forget($cacheService->getPrefix() . ':teachers:list');

            return $this->successResponse(null, 'Teacher deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::TEACHER_DELETION_ERROR, null, ErrorCode::getStatusCode(ErrorCode::TEACHER_DELETION_ERROR));
        }
    }
}
