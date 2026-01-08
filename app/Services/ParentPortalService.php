<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ParentPortal\ParentStudentRelationship;
use App\Models\SchoolManagement\Student;
use App\Services\SISService;
use Hyperf\DbConnection\Db;

class ParentPortalService
{
    public function __construct(
        private readonly SISService $sisService
    ) {}

    public function getParentChildren(string $parentId): array
    {
        $relationships = ParentStudentRelationship::where('parent_id', $parentId)
            ->with('student')
            ->get();

        return $relationships->map(function ($relationship) {
            $student = $relationship->student;
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'grade_level' => $student->grade_level,
                'enrollment_status' => $student->enrollment_status ?? 'active',
                'relationship_type' => $relationship->relationship_type,
                'is_primary_contact' => $relationship->is_primary_contact,
            ];
        })->toArray();
    }

    public function getStudentDashboard(string $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        $student = Student::find($studentId);

        return [
            'student' => $this->getStudentInfo($student),
            'grades' => $this->sisService->getStudentGrades($studentId),
            'gpa' => $this->sisService->calculateGPA($studentId),
            'attendance' => $this->getAttendanceSummary($studentId),
            'assignments' => $this->getUpcomingAssignments($studentId),
            'behavior' => $this->getBehaviorRecords($studentId),
        ];
    }

    public function getStudentProgress(string $parentId, string $studentId, ?string $term = null): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        $student = Student::find($studentId);
        $grades = $this->sisService->getStudentGrades($studentId);
        $gpa = $this->sisService->calculateGPA($studentId, $term);

        return [
            'student_id' => $studentId,
            'student_name' => $student->name,
            'term' => $term,
            'gpa' => $gpa,
            'grades_by_subject' => $this->groupGradesBySubject($grades),
            'performance_trend' => $this->calculatePerformanceTrend($grades),
            'areas_of_strength' => $this->identifyStrengths($grades),
            'areas_needing_attention' => $this->identifyWeaknesses($grades),
        ];
    }

    public function getStudentTranscript(string $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        return $this->sisService->generateTranscript($studentId);
    }

    public function getStudentAttendance(string $parentId, string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        return $this->getAttendanceRecords($studentId, $startDate, $endDate);
    }

    public function getStudentAssignments(string $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        return [
            'upcoming' => $this->getUpcomingAssignments($studentId),
            'overdue' => $this->getOverdueAssignments($studentId),
            'completed' => $this->getCompletedAssignments($studentId),
        ];
    }

    public function getStudentBehaviorRecords(string $parentId, string $studentId): array
    {
        $this->verifyParentAccess($parentId, $studentId);

        return $this->getBehaviorRecords($studentId);
    }

    private function verifyParentAccess(string $parentId, string $studentId): void
    {
        $hasAccess = ParentStudentRelationship::where('parent_id', $parentId)
            ->where('student_id', $studentId)
            ->exists();

        if (!$hasAccess) {
            throw new \RuntimeException('Parent does not have access to this student');
        }
    }

    private function getStudentInfo(?Student $student): array
    {
        if (!$student) {
            return [];
        }

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'grade_level' => $student->grade_level,
            'class' => $student->class_id,
        ];
    }

    private function getAttendanceSummary(string $studentId): array
    {
        $records = $this->getAttendanceRecords($studentId);

        $present = collect($records)->where('status', 'present')->count();
        $absent = collect($records)->where('status', 'absent')->count();
        $late = collect($records)->where('status', 'late')->count();
        $total = $records ? count($records) : 1;

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'total_days' => $total,
        ];
    }

    private function getAttendanceRecords(string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Db::table('attendance_records')
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->get()->toArray();
    }

    private function getUpcomingAssignments(string $studentId): array
    {
        return Db::table('assignments')
            ->where('student_id', $studentId)
            ->where('due_date', '>', now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getOverdueAssignments(string $studentId): array
    {
        return Db::table('assignments')
            ->where('student_id', $studentId)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getCompletedAssignments(string $studentId): array
    {
        return Db::table('assignments')
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    private function getBehaviorRecords(string $studentId): array
    {
        return Db::table('behavior_records')
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function groupGradesBySubject(array $grades): array
    {
        $grouped = [];
        foreach ($grades as $grade) {
            $subject = $grade['subject'] ?? 'Unknown';
            if (!isset($grouped[$subject])) {
                $grouped[$subject] = [];
            }
            $grouped[$subject][] = $grade;
        }
        return $grouped;
    }

    private function calculatePerformanceTrend(array $grades): array
    {
        if (empty($grades)) {
            return ['trend' => 'stable', 'change' => 0];
        }

        $recentScores = array_slice(array_column($grades, 'score'), 0, 5);
        $earlierScores = array_slice(array_column($grades, 'score'), 5);

        if (empty($earlierScores)) {
            return ['trend' => 'stable', 'change' => 0];
        }

        $recentAvg = array_sum($recentScores) / count($recentScores);
        $earlierAvg = array_sum($earlierScores) / count($earlierScores);
        $change = $recentAvg - $earlierAvg;

        return [
            'trend' => $change > 5 ? 'improving' : ($change < -5 ? 'declining' : 'stable'),
            'change' => round($change, 2),
            'recent_average' => round($recentAvg, 2),
            'earlier_average' => round($earlierAvg, 2),
        ];
    }

    private function identifyStrengths(array $grades): array
    {
        if (empty($grades)) {
            return [];
        }

        $grouped = $this->groupGradesBySubject($grades);
        $strengths = [];

        foreach ($grouped as $subject => $subjectGrades) {
            $avg = array_sum(array_column($subjectGrades, 'score')) / count($subjectGrades);
            if ($avg >= 85) {
                $strengths[] = [
                    'subject' => $subject,
                    'average' => round($avg, 2),
                    'grade_count' => count($subjectGrades),
                ];
            }
        }

        return $strengths;
    }

    private function identifyWeaknesses(array $grades): array
    {
        if (empty($grades)) {
            return [];
        }

        $grouped = $this->groupGradesBySubject($grades);
        $weaknesses = [];

        foreach ($grouped as $subject => $subjectGrades) {
            $avg = array_sum(array_column($subjectGrades, 'score')) / count($subjectGrades);
            if ($avg < 70 && count($subjectGrades) >= 3) {
                $weaknesses[] = [
                    'subject' => $subject,
                    'average' => round($avg, 2),
                    'grade_count' => count($subjectGrades),
                ];
            }
        }

        return $weaknesses;
    }
}
