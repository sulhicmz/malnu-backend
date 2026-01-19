<?php

declare(strict_types=1);

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Api\BaseController;
use App\Services\LmsService;

class LmsController extends BaseController
{
    public function __construct(
        \Hyperf\HttpServer\Contract\RequestInterface $request,
        \Hyperf\HttpServer\Contract\ResponseInterface $response,
        \Psr\Container\ContainerInterface $container,
        private LmsService $lmsService
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        try {
            $filters = [
                'subject_id' => $this->request->input('subject_id'),
                'teacher_id' => $this->request->input('teacher_id'),
                'status' => $this->request->input('status'),
                'is_active' => $this->request->input('is_active'),
                'search' => $this->request->input('search'),
                'per_page' => $this->request->input('per_page', 20),
            ];

            $courses = $this->lmsService->getCourses($filters);

            return $this->response->json([
                'success' => true,
                'data' => $courses->items(),
                'pagination' => [
                    'total' => $courses->total(),
                    'per_page' => $courses->perPage(),
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to fetch courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            $course = $this->lmsService->createCourse($data);

            return $this->response->json([
                'success' => true,
                'message' => 'Course created successfully',
                'data' => $course,
            ], 201);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $details = $this->lmsService->getCourseDetails($id);

            return $this->response->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to fetch course details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            
            $course = $this->lmsService->updateCourse($id, $data);

            return $this->response->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'data' => $course,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function publish(string $id)
    {
        try {
            $course = $this->lmsService->publishCourse($id);

            return $this->response->json([
                'success' => true,
                'message' => 'Course published successfully',
                'data' => $course,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to publish course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function archive(string $id)
    {
        try {
            $course = $this->lmsService->archiveCourse($id);

            return $this->response->json([
                'success' => true,
                'message' => 'Course archived successfully',
                'data' => $course,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to archive course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enroll()
    {
        try {
            $data = $this->request->all();
            
            $enrollment = $this->lmsService->enrollStudent($data['course_id'], $data['student_id']);

            return $this->response->json([
                'success' => true,
                'message' => 'Student enrolled successfully',
                'data' => $enrollment,
            ], 201);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to enroll student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activateEnrollment(string $id)
    {
        try {
            $enrollment = $this->lmsService->activateEnrollment($id);

            return $this->response->json([
                'success' => true,
                'message' => 'Enrollment activated successfully',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to activate enrollment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dropEnrollment(string $id)
    {
        try {
            $enrollment = $this->lmsService->dropCourse($id);

            return $this->response->json([
                'success' => true,
                'message' => 'Course dropped successfully',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to drop course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function recordProgress(string $enrollmentId)
    {
        try {
            $data = $this->request->all();
            
            $progress = $this->lmsService->recordLearningProgress(
                $enrollmentId,
                $data['type'],
                $data['item_id'],
                $data
            );

            return $this->response->json([
                'success' => true,
                'message' => 'Progress recorded successfully',
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to record progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completeCourse(string $enrollmentId)
    {
        try {
            $data = $this->request->all();
            $finalGrade = $data['final_grade'] ?? null;
            
            $enrollment = $this->lmsService->completeCourse($enrollmentId, $finalGrade);

            return $this->response->json([
                'success' => true,
                'message' => 'Course completed successfully',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to complete course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function studentEnrollments(string $studentId)
    {
        try {
            $filters = [
                'status' => $this->request->input('status'),
                'per_page' => $this->request->input('per_page', 20),
            ];

            $enrollments = $this->lmsService->getStudentEnrollments($studentId, $filters);

            return $this->response->json([
                'success' => true,
                'data' => $enrollments->items(),
                'pagination' => [
                    'total' => $enrollments->total(),
                    'per_page' => $enrollments->perPage(),
                    'current_page' => $enrollments->currentPage(),
                    'last_page' => $enrollments->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to fetch enrollments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function studentProgress(string $enrollmentId)
    {
        try {
            $progress = $this->lmsService->getStudentProgress($enrollmentId);

            return $this->response->json([
                'success' => true,
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to fetch student progress',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function courseAnalytics(string $courseId)
    {
        try {
            $analytics = $this->lmsService->getCourseAnalytics($courseId);

            return $this->response->json([
                'success' => true,
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to fetch course analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
