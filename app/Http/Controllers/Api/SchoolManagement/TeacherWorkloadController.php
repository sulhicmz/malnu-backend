<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\TeacherWorkload;
use App\Services\SchoolManagement\TeacherWorkloadService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherWorkloadController extends BaseController
{
    protected TeacherWorkloadService $workloadService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        TeacherWorkloadService $workloadService
    ) {
        parent::__construct($request, $response, $container);
        $this->workloadService = $workloadService;
    }

    public function index()
    {
        try {
            $query = TeacherWorkload::with(['teacher', 'teacher.user']);

            $teacherId = $this->request->query('teacher_id');
            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester');
            $status = $this->request->query('workload_status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($teacherId) {
                $query->where('teacher_id', $teacherId);
            }

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            if ($semester) {
                $query->where('semester', $semester);
            }

            if ($status) {
                $query->where('workload_status', $status);
            }

            $workloads = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($workloads, 'Teacher workloads retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $workload = TeacherWorkload::with(['teacher', 'teacher.user'])->find($id);

            if (! $workload) {
                return $this->notFoundResponse('Teacher workload not found');
            }

            return $this->successResponse($workload, 'Teacher workload retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['teacher_id', 'academic_year', 'semester'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $existingWorkload = TeacherWorkload::where('teacher_id', $data['teacher_id'])
                ->where('academic_year', $data['academic_year'])
                ->where('semester', $data['semester'])
                ->first();

            if ($existingWorkload) {
                return $this->errorResponse(
                    'Workload record already exists for this teacher in the specified academic period',
                    'DUPLICATE_WORKLOAD',
                    null,
                    409
                );
            }

            $workload = $this->workloadService->createWorkload($data);

            return $this->successResponse(
                $workload->load(['teacher', 'teacher.user']),
                'Teacher workload created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $workload = TeacherWorkload::find($id);

            if (! $workload) {
                return $this->notFoundResponse('Teacher workload not found');
            }

            $data = $this->request->all();

            $workload = $this->workloadService->updateWorkload($workload, $data);

            return $this->successResponse(
                $workload->load(['teacher', 'teacher.user']),
                'Teacher workload updated successfully'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $workload = TeacherWorkload::find($id);

            if (! $workload) {
                return $this->notFoundResponse('Teacher workload not found');
            }

            $workload->delete();

            return $this->successResponse(null, 'Teacher workload deleted successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getByTeacher(string $teacherId)
    {
        try {
            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester');

            $query = TeacherWorkload::with(['teacher', 'teacher.user'])
                ->where('teacher_id', $teacherId);

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            if ($semester) {
                $query->where('semester', $semester);
            }

            $workloads = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($workloads, 'Teacher workloads retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function calculateFromSchedule(string $teacherId)
    {
        try {
            $academicYear = $this->request->input('academic_year');
            $semester = $this->request->input('semester');

            if (! $academicYear || ! $semester) {
                return $this->validationErrorResponse([
                    'academic_year' => ['The academic year is required'],
                    'semester' => ['The semester is required'],
                ]);
            }

            $workload = $this->workloadService->calculateFromSchedule($teacherId, $academicYear, $semester);

            return $this->successResponse(
                $workload->load(['teacher', 'teacher.user']),
                'Teacher workload calculated from schedule successfully'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getWorkloadSummary()
    {
        try {
            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester');

            if (! $academicYear || ! $semester) {
                return $this->validationErrorResponse([
                    'academic_year' => ['The academic year is required'],
                    'semester' => ['The semester is required'],
                ]);
            }

            $summary = $this->workloadService->getWorkloadSummary($academicYear, $semester);

            return $this->successResponse($summary, 'Workload summary retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getOverloadedTeachers()
    {
        try {
            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester');

            $query = TeacherWorkload::with(['teacher', 'teacher.user'])
                ->whereRaw('total_hours_per_week > max_hours_per_week');

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            if ($semester) {
                $query->where('semester', $semester);
            }

            $overloaded = $query->get();

            return $this->successResponse($overloaded, 'Overloaded teachers retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getUnderloadedTeachers()
    {
        try {
            $academicYear = $this->request->query('academic_year');
            $semester = $this->request->query('semester');

            $query = TeacherWorkload::with(['teacher', 'teacher.user'])
                ->whereRaw('total_hours_per_week < (max_hours_per_week * 0.5)');

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            if ($semester) {
                $query->where('semester', $semester);
            }

            $underloaded = $query->get();

            return $this->successResponse($underloaded, 'Underloaded teachers retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
