<?php

namespace App\Http\Controllers\Api\Grading;

use App\Http\Controllers\Api\BaseController;
use App\Services\ReportGenerationService;
use App\Models\Grading\ReportTemplate;
use App\Models\Grading\GeneratedReport;
use App\Models\Grading\ReportSignature;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

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

    public function generateReportCard()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateReportCardRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->reportService->generateReportCard(
                $data['student_id'],
                $data['semester'] ?? null,
                $data['academic_year'] ?? null
            );

            return $this->successResponse($result, 'Report card generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function generateTranscript()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateTranscriptRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->reportService->generateTranscript(
                $data['student_id'],
                $data['academic_year'] ?? null
            );

            return $this->successResponse($result, 'Transcript generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function generateProgressReport()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateProgressReportRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->reportService->generateProgressReport(
                $data['student_id'],
                $data['semester'],
                $data['academic_year']
            );

            return $this->successResponse($result, 'Progress report generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function generateClassReports()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateClassReportsRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->reportService->generateClassReports(
                $data['class_id'],
                $data['semester'],
                $data['academic_year']
            );

            return $this->successResponse($result, 'Class reports generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getStudentReports(string $studentId)
    {
        try {
            $query = GeneratedReport::where('student_id', $studentId);

            $reportType = $this->request->query('report_type');
            $semester = $this->request->query('semester');
            $academicYear = $this->request->query('academic_year');

            if ($reportType) {
                $query->where('report_type', $reportType);
            }
            if ($semester) {
                $query->where('semester', $semester);
            }
            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            $reports = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($reports, 'Student reports retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getClassReports(string $classId)
    {
        try {
            $query = GeneratedReport::with(['student'])
                ->whereHas('student', fn($q) => $q->where('class_id', $classId));

            $reportType = $this->request->query('report_type');
            $semester = $this->request->query('semester');
            $academicYear = $this->request->query('academic_year');

            if ($reportType) {
                $query->where('report_type', $reportType);
            }
            if ($semester) {
                $query->where('semester', $semester);
            }
            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            $reports = $query->orderBy('created_at', 'desc')->get();

            return $this->successResponse($reports, 'Class reports retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getReport(string $id)
    {
        try {
            $report = GeneratedReport::with(['student', 'template'])->find($id);

            if (!$report) {
                return $this->notFoundResponse('Report not found');
            }

            return $this->successResponse($report, 'Report retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getTemplates()
    {
        try {
            $type = $this->request->query('type');
            $gradeLevel = $this->request->query('grade_level');

            $query = ReportTemplate::active();

            if ($type) {
                $query->where('type', $type);
            }
            if ($gradeLevel) {
                $query->where('grade_level', $gradeLevel);
            }

            $templates = $query->get();

            return $this->successResponse($templates, 'Templates retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createTemplate()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateTemplateRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $template = ReportTemplate::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'html_template' => $data['html_template'],
                'variables' => json_encode($data['variables'] ?? []),
                'grade_level' => $data['grade_level'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $this->getCurrentUserId(),
            ]);

            return $this->successResponse($template, 'Template created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateTemplate(string $id)
    {
        try {
            $template = ReportTemplate::find($id);

            if (!$template) {
                return $this->notFoundResponse('Template not found');
            }

            $data = $this->request->all();
            $template->update([
                'name' => $data['name'] ?? $template->name,
                'html_template' => $data['html_template'] ?? $template->html_template,
                'variables' => isset($data['variables']) ? json_encode($data['variables']) : $template->variables,
                'grade_level' => $data['grade_level'] ?? $template->grade_level,
                'is_active' => $data['is_active'] ?? $template->is_active,
            ]);

            return $this->successResponse($template, 'Template updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function deleteTemplate(string $id)
    {
        try {
            $template = ReportTemplate::find($id);

            if (!$template) {
                return $this->notFoundResponse('Template not found');
            }

            $template->delete();

            return $this->successResponse(null, 'Template deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getSignatures()
    {
        try {
            $type = $this->request->query('type');

            $query = ReportSignature::query();

            if ($type) {
                $query->where('signature_type', $type);
            }

            $signatures = $query->orderBy('is_default', 'desc')->get();

            return $this->successResponse($signatures, 'Signatures retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createSignature()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateSignatureRequest($data);

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $signature = ReportSignature::create([
                'name' => $data['name'],
                'title' => $data['title'] ?? null,
                'signature_type' => $data['signature_type'],
                'signature_image' => $data['signature_image'] ?? null,
                'signature_image_path' => $data['signature_image_path'] ?? null,
                'is_default' => $data['is_default'] ?? false,
                'metadata' => json_encode($data['metadata'] ?? []),
                'created_by' => $this->getCurrentUserId(),
            ]);

            return $this->successResponse($signature, 'Signature created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function publishReport(string $id)
    {
        try {
            $report = GeneratedReport::find($id);

            if (!$report) {
                return $this->notFoundResponse('Report not found');
            }

            $report->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            return $this->successResponse($report, 'Report published successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    private function validateReportCardRequest(array $data): array
    {
        $errors = [];
        if (empty($data['student_id'])) {
            $errors['student_id'] = ['The student_id field is required.'];
        }
        return $errors;
    }

    private function validateTranscriptRequest(array $data): array
    {
        $errors = [];
        if (empty($data['student_id'])) {
            $errors['student_id'] = ['The student_id field is required.'];
        }
        return $errors;
    }

    private function validateProgressReportRequest(array $data): array
    {
        $errors = [];
        if (empty($data['student_id'])) {
            $errors['student_id'] = ['The student_id field is required.'];
        }
        if (empty($data['semester'])) {
            $errors['semester'] = ['The semester field is required.'];
        }
        if (empty($data['academic_year'])) {
            $errors['academic_year'] = ['The academic_year field is required.'];
        }
        return $errors;
    }

    private function validateClassReportsRequest(array $data): array
    {
        $errors = [];
        if (empty($data['class_id'])) {
            $errors['class_id'] = ['The class_id field is required.'];
        }
        if (empty($data['semester'])) {
            $errors['semester'] = ['The semester field is required.'];
        }
        if (empty($data['academic_year'])) {
            $errors['academic_year'] = ['The academic_year field is required.'];
        }
        return $errors;
    }

    private function validateTemplateRequest(array $data): array
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = ['The name field is required.'];
        }
        if (empty($data['type'])) {
            $errors['type'] = ['The type field is required.'];
        }
        if (empty($data['html_template'])) {
            $errors['html_template'] = ['The html_template field is required.'];
        }
        return $errors;
    }

    private function validateSignatureRequest(array $data): array
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = ['The name field is required.'];
        }
        if (empty($data['signature_type'])) {
            $errors['signature_type'] = ['The signature_type field is required.'];
        }
        return $errors;
    }

    private function getCurrentUserId(): ?string
    {
        $token = $this->request->getHeaderLine('Authorization');
        if ($token && strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
            try {
                $payload = \App\Services\JWTService::decode($token);
                return $payload['sub'] ?? null;
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}
