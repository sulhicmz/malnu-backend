<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Display a listing of the students.
     */
    public function index()
    {
        try {
            $query = Student::with(['class']);

            // Get query parameters
            $classId = $this->request->query('class_id');
            $status = $this->request->query('status');
            $search = $this->request->query('search');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            // Filter by class if provided
            if ($classId) {
                $query->where('class_id', $classId);
            }

            // Filter by status if provided
            if ($status) {
                $query->where('status', $status);
            }

            // Search by name or NISN if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nisn', 'like', "%{$search}%");
                });
            }

            $students = $query->orderBy('name', 'asc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($students, 'Students retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Store a newly created student.
     */
    public function store()
    {
        try {
            $data = $this->request->all();

            // Validate required fields
            $requiredFields = ['name', 'nisn', 'class_id', 'enrollment_year', 'status'];
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

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $student = Student::create($data);

            return $this->successResponse($student, 'Student created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_CREATION_ERROR', null, 400);
        }
    }

    /**
     * Display the specified student.
     */
    public function show(string $id)
    {
        try {
            $student = Student::with(['class'])->find($id);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            return $this->successResponse($student, 'Student retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified student.
     */
    public function update(string $id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();

            // Validate unique fields if they are being updated
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

            return $this->successResponse($student, 'Student updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_UPDATE_ERROR', null, 400);
        }
    }

    /**
     * Remove the specified student.
     */
    public function destroy(string $id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $student->delete();

            return $this->successResponse(null, 'Student deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_DELETION_ERROR', null, 400);
        }
    }
}