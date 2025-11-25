<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Teacher;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Display a listing of the teachers.
     */
    public function index()
    {
        try {
            $query = Teacher::with(['subject', 'class']);

            // Get query parameters
            $subjectId = $this->request->query('subject_id');
            $classId = $this->request->query('class_id');
            $status = $this->request->query('status');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            // Filter by subject if provided
            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            // Filter by class if provided
            if ($classId) {
                $query->where('class_id', $classId);
            }

            // Filter by status if provided
            if ($status) {
                $query->where('status', $status);
            }

            // Search by name or NIP if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            }

            $teachers = $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($teachers, 'Teachers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Store a newly created teacher.
     */
    public function store()
    {
        try {
            $data = $this->request->all();

            // Validate required fields
            $requiredFields = ['name', 'nip', 'subject_id', 'join_date'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            // Additional validation
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
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

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $teacher = Teacher::create($data);

            return $this->successResponse($teacher, 'Teacher created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_CREATION_ERROR', null, 400);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(string $id)
    {
        try {
            $teacher = Teacher::with(['subject', 'class'])->find($id);

            if (!$teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            return $this->successResponse($teacher, 'Teacher retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified teacher.
     */
    public function update(string $id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $data = $this->request->all();

            // Validate unique fields if they are being updated
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

            return $this->successResponse($teacher, 'Teacher updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_UPDATE_ERROR', null, 400);
        }
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(string $id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return $this->notFoundResponse('Teacher not found');
            }

            $teacher->delete();

            return $this->successResponse(null, 'Teacher deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_DELETION_ERROR', null, 400);
        }
    }
}