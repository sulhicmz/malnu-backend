<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\ReportGenerationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

class ReportController extends BaseController
{
    private ReportGenerationService $reportService;

    public function __construct(
        RequestInterface $request,
        ContainerInterface $container,
        ReportGenerationService $reportService
    ) {
        parent::__construct($request, $container);
        $this->reportService = $reportService;
    }

    public function generateReportCard()
    {
        $data = $this->request->all();

        $validated = $this->validateReportCardRequest($data);
        if ($validated !== true) {
            return $this->validationErrorResponse($validated);
        }

        try {
            $result = $this->reportService->generateReportCard(
                $data['student_id'],
                $data['class_id'],
                (int)$data['semester'],
                $data['academic_year'],
                $data['template_id'] ?? null
            );

            return $this->successResponse($result, 'Report card generated successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate report card', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to generate report card');
        }
    }

    public function generateTranscript()
    {
        $data = $this->request->all();

        $validated = $this->validateTranscriptRequest($data);
        if ($validated !== true) {
            return $this->validationErrorResponse($validated);
        }

        try {
            $result = $this->reportService->generateTranscript($data['student_id']);

            return $this->successResponse($result, 'Transcript generated successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate transcript', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to generate transcript');
        }
    }

    public function generateProgressReport()
    {
        $data = $this->request->all();

        $validated = $this->validateProgressReportRequest($data);
        if ($validated !== true) {
            return $this->validationErrorResponse($validated);
        }

        try {
            $result = $this->reportService->generateProgressReport(
                $data['student_id'],
                $data['class_id'],
                (int)$data['semester'],
                $data['academic_year']
            );

            return $this->successResponse($result, 'Progress report generated successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate progress report', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to generate progress report');
        }
    }

    public function generateBatchReportCards()
    {
        $data = $this->request->all();

        $validated = $this->validateBatchRequest($data);
        if ($validated !== true) {
            return $this->validationErrorResponse($validated);
        }

        try {
            $results = $this->reportService->generateBatchReportCards(
                $data['class_id'],
                (int)$data['semester'],
                $data['academic_year']
            );

            return $this->successResponse($results, 'Batch report cards generated successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate batch report cards', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to generate batch report cards');
        }
    }

    public function index()
    {
        $data = $this->request->all();

        try {
            $query = \App\Models\Grading\Report::with(['student', 'class', 'creator']);

            if (isset($data['student_id'])) {
                $query = $query->where('student_id', $data['student_id']);
            }

            if (isset($data['class_id'])) {
                $query = $query->where('class_id', $data['class_id']);
            }

            if (isset($data['semester'])) {
                $query = $query->where('semester', (int)$data['semester']);
            }

            if (isset($data['academic_year'])) {
                $query = $query->where('academic_year', $data['academic_year']);
            }

            if (isset($data['is_published'])) {
                $query = $query->where('is_published', (bool)$data['is_published']);
            }

            $reports = $query->orderByDesc('created_at')->get();

            return $this->successResponse($reports);

        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch reports', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to fetch reports');
        }
    }

    public function show($id)
    {
        try {
            $report = \App\Models\Grading\Report::with(['student', 'class', 'creator', 'signatures'])
                ->where('id', $id)
                ->first();

            if (!$report) {
                return $this->notFoundResponse('Report not found');
            }

            return $this->successResponse($report);

        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch report', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to fetch report');
        }
    }

    public function publishReport($id)
    {
        try {
            $report = \App\Models\Grading\Report::find($id);

            if (!$report) {
                return $this->notFoundResponse('Report not found');
            }

            $report->update([
                'is_published' => true,
                'published_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->successResponse($report, 'Report published successfully');

        } catch (\Exception $e) {
            $this->logger->error('Failed to publish report', [
                'error' => $e->getMessage(),
            ]);
            return $this->serverErrorResponse('Failed to publish report');
        }
    }

    private function validateReportCardRequest(array $data)
    {
        $errors = [];

        if (!isset($data['student_id']) || empty($data['student_id'])) {
            $errors['student_id'] = 'Student ID is required';
        }

        if (!isset($data['class_id']) || empty($data['class_id'])) {
            $errors['class_id'] = 'Class ID is required';
        }

        if (!isset($data['semester']) || empty($data['semester'])) {
            $errors['semester'] = 'Semester is required';
        }

        if (!isset($data['academic_year']) || empty($data['academic_year'])) {
            $errors['academic_year'] = 'Academic year is required';
        }

        if (!preg_match('/^\d{4}-\d{4}$/', $data['academic_year'] ?? '')) {
            $errors['academic_year'] = 'Academic year must be in YYYY-YYYY format';
        }

        return empty($errors) ? true : $errors;
    }

    private function validateTranscriptRequest(array $data)
    {
        $errors = [];

        if (!isset($data['student_id']) || empty($data['student_id'])) {
            $errors['student_id'] = 'Student ID is required';
        }

        return empty($errors) ? true : $errors;
    }

    private function validateProgressReportRequest(array $data)
    {
        return $this->validateReportCardRequest($data);
    }

    private function validateBatchRequest(array $data)
    {
        $errors = [];

        if (!isset($data['class_id']) || empty($data['class_id'])) {
            $errors['class_id'] = 'Class ID is required';
        }

        if (!isset($data['semester']) || empty($data['semester'])) {
            $errors['semester'] = 'Semester is required';
        }

        if (!isset($data['academic_year']) || empty($data['academic_year'])) {
            $errors['academic_year'] = 'Academic year is required';
        }

        return empty($errors) ? true : $errors;
    }
}
