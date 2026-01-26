<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\DataExportService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use RuntimeException;

class ExportController extends BaseController
{
    private DataExportService $exportService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        DataExportService $exportService
    ) {
        parent::__construct($request, $response);
        $this->exportService = $exportService;
    }

    public function exportStudents()
    {
        try {
            $filters = [
                'class_id' => $this->request->input('class_id'),
                'status' => $this->request->input('status'),
                'date_from' => $this->request->input('date_from'),
                'date_to' => $this->request->input('date_to')
            ];

            $filename = 'students_export_' . date('Y-m-d_His') . '.csv';
            $outputPath = BASE_PATH . '/storage/exports/' . $filename;

            $results = $this->exportService->exportStudentsToCsv($outputPath, $filters);

            if (!$results['success']) {
                return $this->errorResponse(
                    'Export failed: ' . ($results['errors'][0]['message'] ?? 'Unknown error'),
                    'EXPORT_FAILED',
                    $results['errors']
                );
            }

            return $this->successResponse([
                'filename' => $filename,
                'exported' => $results['exported'],
                'download_url' => '/api/export/download/' . $filename,
                'file_size' => filesize($outputPath)
            ], 'Students exported successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'EXPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Export students failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Export failed due to server error');
        }
    }

    public function exportTeachers()
    {
        try {
            $filters = [
                'department' => $this->request->input('department'),
                'subject' => $this->request->input('subject')
            ];

            $filename = 'teachers_export_' . date('Y-m-d_His') . '.csv';
            $outputPath = BASE_PATH . '/storage/exports/' . $filename;

            $results = $this->exportService->exportTeachersToCsv($outputPath, $filters);

            if (!$results['success']) {
                return $this->errorResponse(
                    'Export failed: ' . ($results['errors'][0]['message'] ?? 'Unknown error'),
                    'EXPORT_FAILED',
                    $results['errors']
                );
            }

            return $this->successResponse([
                'filename' => $filename,
                'exported' => $results['exported'],
                'download_url' => '/api/export/download/' . $filename,
                'file_size' => filesize($outputPath)
            ], 'Teachers exported successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'EXPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Export teachers failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Export failed due to server error');
        }
    }

    public function exportClasses()
    {
        try {
            $filters = [
                'grade_level' => $this->request->input('grade_level'),
                'academic_year' => $this->request->input('academic_year'),
                'status' => $this->request->input('status')
            ];

            $filename = 'classes_export_' . date('Y-m-d_His') . '.csv';
            $outputPath = BASE_PATH . '/storage/exports/' . $filename;

            $results = $this->exportService->exportClassesToCsv($outputPath, $filters);

            if (!$results['success']) {
                return $this->errorResponse(
                    'Export failed: ' . ($results['errors'][0]['message'] ?? 'Unknown error'),
                    'EXPORT_FAILED',
                    $results['errors']
                );
            }

            return $this->successResponse([
                'filename' => $filename,
                'exported' => $results['exported'],
                'download_url' => '/api/export/download/' . $filename,
                'file_size' => filesize($outputPath)
            ], 'Classes exported successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'EXPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Export classes failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Export failed due to server error');
        }
    }

    public function downloadFile()
    {
        try {
            $filename = $this->request->route('filename');

            $filepath = BASE_PATH . '/storage/exports/' . $filename;

            if (!file_exists($filepath)) {
                return $this->notFoundResponse('File not found');
            }

            return $this->response->withFile($filepath, $filename);

        } catch (\Exception $e) {
            $this->logger->error('Download export file failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Download failed due to server error');
        }
    }
}
