<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Services\AcademicRecordService;
use App\Services\EnrollmentService;
use App\Services\PerformanceAnalyticsService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StudentController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        protected AcademicRecordService $academicRecordService,
        protected EnrollmentService $enrollmentService,
        protected PerformanceAnalyticsService $performanceAnalyticsService
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

    public function calculateGpa(string $id)
    {
        try {
            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;
            $gpaData = $this->academicRecordService->calculateGPA($id, $semester);

            return $this->successResponse($gpaData, 'GPA calculated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'GPA_CALCULATION_ERROR', null, 400);
        }
    }

    public function generateTranscript(string $id)
    {
        try {
            $transcript = $this->academicRecordService->generateTranscript($id);

            return $this->successResponse($transcript, 'Transcript generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSCRIPT_GENERATION_ERROR', null, 400);
        }
    }

    public function getStudentProgress(string $id)
    {
        try {
            $progress = $this->academicRecordService->getStudentProgress($id);

            return $this->successResponse($progress, 'Student progress retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PROGRESS_RETRIEVAL_ERROR', null, 400);
        }
    }

    public function updateEnrollmentStatus(string $id)
    {
        try {
            $data = $this->request->all();

            if (empty($data['status'])) {
                return $this->validationErrorResponse(['status' => ['The status field is required.']]);
            }

            $student = $this->enrollmentService->updateEnrollmentStatus($id, $data['status']);

            return $this->successResponse($student, 'Enrollment status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENROLLMENT_UPDATE_ERROR', null, 400);
        }
    }

    public function assignToClass(string $id)
    {
        try {
            $data = $this->request->all();

            if (empty($data['class_id'])) {
                return $this->validationErrorResponse(['class_id' => ['The class_id field is required.']]);
            }

            $student = $this->enrollmentService->assignToClass($id, $data['class_id']);

            return $this->successResponse($student, 'Student assigned to class successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLASS_ASSIGNMENT_ERROR', null, 400);
        }
    }

    public function getEnrollmentHistory(string $id)
    {
        try {
            $history = $this->enrollmentService->getEnrollmentHistory($id);

            return $this->successResponse($history, 'Enrollment history retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENROLLMENT_HISTORY_ERROR', null, 400);
        }
    }

    public function getEnrollmentStats()
    {
        try {
            $stats = $this->enrollmentService->getEnrollmentStatistics();

            return $this->successResponse($stats, 'Enrollment statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STATISTICS_RETRIEVAL_ERROR', null, 400);
        }
    }

    public function getClassEnrollment(string $classId)
    {
        try {
            $enrollment = $this->enrollmentService->getClassEnrollment($classId);

            return $this->successResponse($enrollment, 'Class enrollment retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLASS_ENROLLMENT_ERROR', null, 400);
        }
    }

    public function getStudentPerformance(string $id)
    {
        try {
            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;
            $performance = $this->performanceAnalyticsService->getStudentPerformance($id, $semester);

            return $this->successResponse($performance, 'Student performance retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PERFORMANCE_RETRIEVAL_ERROR', null, 400);
        }
    }

    public function getClassPerformance(string $classId)
    {
        try {
            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;
            $performance = $this->performanceAnalyticsService->getClassPerformance($classId, $semester);

            return $this->successResponse($performance, 'Class performance retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLASS_PERFORMANCE_ERROR', null, 400);
        }
    }

    public function getComparativeAnalysis(string $id)
    {
        try {
            $semester = $this->request->query('semester') ? (int) $this->request->query('semester') : null;
            $analysis = $this->performanceAnalyticsService->getComparativeAnalysis($id, $semester);

            return $this->successResponse($analysis, 'Comparative analysis retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ANALYSIS_RETRIEVAL_ERROR', null, 400);
        }
    }
}