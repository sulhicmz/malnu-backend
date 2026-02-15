<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\LearningAnalyticsService;
use Carbon\Carbon;

class LearningAnalyticsController extends Controller
{
    private LearningAnalyticsService $learningAnalyticsService;

    public function __construct(LearningAnalyticsService $learningAnalyticsService)
    {
        $this->learningAnalyticsService = $learningAnalyticsService;
    }

    /**
     * Get student performance overview.
     */
    public function studentPerformance(string $studentId)
    {
        try {
            $data = $this->learningAnalyticsService->getStudentPerformanceSummary($studentId);

            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve student performance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get class performance metrics.
     */
    public function classPerformance(string $classId)
    {
        try {
            $data = $this->learningAnalyticsService->getClassPerformanceMetrics($classId);

            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve class performance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get at-risk students.
     */
    public function atRiskStudents()
    {
        try {
            $warnings = $this->learningAnalyticsService->detectAtRiskStudents();

            return $this->json([
                'success' => true,
                'data' => [
                    'count' => count($warnings),
                    'warnings' => $warnings,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to detect at-risk students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get knowledge gaps for a student.
     */
    public function knowledgeGaps(string $studentId)
    {
        try {
            $subjectId = $this->request->input('subject_id');

            if (!$subjectId) {
                return $this->json([
                    'success' => false,
                    'message' => 'subject_id is required',
                ], 400);
            }

            $gaps = $this->learningAnalyticsService->identifyKnowledgeGaps($studentId, $subjectId);

            return $this->json([
                'success' => true,
                'data' => [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'gaps' => $gaps,
                    'critical_count' => $gaps->where('gap_status', 'critical')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to identify knowledge gaps: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record a learning activity.
     */
    public function recordActivity()
    {
        try {
            $validated = $this->request->validate([
                'student_id' => 'required|string',
                'activity_type' => 'required|string',
                'activity_name' => 'required|string',
                'subject_id' => 'nullable|string',
                'score' => 'nullable|numeric',
                'max_score' => 'nullable|numeric',
                'description' => 'nullable|string',
                'duration_minutes' => 'nullable|integer',
            ]);

            $activity = $this->learningAnalyticsService->recordLearningActivity(
                $validated['student_id'],
                $validated['activity_type'],
                $validated['activity_name'],
                $validated['subject_id'] ?? null,
                $validated['score'] ?? null,
                $validated['max_score'] ?? null,
                $validated['description'] ?? null,
                $validated['duration_minutes'] ?? null
            );

            return $this->json([
                'success' => true,
                'data' => $activity,
                'message' => 'Learning activity recorded successfully',
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to record activity: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate performance metrics for a student.
     */
    public function calculateMetrics(string $studentId)
    {
        try {
            $periodType = $this->request->input('period_type', 'monthly');
            $periodStart = $this->request->input('period_start');
            $periodEnd = $this->request->input('period_end');
            $subjectId = $this->request->input('subject_id');

            if (!$periodStart || !$periodEnd) {
                return $this->json([
                    'success' => false,
                    'message' => 'period_start and period_end are required',
                ], 400);
            }

            $this->learningAnalyticsService->calculateStudentPerformanceMetrics(
                $studentId,
                $periodType,
                Carbon::parse($periodStart),
                Carbon::parse($periodEnd),
                $subjectId
            );

            return $this->json([
                'success' => true,
                'message' => 'Performance metrics calculated successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to calculate metrics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Acknowledge an early warning.
     */
    public function acknowledgeWarning(string $warningId)
    {
        try {
            $warning = \App\Models\Analytics\EarlyWarning::findOrFail($warningId);
            $userId = $this->request->getAttribute('user_id');

            $warning->acknowledge($userId);

            return $this->json([
                'success' => true,
                'message' => 'Warning acknowledged successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to acknowledge warning: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve an early warning.
     */
    public function resolveWarning(string $warningId)
    {
        try {
            $warning = \App\Models\Analytics\EarlyWarning::findOrFail($warningId);
            $notes = $this->request->input('resolution_notes');

            $warning->resolve($notes);

            return $this->json([
                'success' => true,
                'message' => 'Warning resolved successfully',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to resolve warning: ' . $e->getMessage(),
            ], 500);
        }
    }
}
