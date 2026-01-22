<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\LMSService;
use Hyperf\HttpServer\Contract\RequestInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="LMS",
 *     description="Learning Management System endpoints"
 * )
 */
class LMSController extends BaseController
{
    private LMSService $lmsService;

    public function __construct(RequestInterface $request, LMSService $lmsService)
    {
        parent::__construct($request);
        $this->lmsService = $lmsService;
    }

    /**
     * Create course
     * 
     * @OA\Post(
     *     path="/api/lms/courses",
     *     tags={"LMS"},
     *     summary="Create course",
     *     description="Create a new course",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Introduction to Mathematics"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="virtual_class_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="level", type="string", enum={"beginner","intermediate","advanced"}, default="beginner"),
     *             @OA\Property(property="duration_hours", type="integer", nullable=true),
     *             @OA\Property(property="is_published", type="boolean", default=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function createCourse(RequestInterface $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'virtual_class_id' => 'nullable|string',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_hours' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $course = $this->lmsService->createCourse($data);

        return $this->successResponse($course, 'Course created successfully');
    }

    /**
     * Get courses
     * 
     * @OA\Get(
     *     path="/api/lms/courses",
     *     tags={"LMS"},
     *     summary="Get courses",
     *     description="Retrieve all courses",
     *     @OA\Parameter(
     *         name="published",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courses retrieved successfully"
     *     )
     * )
     */
    public function getCourses(RequestInterface $request)
    {
        $publishedOnly = $request->input('published', true);

        $courses = $this->lmsService->getAllCourses((bool) $publishedOnly);

        return $this->successResponse($courses, 'Courses retrieved successfully');
    }

    /**
     * Get course
     * 
     * @OA\Get(
     *     path="/api/lms/courses/{courseId}",
     *     tags={"LMS"},
     *     summary="Get course",
     *     description="Retrieve a specific course",
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course retrieved successfully"
     *     )
     * )
     */
    public function getCourse(RequestInterface $request, string $courseId)
    {
        $course = $this->lmsService->getCourse($courseId);

        if (!$course) {
            return $this->notFoundResponse('Course not found');
        }

        return $this->successResponse($course, 'Course retrieved successfully');
    }

    /**
     * Update course
     * 
     * @OA\Put(
     *     path="/api/lms/courses/{courseId}",
     *     tags={"LMS"},
     *     summary="Update course",
     *     description="Update an existing course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully"
     *     )
     * )
     */
    public function updateCourse(RequestInterface $request, string $courseId)
    {
        $data = $request->all();

        $validator = validator($data, [
            'name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'duration_hours' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $course = $this->lmsService->updateCourse($courseId, $data);

        if (!$course) {
            return $this->notFoundResponse('Course not found');
        }

        return $this->successResponse($course, 'Course updated successfully');
    }

    /**
     * Create learning path
     * 
     * @OA\Post(
     *     path="/api/lms/learning-paths",
     *     tags={"LMS"},
     *     summary="Create learning path",
     *     description="Create a new learning path",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Learning path created successfully"
     *     )
     * )
     */
    public function createLearningPath(RequestInterface $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $path = $this->lmsService->createLearningPath($data);

        return $this->successResponse($path, 'Learning path created successfully');
    }

    /**
     * Get learning paths
     * 
     * @OA\Get(
     *     path="/api/lms/learning-paths",
     *     tags={"LMS"},
     *     summary="Get learning paths",
     *     description="Retrieve all learning paths",
     *     @OA\Response(
     *         response=200,
     *         description="Learning paths retrieved successfully"
     *     )
     * )
     */
    public function getLearningPaths()
    {
        $paths = $this->lmsService->getAllLearningPaths();

        return $this->successResponse($paths, 'Learning paths retrieved successfully');
    }

    /**
     * Get learning path
     * 
     * @OA\Get(
     *     path="/api/lms/learning-paths/{pathId}",
     *     tags={"LMS"},
     *     summary="Get learning path",
     *     description="Retrieve a specific learning path with courses",
     *     @OA\Parameter(
     *         name="pathId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Learning path retrieved successfully"
     *     )
     * )
     */
    public function getLearningPath(RequestInterface $request, string $pathId)
    {
        $path = $this->lmsService->getLearningPath($pathId);

        if (!$path) {
            return $this->notFoundResponse('Learning path not found');
        }

        return $this->successResponse($path, 'Learning path retrieved successfully');
    }

    /**
     * Enroll student
     * 
     * @OA\Post(
     *     path="/api/lms/courses/{courseId}/enroll",
     *     tags={"LMS"},
     *     summary="Enroll student",
     *     description="Enroll a student in a course",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id"},
     *             @OA\Property(property="student_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student enrolled successfully"
     *     )
     * )
     */
    public function enrollStudent(RequestInterface $request, string $courseId)
    {
        $data = $request->all();

        $validator = validator($data, [
            'student_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $enrollment = $this->lmsService->enrollStudent($courseId, $data['student_id']);

        return $this->successResponse($enrollment, 'Student enrolled successfully');
    }

    /**
     * Get enrollments
     * 
     * @OA\Get(
     *     path="/api/lms/enrollments",
     *     tags={"LMS"},
     *     summary="Get enrollments",
     *     description="Retrieve all enrollments",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollments retrieved successfully"
     *     )
     * )
     */
    public function getEnrollments(RequestInterface $request)
    {
        $courseId = $request->input('course_id');

        $enrollments = $this->lmsService->getEnrollments($courseId);

        return $this->successResponse($enrollments, 'Enrollments retrieved successfully');
    }

    /**
     * Get course progress
     * 
     * @OA\Get(
     *     path="/api/lms/courses/{courseId}/progress",
     *     tags={"LMS"},
     *     summary="Get course progress",
     *     description="Retrieve progress for a specific course",
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress retrieved successfully"
     *     )
     * )
     */
    public function getCourseProgress(RequestInterface $request, string $courseId)
    {
        $enrollments = $this->lmsService->getEnrollments($courseId);

        return $this->successResponse($enrollments, 'Course progress retrieved successfully');
    }

    /**
     * Update progress
     * 
     * @OA\Put(
     *     path="/api/lms/progress/{enrollmentId}",
     *     tags={"LMS"},
     *     summary="Update progress",
     *     description="Update student progress",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress updated successfully"
     *     )
     * )
     */
    public function updateProgress(RequestInterface $request, string $enrollmentId)
    {
        $data = $request->all();

        $validator = validator($data, [
            'completed_lessons' => 'nullable|integer|min:0',
            'completed_assignments' => 'nullable|integer|min:0',
            'completed_quizzes' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $progress = $this->lmsService->updateProgress($enrollmentId, $data);

        if (!$progress) {
            return $this->notFoundResponse('Enrollment not found');
        }

        return $this->successResponse($progress, 'Progress updated successfully');
    }

    /**
     * Complete course
     * 
     * @OA\Post(
     *     path="/api/lms/progress/{enrollmentId}/complete",
     *     tags={"LMS"},
     *     summary="Complete course",
     *     description="Mark course as completed and issue certificate",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course completed successfully"
     *     )
     * )
     */
    public function completeCourse(RequestInterface $request, string $enrollmentId)
    {
        $enrollment = $this->lmsService->completeCourse($enrollmentId);

        if (!$enrollment) {
            return $this->notFoundResponse('Enrollment not found');
        }

        return $this->successResponse($enrollment, 'Course completed successfully');
    }

    /**
     * Get certificates
     * 
     * @OA\Get(
     *     path="/api/lms/certificates",
     *     tags={"LMS"},
     *     summary="Get certificates",
     *     description="Retrieve all certificates",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificates retrieved successfully"
     *     )
     * )
     */
    public function getCertificates(RequestInterface $request)
    {
        $courseId = $request->input('course_id');
        $studentId = $request->input('student_id');

        $certificates = $this->lmsService->getCertificates($courseId, $studentId);

        return $this->successResponse($certificates, 'Certificates retrieved successfully');
    }
}
