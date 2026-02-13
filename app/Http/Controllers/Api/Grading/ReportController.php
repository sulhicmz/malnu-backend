<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Grading;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grading\Report;
use App\Models\Grading\ReportTemplate;
use App\Services\ReportGenerationService;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Report",
 *     description="Report generation and management endpoints"
 * )
 */
class ReportController extends BaseController
{
    private ReportGenerationService $reportService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ReportGenerationService $reportService
    ) {
        parent::__construct($request, $response, $container);
        $this->reportService = $reportService;
    }

    /**
     * Generate a report card for a student.
     *
     * @OA\Post(
     *     path="/api/reports/report-cards",
     *     summary="Generate report card",
     *     tags={"Report"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "class_id", "semester", "academic_year"},
     *             @OA\Property(property="student_id", type="string"),
     *             @OA\Property(property="class_id", type="string"),
     *             @OA\Property(property="semester", type="integer"),
     *             @OA\Property(property="academic_year", type="string"),
     *             @OA\Property(property="template_id", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Report card generated successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Student or class not found")
     * )
     */
    public function generateReportCard(): ResponseInterface
    {
        $data = $this->request->all();

        $required = ['student_id', 'class_id', 'semester', 'academic_year'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("{$field} is required", 'VALIDATION_ERROR', null, 400);
            }
        }

        try {
            $userId = $this->getCurrentUserId();
            $report = $this->reportService->generateReportCard(
                $data['student_id'],
                $data['class_id'],
                (int) $data['semester'],
                $data['academic_year'],
                $data['template_id'] ?? null,
                $userId
            );

            return $this->successResponse($report, 'Report card generated successfully', 201);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->serverErrorResponse('Failed to generate report card: ' . $e->getMessage());
        }
    }

    /**
     * Generate an academic transcript for a student.
     *
     * @OA\Post(
     *     path="/api/reports/transcripts",
     *     summary="Generate transcript",
     *     tags={"Report"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id"},
     *             @OA\Property(property="student_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transcript generated successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function generateTranscript(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['student_id'])) {
            return $this->errorResponse('student_id is required', 'VALIDATION_ERROR', null, 400);
        }

        try {
            $userId = $this->getCurrentUserId();
            $report = $this->reportService->generateTranscript(
                $data['student_id'],
                $userId
            );

            return $this->successResponse($report, 'Transcript generated successfully', 201);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->serverErrorResponse('Failed to generate transcript: ' . $e->getMessage());
        }
    }

    /**
     * Generate a progress report for a student.
     *
     * @OA\Post(
     *     path="/api/reports/progress-reports",
     *     summary="Generate progress report",
     *     tags={"Report"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "class_id", "semester", "academic_year"},
     *             @OA\Property(property="student_id", type="string"),
     *             @OA\Property(property="class_id", type="string"),
     *             @OA\Property(property="semester", type="integer"),
     *             @OA\Property(property="academic_year", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Progress report generated successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Student or class not found")
     * )
     */
    public function generateProgressReport(): ResponseInterface
    {
        $data = $this->request->all();

        $required = ['student_id', 'class_id', 'semester', 'academic_year'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("{$field} is required", 'VALIDATION_ERROR', null, 400);
            }
        }

        try {
            $userId = $this->getCurrentUserId();
            $report = $this->reportService->generateProgressReport(
                $data['student_id'],
                $data['class_id'],
                (int) $data['semester'],
                $data['academic_year'],
                $userId
            );

            return $this->successResponse($report, 'Progress report generated successfully', 201);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->serverErrorResponse('Failed to generate progress report: ' . $e->getMessage());
        }
    }

    /**
     * Batch generate report cards for all students in a class.
     *
     * @OA\Post(
     *     path="/api/reports/batch-report-cards",
     *     summary="Generate batch report cards",
     *     tags={"Report"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"class_id", "semester", "academic_year"},
     *             @OA\Property(property="class_id", type="string"),
     *             @OA\Property(property="semester", type="integer"),
     *             @OA\Property(property="academic_year", type="string"),
     *             @OA\Property(property="template_id", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Batch report cards generated successfully")
     * )
     */
    public function batchGenerateReportCards(): ResponseInterface
    {
        $data = $this->request->all();

        $required = ['class_id', 'semester', 'academic_year'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("{$field} is required", 'VALIDATION_ERROR', null, 400);
            }
        }

        try {
            $userId = $this->getCurrentUserId();
            $results = $this->reportService->batchGenerateReportCards(
                $data['class_id'],
                (int) $data['semester'],
                $data['academic_year'],
                $data['template_id'] ?? null,
                $userId
            );

            return $this->successResponse($results, 'Batch report cards generated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to generate batch report cards: ' . $e->getMessage());
        }
    }

    /**
     * Get all reports for a student.
     *
     * @OA\Get(
     *     path="/api/reports/student/{studentId}",
     *     summary="Get student reports",
     *     tags={"Report"},
     *     @OA\Parameter(name="studentId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="semester", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="academic_year", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_published", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Student reports retrieved successfully")
     * )
     */
    public function getStudentReports(string $studentId): ResponseInterface
    {
        try {
            $semester = $this->request->query('semester');
            $academicYear = $this->request->query('academic_year');
            $isPublished = $this->request->query('is_published');

            $reports = $this->reportService->getStudentReports(
                $studentId,
                $semester ? (int) $semester : null,
                $academicYear,
                $isPublished !== null ? (bool) $isPublished : null
            );

            return $this->successResponse($reports, 'Student reports retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve student reports: ' . $e->getMessage());
        }
    }

    /**
     * Get all reports for a class.
     *
     * @OA\Get(
     *     path="/api/reports/class/{classId}",
     *     summary="Get class reports",
     *     tags={"Report"},
     *     @OA\Parameter(name="classId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="semester", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="academic_year", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Class reports retrieved successfully")
     * )
     */
    public function getClassReports(string $classId): ResponseInterface
    {
        try {
            $semester = $this->request->query('semester');
            $academicYear = $this->request->query('academic_year');

            $reports = $this->reportService->getClassReports(
                $classId,
                $semester ? (int) $semester : null,
                $academicYear
            );

            return $this->successResponse($reports, 'Class reports retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve class reports: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific report by ID.
     *
     * @OA\Get(
     *     path="/api/reports/{id}",
     *     summary="Get report by ID",
     *     tags={"Report"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Report retrieved successfully"),
     *     @OA\Response(response=404, description="Report not found")
     * )
     */
    public function getReport(string $id): ResponseInterface
    {
        try {
            $report = Report::with(['student', 'class', 'template', 'signatures'])->find($id);

            if (! $report) {
                return $this->notFoundResponse('Report not found');
            }

            return $this->successResponse($report, 'Report retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve report: ' . $e->getMessage());
        }
    }

    /**
     * Publish a report.
     *
     * @OA\Post(
     *     path="/api/reports/{id}/publish",
     *     summary="Publish report",
     *     tags={"Report"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Report published successfully"),
     *     @OA\Response(response=404, description="Report not found")
     * )
     */
    public function publishReport(string $id): ResponseInterface
    {
        try {
            $report = $this->reportService->publishReport($id);
            return $this->successResponse($report, 'Report published successfully');
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->serverErrorResponse('Failed to publish report: ' . $e->getMessage());
        }
    }

    /**
     * Add a signature to a report.
     *
     * @OA\Post(
     *     path="/api/reports/{id}/signatures",
     *     summary="Add signature to report",
     *     tags={"Report"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"signer_name", "signer_title"},
     *             @OA\Property(property="signer_name", type="string"),
     *             @OA\Property(property="signer_title", type="string"),
     *             @OA\Property(property="signature_image_url", type="string", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Signature added successfully"),
     *     @OA\Response(response=404, description="Report not found")
     * )
     */
    public function addSignature(string $id): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['signer_name']) || empty($data['signer_title'])) {
            return $this->errorResponse('signer_name and signer_title are required', 'VALIDATION_ERROR', null, 400);
        }

        try {
            $userId = $this->getCurrentUserId();
            $signature = $this->reportService->addSignature(
                $id,
                $data['signer_name'],
                $data['signer_title'],
                $data['signature_image_url'] ?? null,
                $data['notes'] ?? null,
                $userId
            );

            return $this->successResponse($signature, 'Signature added successfully', 201);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->serverErrorResponse('Failed to add signature: ' . $e->getMessage());
        }
    }

    /**
     * Create a report template.
     *
     * @OA\Post(
     *     path="/api/report-templates",
     *     summary="Create report template",
     *     tags={"Report"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "header_template", "content_template", "footer_template"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="type", type="string", enum={"report_card", "transcript", "progress_report"}),
     *             @OA\Property(property="grade_level", type="string", nullable=true),
     *             @OA\Property(property="header_template", type="string"),
     *             @OA\Property(property="content_template", type="string"),
     *             @OA\Property(property="footer_template", type="string"),
     *             @OA\Property(property="css_styles", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Template created successfully")
     * )
     */
    public function createTemplate(): ResponseInterface
    {
        $data = $this->request->all();

        $required = ['name', 'type', 'header_template', 'content_template', 'footer_template'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->errorResponse("{$field} is required", 'VALIDATION_ERROR', null, 400);
            }
        }

        try {
            $userId = $this->getCurrentUserId();
            $template = ReportTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'grade_level' => $data['grade_level'] ?? null,
                'header_template' => $data['header_template'],
                'content_template' => $data['content_template'],
                'footer_template' => $data['footer_template'],
                'css_styles' => $data['css_styles'] ?? null,
                'created_by' => $userId,
            ]);

            return $this->successResponse($template, 'Template created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create template: ' . $e->getMessage());
        }
    }

    /**
     * Get all report templates.
     *
     * @OA\Get(
     *     path="/api/report-templates",
     *     summary="Get report templates",
     *     tags={"Report"},
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="grade_level", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Templates retrieved successfully")
     * )
     */
    public function getTemplates(): ResponseInterface
    {
        try {
            $type = $this->request->query('type');
            $gradeLevel = $this->request->query('grade_level');

            $query = ReportTemplate::query();

            if ($type) {
                $query->where('type', $type);
            }

            if ($gradeLevel) {
                $query->where('grade_level', $gradeLevel);
            }

            $templates = $query->active()->get();

            return $this->successResponse($templates, 'Templates retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve templates: ' . $e->getMessage());
        }
    }

    /**
     * Get current authenticated user ID.
     */
    private function getCurrentUserId(): ?string
    {
        $user = $this->request->getAttribute('user');
        return $user?->id;
    }
}
