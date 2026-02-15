<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Analytics\EarlyWarning;
use App\Models\Analytics\KnowledgeGap;
use App\Models\Analytics\LearningActivity;
use App\Models\Analytics\LearningPattern;
use App\Models\Analytics\StudentPerformanceMetric;
use App\Models\Analytics\TeachingEffectivenessMetric;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use Carbon\Carbon;
use Hypervel\Support\Collection;

class LearningAnalyticsService
{
    /**
     * Record a learning activity for a student.
     */
    public function recordLearningActivity(
        string $studentId,
        string $activityType,
        string $activityName,
        ?string $subjectId = null,
        ?float $score = null,
        ?float $maxScore = null,
        ?string $description = null,
        ?int $durationMinutes = null,
        ?array $metadata = null
    ): LearningActivity {
        return LearningActivity::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'activity_type' => $activityType,
            'activity_name' => $activityName,
            'description' => $description,
            'score' => $score,
            'max_score' => $maxScore,
            'activity_date' => now(),
            'duration_minutes' => $durationMinutes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Calculate and store student performance metrics for a period.
     */
    public function calculateStudentPerformanceMetrics(
        string $studentId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?string $subjectId = null
    ): void {
        $student = Student::findOrFail($studentId);

        // Calculate GPA
        $this->calculateAndStoreGpa($studentId, $subjectId, $periodType, $periodStart, $periodEnd);

        // Calculate Attendance Rate
        $this->calculateAndStoreAttendanceRate($studentId, $subjectId, $periodType, $periodStart, $periodEnd);

        // Calculate Engagement Score
        $this->calculateAndStoreEngagementScore($studentId, $subjectId, $periodType, $periodStart, $periodEnd);

        // Calculate Completion Rate
        $this->calculateAndStoreCompletionRate($studentId, $subjectId, $periodType, $periodStart, $periodEnd);
    }

    /**
     * Calculate GPA metric.
     */
    private function calculateAndStoreGpa(
        string $studentId,
        ?string $subjectId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        $gradesQuery = Grade::where('student_id', $studentId)
            ->whereBetween('created_at', [$periodStart, $periodEnd]);

        if ($subjectId) {
            $gradesQuery->where('subject_id', $subjectId);
        }

        $grades = $gradesQuery->get();

        if ($grades->isEmpty()) {
            return;
        }

        $totalScore = 0;
        $totalMaxScore = 0;

        foreach ($grades as $grade) {
            $totalScore += $grade->score;
            $totalMaxScore += $grade->max_score;
        }

        $gpa = $totalMaxScore > 0 ? ($totalScore / $totalMaxScore) * 4.0 : 0;

        $this->storePerformanceMetric($studentId, $subjectId, 'gpa', $gpa, $periodType, $periodStart, $periodEnd);
    }

    /**
     * Calculate attendance rate metric.
     */
    private function calculateAndStoreAttendanceRate(
        string $studentId,
        ?string $subjectId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        $activities = LearningActivity::where('student_id', $studentId)
            ->where('activity_type', 'attendance')
            ->whereBetween('activity_date', [$periodStart, $periodEnd])
            ->get();

        $presentCount = $activities->where('score', '>', 0)->count();
        $totalCount = $activities->count();

        $attendanceRate = $totalCount > 0 ? ($presentCount / $totalCount) * 100 : 0;

        $this->storePerformanceMetric($studentId, $subjectId, 'attendance_rate', $attendanceRate, $periodType, $periodStart, $periodEnd);
    }

    /**
     * Calculate engagement score.
     */
    private function calculateAndStoreEngagementScore(
        string $studentId,
        ?string $subjectId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        $activitiesQuery = LearningActivity::where('student_id', $studentId)
            ->whereBetween('activity_date', [$periodStart, $periodEnd]);

        if ($subjectId) {
            $activitiesQuery->where('subject_id', $subjectId);
        }

        $activities = $activitiesQuery->get();

        if ($activities->isEmpty()) {
            return;
        }

        $totalDuration = $activities->sum('duration_minutes');
        $activityCount = $activities->count();

        // Engagement score based on activity frequency and duration
        $avgDuration = $activityCount > 0 ? $totalDuration / $activityCount : 0;
        $engagementScore = min(100, ($activityCount * 5) + ($avgDuration * 0.5));

        $this->storePerformanceMetric($studentId, $subjectId, 'engagement_score', $engagementScore, $periodType, $periodStart, $periodEnd);
    }

    /**
     * Calculate completion rate.
     */
    private function calculateAndStoreCompletionRate(
        string $studentId,
        ?string $subjectId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        $activitiesQuery = LearningActivity::where('student_id', $studentId)
            ->whereIn('activity_type', ['assignment', 'quiz', 'exam'])
            ->whereBetween('activity_date', [$periodStart, $periodEnd]);

        if ($subjectId) {
            $activitiesQuery->where('subject_id', $subjectId);
        }

        $activities = $activitiesQuery->get();

        if ($activities->isEmpty()) {
            return;
        }

        $completedCount = $activities->whereNotNull('score')->count();
        $totalCount = $activities->count();

        $completionRate = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;

        $this->storePerformanceMetric($studentId, $subjectId, 'completion_rate', $completionRate, $periodType, $periodStart, $periodEnd);
    }

    /**
     * Store a performance metric.
     */
    private function storePerformanceMetric(
        string $studentId,
        ?string $subjectId,
        string $metricType,
        float $value,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        // Find previous value for trend calculation
        $previousMetric = StudentPerformanceMetric::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('metric_type', $metricType)
            ->where('period_type', $periodType)
            ->where('period_start', '<', $periodStart)
            ->orderBy('period_start', 'desc')
            ->first();

        $previousValue = $previousMetric?->value;
        $trendPercentage = null;

        if ($previousValue !== null && $previousValue != 0) {
            $trendPercentage = round((($value - $previousValue) / $previousValue) * 100, 2);
        }

        StudentPerformanceMetric::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'metric_type' => $metricType,
                'period_type' => $periodType,
                'period_start' => $periodStart,
            ],
            [
                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'value' => $value,
                'period_end' => $periodEnd,
                'previous_value' => $previousValue,
                'trend_percentage' => $trendPercentage,
            ]
        );
    }

    /**
     * Detect at-risk students based on configurable thresholds.
     */
    public function detectAtRiskStudents(): array
    {
        $warnings = [];
        $students = Student::all();

        foreach ($students as $student) {
            // Check performance decline
            $performanceWarning = $this->checkPerformanceDecline($student);
            if ($performanceWarning) {
                $warnings[] = $performanceWarning;
            }

            // Check low attendance
            $attendanceWarning = $this->checkLowAttendance($student);
            if ($attendanceWarning) {
                $warnings[] = $attendanceWarning;
            }

            // Check low engagement
            $engagementWarning = $this->checkLowEngagement($student);
            if ($engagementWarning) {
                $warnings[] = $engagementWarning;
            }
        }

        return $warnings;
    }

    /**
     * Check for performance decline.
     */
    private function checkPerformanceDecline(Student $student): ?EarlyWarning
    {
        $currentGpa = StudentPerformanceMetric::where('student_id', $student->id)
            ->where('metric_type', 'gpa')
            ->where('period_type', 'monthly')
            ->orderBy('period_start', 'desc')
            ->first();

        if ($currentGpa && $currentGpa->trend_percentage !== null && $currentGpa->trend_percentage < -15) {
            return $this->createEarlyWarning(
                $student->id,
                'performance_decline',
                $currentGpa->trend_percentage < -25 ? 'high' : 'medium',
                "Student's GPA has declined by {$currentGpa->trend_percentage}%",
                ['gpa_trend' => $currentGpa->trend_percentage, 'current_gpa' => $currentGpa->value]
            );
        }

        return null;
    }

    /**
     * Check for low attendance.
     */
    private function checkLowAttendance(Student $student): ?EarlyWarning
    {
        $attendanceRate = StudentPerformanceMetric::where('student_id', $student->id)
            ->where('metric_type', 'attendance_rate')
            ->where('period_type', 'monthly')
            ->orderBy('period_start', 'desc')
            ->first();

        if ($attendanceRate && $attendanceRate->value < 75) {
            return $this->createEarlyWarning(
                $student->id,
                'low_attendance',
                $attendanceRate->value < 60 ? 'high' : 'medium',
                "Student's attendance rate is {$attendanceRate->value}%",
                ['attendance_rate' => $attendanceRate->value]
            );
        }

        return null;
    }

    /**
     * Check for low engagement.
     */
    private function checkLowEngagement(Student $student): ?EarlyWarning
    {
        $engagementScore = StudentPerformanceMetric::where('student_id', $student->id)
            ->where('metric_type', 'engagement_score')
            ->where('period_type', 'monthly')
            ->orderBy('period_start', 'desc')
            ->first();

        if ($engagementScore && $engagementScore->value < 40) {
            return $this->createEarlyWarning(
                $student->id,
                'low_engagement',
                $engagementScore->value < 25 ? 'high' : 'medium',
                "Student's engagement score is {$engagementScore->value}",
                ['engagement_score' => $engagementScore->value]
            );
        }

        return null;
    }

    /**
     * Create an early warning.
     */
    private function createEarlyWarning(
        string $studentId,
        string $warningType,
        string $severity,
        string $description,
        array $triggerData
    ): EarlyWarning {
        // Check if there's already an active warning of this type
        $existing = EarlyWarning::where('student_id', $studentId)
            ->where('warning_type', $warningType)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return $existing;
        }

        return EarlyWarning::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'student_id' => $studentId,
            'warning_type' => $warningType,
            'severity' => $severity,
            'description' => $description,
            'trigger_data' => $triggerData,
            'status' => 'active',
            'triggered_at' => now(),
        ]);
    }

    /**
     * Identify knowledge gaps for a student in a subject.
     */
    public function identifyKnowledgeGaps(string $studentId, string $subjectId): Collection
    {
        $student = Student::findOrFail($studentId);
        $subject = Subject::findOrFail($subjectId);

        // Get all learning activities for this student and subject
        $activities = LearningActivity::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereNotNull('score')
            ->get();

        // Group by topic area (using activity name as proxy for topic)
        $topicScores = [];
        foreach ($activities as $activity) {
            $topic = $activity->activity_name;
            if (!isset($topicScores[$topic])) {
                $topicScores[$topic] = ['total_score' => 0, 'total_max' => 0, 'count' => 0];
            }
            $topicScores[$topic]['total_score'] += $activity->score;
            $topicScores[$topic]['total_max'] += $activity->max_score;
            $topicScores[$topic]['count']++;
        }

        $gaps = new Collection();

        foreach ($topicScores as $topic => $data) {
            $masteryLevel = $data['total_max'] > 0
                ? ($data['total_score'] / $data['total_max']) * 100
                : 0;

            $gapStatus = 'identified';
            if ($masteryLevel >= 70) {
                $gapStatus = 'resolved';
            } elseif ($masteryLevel < 40) {
                $gapStatus = 'critical';
            } elseif ($masteryLevel < 55) {
                $gapStatus = 'improving';
            }

            $gap = KnowledgeGap::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'topic_area' => $topic,
                ],
                [
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'mastery_level' => $masteryLevel,
                    'gap_status' => $gapStatus,
                    'assessment_count' => $data['count'],
                    'last_assessed_at' => now(),
                ]
            );

            $gaps->push($gap);
        }

        return $gaps;
    }

    /**
     * Calculate teaching effectiveness for a teacher.
     */
    public function calculateTeachingEffectiveness(
        string $teacherId,
        string $periodType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): TeachingEffectivenessMetric {
        $teacher = Teacher::findOrFail($teacherId);

        // Get all students in this teacher's classes
        $classes = ClassModel::where('teacher_id', $teacherId)->get();
        $totalStudents = 0;
        $studentsImproved = 0;
        $totalImprovement = 0;

        foreach ($classes as $class) {
            $students = Student::where('class_id', $class->id)->get();

            foreach ($students as $student) {
                $totalStudents++;

                $metrics = StudentPerformanceMetric::where('student_id', $student->id)
                    ->where('metric_type', 'gpa')
                    ->whereBetween('period_start', [$periodStart, $periodEnd])
                    ->get();

                if ($metrics->count() >= 2) {
                    $firstGpa = $metrics->first()->value;
                    $lastGpa = $metrics->last()->value;

                    if ($lastGpa > $firstGpa) {
                        $studentsImproved++;
                        $totalImprovement += ($lastGpa - $firstGpa);
                    }
                }
            }
        }

        $classAverageImprovement = $totalStudents > 0 ? $totalImprovement / $totalStudents : 0;

        return TeachingEffectivenessMetric::create([
            'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'teacher_id' => $teacherId,
            'class_average_improvement' => $classAverageImprovement,
            'student_engagement_score' => $this->calculateAverageEngagement($teacherId, $periodStart, $periodEnd),
            'total_students' => $totalStudents,
            'students_improved' => $studentsImproved,
            'period_type' => $periodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);
    }

    /**
     * Calculate average engagement for a teacher's students.
     */
    private function calculateAverageEngagement(string $teacherId, Carbon $periodStart, Carbon $periodEnd): float
    {
        $classes = ClassModel::where('teacher_id', $teacherId)->pluck('id');
        $students = Student::whereIn('class_id', $classes)->pluck('id');

        $avgEngagement = StudentPerformanceMetric::whereIn('student_id', $students)
            ->where('metric_type', 'engagement_score')
            ->whereBetween('period_start', [$periodStart, $periodEnd])
            ->avg('value');

        return $avgEngagement ?: 0;
    }

    /**
     * Get student performance summary.
     */
    public function getStudentPerformanceSummary(string $studentId): array
    {
        $student = Student::findOrFail($studentId);

        $latestMetrics = StudentPerformanceMetric::where('student_id', $studentId)
            ->where('period_type', 'monthly')
            ->orderBy('period_start', 'desc')
            ->get()
            ->unique('metric_type');

        $knowledgeGaps = KnowledgeGap::where('student_id', $studentId)
            ->where('gap_status', '!=', 'resolved')
            ->get();

        $activeWarnings = EarlyWarning::where('student_id', $studentId)
            ->where('status', 'active')
            ->get();

        return [
            'student_id' => $studentId,
            'student_name' => $student->user?->name ?? 'Unknown',
            'metrics' => $latestMetrics,
            'knowledge_gaps_count' => $knowledgeGaps->count(),
            'critical_gaps_count' => $knowledgeGaps->where('gap_status', 'critical')->count(),
            'active_warnings_count' => $activeWarnings->count(),
            'high_severity_warnings' => $activeWarnings->whereIn('severity', ['high', 'critical'])->count(),
        ];
    }

    /**
     * Get class performance metrics.
     */
    public function getClassPerformanceMetrics(string $classId): array
    {
        $class = ClassModel::findOrFail($classId);
        $students = Student::where('class_id', $classId)->get();

        $studentIds = $students->pluck('id');

        $avgGpa = StudentPerformanceMetric::whereIn('student_id', $studentIds)
            ->where('metric_type', 'gpa')
            ->avg('value');

        $avgAttendance = StudentPerformanceMetric::whereIn('student_id', $studentIds)
            ->where('metric_type', 'attendance_rate')
            ->avg('value');

        $avgEngagement = StudentPerformanceMetric::whereIn('student_id', $studentIds)
            ->where('metric_type', 'engagement_score')
            ->avg('value');

        $atRiskCount = EarlyWarning::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count();

        return [
            'class_id' => $classId,
            'class_name' => $class->name ?? 'Unknown',
            'total_students' => $students->count(),
            'average_gpa' => round($avgGpa, 2),
            'average_attendance_rate' => round($avgAttendance, 2),
            'average_engagement_score' => round($avgEngagement, 2),
            'at_risk_students_count' => $atRiskCount,
        ];
    }
}
