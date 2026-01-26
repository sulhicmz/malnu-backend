<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\DataImportService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use RuntimeException;

class ImportController extends BaseController
{
    private DataImportService $importService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        DataImportService $importService
    ) {
        parent::__construct($request, $response);
        $this->importService = $importService;
    }

    public function importStudents()
    {
        try {
            if (!$this->request->hasFile('csv_file')) {
                return $this->errorResponse('No file uploaded', 'MISSING_FILE');
            }

            $file = $this->request->file('csv_file');
            if ($file->getExtension() !== 'csv') {
                return $this->errorResponse('Only CSV files are allowed', 'INVALID_FILE_TYPE');
            }

            $tempPath = $file->moveTo(BASE_PATH . '/storage/app/imports');
            $results = $this->importService->importStudentsFromCsv($tempPath);

            return $this->successResponse($results, 'Import completed successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'IMPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Import students failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Import failed due to server error');
        }
    }

    public function importTeachers()
    {
        try {
            if (!$this->request->hasFile('csv_file')) {
                return $this->errorResponse('No file uploaded', 'MISSING_FILE');
            }

            $file = $this->request->file('csv_file');
            if ($file->getExtension() !== 'csv') {
                return $this->errorResponse('Only CSV files are allowed', 'INVALID_FILE_TYPE');
            }

            $tempPath = $file->moveTo(BASE_PATH . '/storage/app/imports');
            $results = $this->importService->importTeachersFromCsv($tempPath);

            return $this->successResponse($results, 'Import completed successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'IMPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Import teachers failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Import failed due to server error');
        }
    }

    public function importClasses()
    {
        try {
            if (!$this->request->hasFile('csv_file')) {
                return $this->errorResponse('No file uploaded', 'MISSING_FILE');
            }

            $file = $this->request->file('csv_file');
            if ($file->getExtension() !== 'csv') {
                return $this->errorResponse('Only CSV files are allowed', 'INVALID_FILE_TYPE');
            }

            $tempPath = $file->moveTo(BASE_PATH . '/storage/app/imports');
            $results = $this->importService->importClassesFromCsv($tempPath);

            return $this->successResponse($results, 'Import completed successfully');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'IMPORT_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Import classes failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Import failed due to server error');
        }
    }

    public function validateStudentImport()
    {
        try {
            if (!$this->request->hasFile('csv_file')) {
                return $this->errorResponse('No file uploaded', 'MISSING_FILE');
            }

            $file = $this->request->file('csv_file');
            if ($file->getExtension() !== 'csv') {
                return $this->errorResponse('Only CSV files are allowed', 'INVALID_FILE_TYPE');
            }

            $tempPath = $file->moveTo(BASE_PATH . '/storage/app/imports/temp');
            $results = $this->importService->importStudentsFromCsv($tempPath);

            if (!$results['success']) {
                return $this->errorResponse(
                    'Validation failed with ' . $results['failed'] . ' errors',
                    'VALIDATION_FAILED',
                    $results['errors']
                );
            }

            return $this->successResponse($results, 'Validation passed');

        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_FAILED');
        } catch (\Exception $e) {
            $this->logger->error('Validate student import failed', ['error' => $e->getMessage()]);
            return $this->serverErrorResponse('Validation failed due to server error');
        }
    }
}
