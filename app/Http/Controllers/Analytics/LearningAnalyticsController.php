<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Services\LearningAnalyticsService;
use App\Http\Controllers\Controller;

class LearningAnalyticsController extends Controller
{
    private LearningAnalyticsService $analyticsService;

    public function __construct(LearningAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function getStudentPerformance($studentId)
    {
        try {
            $metric = $this->analyticsService->calculateStudentPerformanceMetrics($studentId);

            if (!$metric) {
                return $this->notFoundResponse('Student performance not found');
            }

            return $this->successResponse($metric->toArray());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get student performance: ' . $e->getMessage());
        }
    }

    public function getClassPerformance($classId)
    {
        try {
            $metrics = StudentPerformanceMetric::where('class_id', $classId)->get();

            if ($metrics->isEmpty()) {
                return $this->notFoundResponse('Class performance not found');
            }

            $performance = [
                'average_gpa' => $metrics->avg('gpa'),
                'average_attendance_rate' => $metrics->avg('attendance_rate'),
                'average_engagement_score' => $metrics->avg('engagement_score'),
                'student_count' => count($metrics),
                'students' => $metrics->map(function ($m) {
                    return [
                        'student_id' => $m->student_id,
                        'gpa' => $m->gpa,
                        'attendance_rate' => $m->attendance_rate,
                        'engagement_score' => $m->engagement_score,
                        'overall_performance' => $m->getOverallPerformanceAttribute(),
                    ];
                }),
            ];

            return $this->successResponse($performance);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get class performance: ' . $e->getMessage());
        }
    }

    public function getLearningPatterns($studentId)
    {
        try {
            $patterns = $this->analyticsService->detectLearningPatterns($studentId);

            if (empty($patterns)) {
                return $this->successResponse(['message' => 'No learning patterns found yet']);
            }

            return $this->successResponse($patterns);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get learning patterns: ' . $e->getMessage());
        }
    }

    public function getEarlyWarnings($classId = null, $severity = null)
    {
        try {
            $warnings = $this->analyticsService->detectAtRiskStudents($classId);

            if ($severity) {
                $warnings = $warnings->where('severity', $severity);
            }

            return $this->successResponse($warnings);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get early warnings: ' . $e->getMessage());
        }
    }

    public function getKnowledgeGaps($classId, $subjectId = null)
    {
        try {
            $gaps = $this->analyticsService->identifyKnowledgeGaps($classId, $subjectId);

            return $this->successResponse($gaps);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get knowledge gaps: ' . $e->getMessage());
        }
    }

    public function getTeachingEffectiveness($teacherId, $classId = null)
    {
        try {
            $metrics = $this->analyticsService->getTeachingEffectivenessMetrics($teacherId, $classId);

            return $this->successResponse($metrics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get teaching effectiveness: ' . $e->getMessage());
        }
    }
}
