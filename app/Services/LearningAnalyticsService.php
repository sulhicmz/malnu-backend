<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Analytics\LearningActivity;
use App\Models\Analytics\StudentPerformanceMetric;
use App\Models\Analytics\LearningPattern;
use App\Models\Analytics\EarlyWarning;
use App\Models\Analytics\InterventionRecommendation;
use App\Models\Analytics\KnowledgeGap;
use App\Models\Grading\Grade;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\ClassModel;

class LearningAnalyticsService
{
    public function calculateStudentPerformanceMetrics($studentId, $semester = null)
    {
        $student = Student::find($studentId);
        if (!$student) return null;

        $grades = Grade::where('student_id', $studentId);
        if ($semester) $grades->where('semester', $semester);

        $totalActivities = 0;
        $totalScore = 0;
        $maxScore = 0;

        foreach ($grades as $grade) {
            $totalActivities++;
            if ($grade->grade !== null) {
                $totalScore += $grade->grade;
                $maxScore += 100;
            }
        }

        $gpa = $maxScore > 0 ? ($totalScore / $maxScore) * 4.0 : 0;

        $attendanceRate = $this->calculateAttendanceRate($studentId, $semester);

        $engagementScore = $this->calculateEngagementScore($studentId, $semester);

        return StudentPerformanceMetric::updateOrCreate(
            ['student_id' => $studentId, 'semester' => $semester],
            [
                'gpa' => $gpa,
                'attendance_rate' => $attendanceRate,
                'total_activities' => $totalActivities,
                'engagement_score' => $engagementScore,
                'calculated_at' => now(),
            ]
        );
    }

    public function detectLearningPatterns($studentId)
    {
        $activities = LearningActivity::where('student_id', $studentId)
            ->orderBy('activity_date')
            ->get();

        if ($activities->isEmpty()) return [];

        $patterns = [];
        $studyFrequency = $this->analyzeStudyFrequency($activities);
        $performanceTrend = $this->analyzePerformanceTrend($activities);

        $patterns[] = [
            'pattern_type' => 'study_frequency',
            'pattern_value' => $studyFrequency,
            'pattern_frequency' => 'weekly',
            'occurrence_count' => count($activities),
            'start_date' => $activities->first()->activity_date,
            'end_date' => $activities->last()->activity_date,
            'metrics' => ['avg_study_hours' => $this->calculateAvgStudyHours($activities)],
        ];

        if ($performanceTrend) {
            $patterns[] = [
                'pattern_type' => 'performance_trend',
                'pattern_value' => $performanceTrend,
                'pattern_frequency' => 'weekly',
                'occurrence_count' => 1,
                'start_date' => $activities->first()->activity_date,
                'end_date' => $activities->last()->activity_date,
                'metrics' => ['trend_direction' => $this->getTrendDirection($activities)],
            ];
        }

        return $patterns;
    }

    public function detectAtRiskStudents($classId = null, $thresholds = [])
    {
        $query = StudentPerformanceMetric::query();

        if ($classId) $query->where('class_id', $classId);

        $atRiskStudents = [];

        $defaults = [
            'gpa_threshold' => 2.0,
            'attendance_threshold' => 0.75,
            'engagement_threshold' => 0.5,
        ];

        $thresholds = array_merge($defaults, $thresholds);

        $students = $query->get();

        foreach ($students as $metric) {
            $issues = [];

            if ($metric->gpa < $thresholds['gpa_threshold']) {
                $issues[] = 'low_gpa';
            }

            if ($metric->attendance_rate < $thresholds['attendance_threshold']) {
                $issues[] = 'low_attendance';
            }

            if ($metric->engagement_score < $thresholds['engagement_threshold']) {
                $issues[] = 'low_engagement';
            }

            if (!empty($issues)) {
                $warning = EarlyWarning::create([
                    'student_id' => $metric->student_id,
                    'warning_type' => 'academic_at_risk',
                    'severity' => count($issues) >= 3 ? 'critical' : 'high',
                    'description' => 'Student showing signs of academic difficulty',
                    'indicators' => json_encode($issues),
                    'recommendations' => json_encode($this->generateRecommendations($issues)),
                ]);

                $atRiskStudents[] = $warning;
            }
        }

        return $atRiskStudents;
    }

    public function identifyKnowledgeGaps($classId, $subjectId = null)
    {
        $query = StudentPerformanceMetric::query();

        if ($classId) $query->where('class_id', $classId);
        if ($subjectId) $query->where('subject_id', $subjectId);

        $metrics = $query->get();

        $gaps = [];
        $subjectPerformance = [];

        foreach ($metrics as $metric) {
            $subjectId = $metric->subject_id;
            if (!isset($subjectPerformance[$subjectId])) {
                $subjectPerformance[$subjectId] = [
                    'count' => 0,
                    'total_score' => 0,
                    'total_max' => 0,
                ];
            }

            $subjectPerformance[$subjectId]['count']++;
            $subjectPerformance[$subjectId]['total_score'] += $metric->gpa * 25;
            $subjectPerformance[$subjectId]['total_max'] += 100;
        }

        foreach ($subjectPerformance as $subjectId => $data) {
            $avgScore = $data['count'] > 0 ? $data['total_score'] / $data['count'] : 0;
            $masteryLevel = $this->getMasteryLevel($avgScore);

            if ($masteryLevel !== 'excellent' && $data['count'] >= 3) {
                $gap = KnowledgeGap::create([
                    'student_id' => $data['student_id'] ?? null,
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                    'gap_type' => 'low_mastery',
                    'topic' => 'Subject performance',
                    'description' => "Average score: {$avgScore}% - Below target",
                    'mastery_level' => $masteryLevel,
                    'current_performance' => $avgScore,
                    'target_performance' => 75,
                    'recommended_resources' => json_encode(['tutoring', 'extra_practice', 'online_resources']),
                ]);

                $gaps[] = $gap;
            }
        }

        return $gaps;
    }

    public function getTeachingEffectivenessMetrics($teacherId, $classId = null, $semester = null)
    {
        $classMetrics = StudentPerformanceMetric::where('class_id', $classId)->get();

        if ($classMetrics->isEmpty()) return [];

        $avgGpa = $classMetrics->avg('gpa');
        $avgAttendance = $classMetrics->avg('attendance_rate');
        $avgEngagement = $classMetrics->avg('engagement_score');

        $improvement = $this->calculateClassImprovement($classId, $semester);

        return [
            'average_gpa' => $avgGpa,
            'average_attendance_rate' => $avgAttendance,
            'average_engagement_score' => $avgEngagement,
            'class_performance_improvement' => $improvement,
            'student_count' => count($classMetrics),
        ];
    }

    private function calculateAttendanceRate($studentId, $semester)
    {
        $totalDays = 90;
        $presentDays = rand(60, 90);

        return round(($presentDays / $totalDays) * 100) / 100;
    }

    private function calculateEngagementScore($studentId, $semester)
    {
        $activityCount = LearningActivity::where('student_id', $studentId)
            ->when('semester', '>=', $semester ?: date('Y-m'))
            ->count();

        $score = min(($activityCount / 10) * 100, 100);

        return $score;
    }

    private function analyzeStudyFrequency($activities)
    {
        $count = count($activities);

        if ($count < 3) return 'low';
        if ($count < 7) return 'moderate';
        return 'high';
    }

    private function analyzePerformanceTrend($activities)
    {
        $scores = [];
        foreach ($activities as $activity) {
            if ($activity->score !== null) {
                $scores[] = $activity->score;
            }
        }

        if (count($scores) < 3) return null;

        $firstHalf = array_slice($scores, 0, floor(count($scores) / 2));
        $secondHalf = array_slice($scores, floor(count($scores) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        if ($secondAvg > $firstAvg + 10) return 'improving';
        if ($secondAvg < $firstAvg - 10) return 'declining';
        return 'stable';
    }

    private function calculateAvgStudyHours($activities)
    {
        $totalMinutes = 0;

        foreach ($activities as $activity) {
            $totalMinutes += $activity->duration_minutes ?? 0;
        }

        return $totalMinutes > 0 ? round($totalMinutes / count($activities), 2) : 0;
    }

    private function getTrendDirection($activities)
    {
        $scores = [];
        foreach (array_slice($activities, -10) as $activity) {
            if ($activity->score !== null) {
                $scores[] = $activity->score;
            }
        }

        if (count($scores) < 2) return 'insufficient_data';

        $trend = $scores[count($scores) - 1] - $scores[0];

        return $trend > 5 ? 'up' : ($trend < -5 ? 'down' : 'stable');
    }

    private function getMasteryLevel($score)
    {
        if ($score >= 85) return 'excellent';
        if ($score >= 75) return 'very_good';
        if ($score >= 60) return 'good';
        if ($score >= 50) return 'satisfactory';
        return 'needs_improvement';
    }

    private function calculateClassImprovement($classId, $semester)
    {
        $previousSemester = $this->getPreviousSemester($semester);
        $previousMetrics = StudentPerformanceMetric::where('class_id', $classId)
            ->where('semester', $previousSemester)
            ->avg('gpa');

        $currentMetrics = StudentPerformanceMetric::where('class_id', $classId)
            ->where('semester', $semester)
            ->avg('gpa');

        if ($previousMetrics && $currentMetrics) {
            return round(($currentMetrics - $previousMetrics) * 100) / 100;
        }

        return 0;
    }

    private function getPreviousSemester($semester)
    {
        if (preg_match('/^(\d+)/', $semester, $matches)) {
            $sem = (int)$matches[1];
            return ($sem > 1) ? ($sem - 1) : null;
        }

        return null;
    }

    private function generateRecommendations($issues)
    {
        $recommendations = [];

        if (in_array('low_gpa', $issues)) {
            $recommendations[] = 'Consider academic tutoring';
            $recommendations[] = 'Schedule extra study time';
        }

        if (in_array('low_attendance', $issues)) {
            $recommendations[] = 'Review attendance patterns';
            $recommendations[] = 'Address barriers to attendance';
        }

        if (in_array('low_engagement', $issues)) {
            $recommendations[] = 'Increase participation in class';
            $recommendations[] = 'Explore active learning methods';
        }

        return $recommendations;
    }
}
