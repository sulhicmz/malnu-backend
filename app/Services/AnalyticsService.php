<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Analytics\AnalyticsData;
use App\Models\Analytics\AnalyticsConfig;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\Grading\Grade;
use App\Models\Attendance\StudentAttendance;
use App\Models\User;
use Hyperf\DbConnection\Db;

class AnalyticsService
{
    public function getDashboardOverview(string $userId = null): array
    {
        $overview = [
            'students_count' => Student::count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'classes_count' => ClassModel::count(),
            'average_attendance' => $this->calculateAverageAttendance(),
            'average_gpa' => $this->calculateAverageGPA(),
            'total_assessments' => Grade::count(),
        ];

        return $overview;
    }

    public function getStudentPerformance(string $studentId, ?string $period = 'monthly'): array
    {
        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception('Student not found');
        }

        $grades = Grade::where('student_id', $studentId)->get();
        $gpa = $this->calculateStudentGPA($studentId);
        $attendance = $this->getStudentAttendance($studentId, $period);

        return [
            'student_id' => $studentId,
            'gpa' => $gpa,
            'total_grades' => $grades->count(),
            'average_score' => $grades->avg('score') ?? 0,
            'attendance' => $attendance,
            'grade_distribution' => $this->calculateGradeDistribution($grades),
            'performance_trend' => $this->calculatePerformanceTrend($studentId, $period),
        ];
    }

    public function getClassMetrics(string $classId, ?string $period = 'monthly'): array
    {
        $class = ClassModel::find($classId);
        if (!$class) {
            throw new \Exception('Class not found');
        }

        $students = Student::where('class_id', $classId)->get();
        $studentIds = $students->pluck('id')->toArray();

        $grades = Grade::whereIn('student_id', $studentIds)->get();
        $attendance = StudentAttendance::whereIn('student_id', $studentIds)->get();

        return [
            'class_id' => $classId,
            'student_count' => $students->count(),
            'average_gpa' => $this->calculateClassAverageGPA($studentIds),
            'average_attendance' => $this->calculateClassAverageAttendance($studentIds),
            'grade_distribution' => $this->calculateGradeDistribution($grades),
            'top_performers' => $this->getTopPerformers($studentIds, 5),
            'at_risk_students' => $this->getAtRiskStudents($studentIds),
        ];
    }

    public function recordMetric(array $data): AnalyticsData
    {
        return AnalyticsData::create([
            'user_id' => $data['user_id'] ?? null,
            'data_type' => $data['data_type'],
            'metric_name' => $data['metric_name'],
            'metric_value' => $data['metric_value'],
            'metadata' => $data['metadata'] ?? [],
            'period' => $data['period'] ?? 'daily',
            'recorded_at' => $data['recorded_at'] ?? \now(),
        ]);
    }

    public function generateReport(string $reportType, ?array $filters = []): array
    {
        $data = match($reportType) {
            'student_performance' => $this->generateStudentPerformanceReport($filters),
            'class_performance' => $this->generateClassPerformanceReport($filters),
            'attendance' => $this->generateAttendanceReport($filters),
            'grades' => $this->generateGradesReport($filters),
            default => throw new \Exception('Invalid report type'),
        };

        return $data;
    }

    private function calculateAverageAttendance(): float
    {
        $totalRecords = StudentAttendance::count();
        if ($totalRecords === 0) {
            return 0.0;
        }

        $presentRecords = StudentAttendance::where('status', 'present')->count();
        return round(($presentRecords / $totalRecords) * 100, 2);
    }

    private function calculateAverageGPA(): float
    {
        $grades = Grade::all();
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        return round($totalScore / $grades->count(), 2);
    }

    private function calculateStudentGPA(string $studentId): float
    {
        $grades = Grade::where('student_id', $studentId)->get();
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        return round($totalScore / $grades->count(), 2);
    }

    private function calculateClassAverageGPA(array $studentIds): float
    {
        $grades = Grade::whereIn('student_id', $studentIds)->get();
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        return round($totalScore / $grades->count(), 2);
    }

    private function calculateClassAverageAttendance(array $studentIds): float
    {
        $totalRecords = StudentAttendance::whereIn('student_id', $studentIds)->count();
        if ($totalRecords === 0) {
            return 0.0;
        }

        $presentRecords = StudentAttendance::whereIn('student_id', $studentIds)
            ->where('status', 'present')
            ->count();

        return round(($presentRecords / $totalRecords) * 100, 2);
    }

    private function getStudentAttendance(string $studentId, string $period): array
    {
        $startDate = match($period) {
            'daily' => \now()->startOfDay(),
            'weekly' => \now()->subWeek()->startOfWeek(),
            'monthly' => \now()->subMonth()->startOfMonth(),
            'yearly' => \now()->subYear()->startOfYear(),
            default => \now()->subMonth()->startOfMonth(),
        };

        $attendances = StudentAttendance::where('student_id', $studentId)
            ->where('attendance_date', '>=', $startDate)
            ->get();

        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();

        return [
            'total_records' => $attendances->count(),
            'present' => $present,
            'absent' => $absent,
            'attendance_rate' => $attendances->count() > 0 
                ? round(($present / $attendances->count()) * 100, 2) 
                : 0,
        ];
    }

    private function calculateGradeDistribution($grades): array
    {
        if ($grades->isEmpty()) {
            return ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        }

        return [
            'A' => $grades->where('score', '>=', 90)->count(),
            'B' => $grades->whereBetween('score', [80, 89])->count(),
            'C' => $grades->whereBetween('score', [70, 79])->count(),
            'D' => $grades->whereBetween('score', [60, 69])->count(),
            'F' => $grades->where('score', '<', 60)->count(),
        ];
    }

    private function calculatePerformanceTrend(string $studentId, string $period): array
    {
        $startDate = match($period) {
            'daily' => \now()->startOfDay(),
            'weekly' => \now()->subWeek()->startOfWeek(),
            'monthly' => \now()->subMonth()->startOfMonth(),
            default => \now()->subMonth()->startOfMonth(),
        };

        $grades = Grade::where('student_id', $studentId)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

        $trend = [];
        foreach ($grades as $grade) {
            $trend[] = [
                'date' => $grade->created_at->format('Y-m-d'),
                'score' => $grade->score,
            ];
        }

        return $trend;
    }

    private function getTopPerformers(array $studentIds, int $limit): array
    {
        $gpas = [];
        foreach ($studentIds as $studentId) {
            $gpas[$studentId] = $this->calculateStudentGPA($studentId);
        }

        arsort($gpas);
        $topStudentIds = array_slice(array_keys($gpas), 0, $limit, true);

        return Student::whereIn('id', $topStudentIds)->get()->map(function ($student) use ($gpas) {
            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'gpa' => $gpas[$student->id] ?? 0,
            ];
        })->toArray();
    }

    private function getAtRiskStudents(array $studentIds): array
    {
        $atRisk = [];

        foreach ($studentIds as $studentId) {
            $gpa = $this->calculateStudentGPA($studentId);
            $attendance = $this->getStudentAttendance($studentId, 'monthly');

            if ($gpa < 60 || $attendance['attendance_rate'] < 75) {
                $student = Student::find($studentId);
                $atRisk[] = [
                    'student_id' => $studentId,
                    'name' => $student->name ?? 'Unknown',
                    'gpa' => $gpa,
                    'attendance_rate' => $attendance['attendance_rate'],
                    'risk_reason' => $gpa < 60 ? 'Low GPA' : 'Low Attendance',
                ];
            }
        }

        return $atRisk;
    }

    private function generateStudentPerformanceReport(array $filters): array
    {
        $query = Student::query();
        
        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        $students = $query->get();
        $data = [];

        foreach ($students as $student) {
            $data[] = $this->getStudentPerformance($student->id);
        }

        return $data;
    }

    private function generateClassPerformanceReport(array $filters): array
    {
        $classes = ClassModel::all();
        $data = [];

        foreach ($classes as $class) {
            $data[] = $this->getClassMetrics($class->id);
        }

        return $data;
    }

    private function generateAttendanceReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? \now()->subMonth()->startOfMonth();
        $endDate = $filters['end_date'] ?? \now();

        $attendances = StudentAttendance::whereBetween('attendance_date', [$startDate, $endDate])->get();

        $report = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total_records' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
        ];

        if (isset($filters['student_id'])) {
            $report['student_attendance'] = $this->getStudentAttendance(
                $filters['student_id'], 
                'monthly'
            );
        }

        return $report;
    }

    private function generateGradesReport(array $filters): array
    {
        $query = Grade::query();

        if (isset($filters['class_id'])) {
            $students = Student::where('class_id', $filters['class_id'])->get();
            $query->whereIn('student_id', $students->pluck('id')->toArray());
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        $grades = $query->with('student')->get();

        return [
            'total_grades' => $grades->count(),
            'average_score' => $grades->avg('score') ?? 0,
            'grade_distribution' => $this->calculateGradeDistribution($grades),
            'grades' => $grades->toArray(),
        ];
    }
}
