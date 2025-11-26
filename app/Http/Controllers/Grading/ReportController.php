<?php

declare(strict_types=1);

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate report card for a student
     */
    public function generateReportCard(
        RequestInterface $request,
        ResponseInterface $response,
        string $studentId
    ): PsrResponseInterface {
        try {
            $classId = $request->input('class_id');
            $semester = (int) $request->input('semester');
            $academicYear = $request->input('academic_year');

            if (!$classId || !$semester || !$academicYear) {
                return $response->json([
                    'success' => false,
                    'message' => 'Missing required parameters: class_id, semester, academic_year'
                ])->withStatus(400);
            }

            $reportData = $this->reportService->generateReportCard($studentId, $classId, $semester, $academicYear);

            return $response->json([
                'success' => true,
                'data' => $reportData
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Generate academic transcript for a student
     */
    public function generateTranscript(
        RequestInterface $request,
        ResponseInterface $response,
        string $studentId
    ): PsrResponseInterface {
        try {
            $transcriptData = $this->reportService->generateTranscript($studentId);

            return $response->json([
                'success' => true,
                'data' => $transcriptData
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all reports for a student
     */
    public function getStudentReports(
        RequestInterface $request,
        ResponseInterface $response,
        string $studentId
    ): PsrResponseInterface {
        try {
            // This would require the Report model to be functional
            // For now, return a placeholder response
            return $response->json([
                'success' => true,
                'data' => [],
                'message' => 'Reports functionality not fully implemented due to framework dependencies'
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all reports for a class
     */
    public function getClassReports(
        RequestInterface $request,
        ResponseInterface $response,
        string $classId
    ): PsrResponseInterface {
        try {
            $semester = (int) $request->input('semester');
            $academicYear = $request->input('academic_year');

            if (!$semester || !$academicYear) {
                return $response->json([
                    'success' => false,
                    'message' => 'Missing required parameters: semester, academic_year'
                ])->withStatus(400);
            }

            // This would require the Report model to be functional
            // For now, return a placeholder response
            return $response->json([
                'success' => true,
                'data' => [],
                'message' => 'Reports functionality not fully implemented due to framework dependencies'
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ])->withStatus(500);
        }
    }
}