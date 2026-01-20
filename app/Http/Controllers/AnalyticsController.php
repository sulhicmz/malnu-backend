<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function dashboard()
    {
        try {
            $data = $this->analyticsService->getDashboardOverview();
            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function studentPerformance(string $studentId)
    {
        try {
            $period = $this->request->input('period', 'monthly');
            $data = $this->analyticsService->getStudentPerformance($studentId, $period);
            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() === 404 ? 404 : 500);
        }
    }

    public function classMetrics(string $classId)
    {
        try {
            $period = $this->request->input('period', 'monthly');
            $data = $this->analyticsService->getClassMetrics($classId, $period);
            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() === 404 ? 404 : 500);
        }
    }

    public function generateReport()
    {
        try {
            $reportType = $this->request->input('report_type');
            $filters = $this->request->input('filters', []);

            if (!$reportType) {
                return $this->json([
                    'success' => false,
                    'message' => 'report_type is required',
                ], 400);
            }

            $data = $this->analyticsService->generateReport($reportType, $filters);
            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function recordMetric()
    {
        try {
            $data = [
                'user_id' => $this->request->input('user_id'),
                'data_type' => $this->request->input('data_type'),
                'metric_name' => $this->request->input('metric_name'),
                'metric_value' => $this->request->input('metric_value'),
                'metadata' => $this->request->input('metadata'),
                'period' => $this->request->input('period', 'daily'),
                'recorded_at' => $this->request->input('recorded_at'),
            ];

            $metric = $this->analyticsService->recordMetric($data);
            return $this->json([
                'success' => true,
                'data' => $metric,
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to record metric: ' . $e->getMessage(),
            ], 500);
        }
    }
}
