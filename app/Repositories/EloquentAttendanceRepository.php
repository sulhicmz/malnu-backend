<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Carbon\Carbon;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    private StudentAttendance $model;
    private int $chronicAbsenceThreshold = 3;
    private int $attendanceCutoffHour = 14;

    public function __construct(StudentAttendance $model)
    {
        $this->model = $model;
    }

    public function markAttendance(array $data): StudentAttendance
    {
        return StudentAttendance::create($data);
    }

    public function markBulkAttendance(string $classId, array $attendanceData, string $teacherId, string $markedBy): array
    {
        $records = [];

        foreach ($attendanceData as $data) {
            $records[] = [
                'student_id' => $data['student_id'],
                'class_id' => $classId,
                'teacher_id' => $teacherId,
                'attendance_date' => $data['attendance_date'] ?? date('Y-m-d'),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'marked_by' => $markedBy,
            ];
        }

        StudentAttendance::insert($records);

        return $records;
    }

    public function getStudentAttendance(string $studentId, ?string $startDate = null, ?string $endDate = null): object
    {
        $query = StudentAttendance::byStudent($studentId)->withRelationships();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return (object) [
            'attendances' => $query->orderBy('attendance_date', 'desc')->get(),
            'statistics' => $this->calculateAttendanceStatistics($studentId, $startDate, $endDate),
        ];
    }

    public function getClassAttendance(string $classId, ?string $date = null): object
    {
        $query = StudentAttendance::byClass($classId)->withRelationships();

        if ($date) {
            $query->byDate($date);
        } else {
            $query->orderBy('attendance_date', 'desc');
        }

        return (object) [
            'attendances' => $query->get(),
            'statistics' => $this->calculateClassStatistics($classId, $date),
            'students' => Student::where('class_id', $classId)->get(),
        ];
    }

    public function calculateAttendanceStatistics(string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StudentAttendance::byStudent($studentId);

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        $result = $query->selectRaw('
            COUNT(*) as total_days,
            SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as excused_days
        ')->first();

        $totalDays = (int) $result->total_days;
        $presentDays = (int) $result->present_days;
        $absentDays = (int) $result->absent_days;
        $lateDays = (int) $result->late_days;
        $excusedDays = (int) $result->excused_days;

        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

        $isChronic = $absentDays >= $this->chronicAbsenceThreshold;

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'excused_days' => $excusedDays,
            'attendance_percentage' => $percentage,
            'is_chronic_absentee' => $isChronic,
        ];
    }

    public function calculateClassStatistics(string $classId, ?string $date = null): array
    {
        $query = StudentAttendance::byClass($classId);

        if ($date) {
            $query->byDate($date);
        }

        $totalStudents = Student::where('class_id', $classId)->count();
        $totalRecords = $query->count();
        $presentCount = $query->present()->count();
        $absentCount = $query->absent()->count();
        $lateCount = $query->late()->count();

        $classAverage = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0;

        return [
            'total_students' => $totalStudents,
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'class_average_attendance' => $classAverage,
        ];
    }

    public function detectChronicAbsenteeism(int $threshold = 3, int $days = 30): array
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));

        $chronicAbsentees = Student::whereHas('attendances', function ($query) use ($cutoffDate) {
            $query->whereDate('attendance_date', '>=', $cutoffDate);
        })
            ->with(['user:id,name,full_name'])
            ->withCount(['attendances as total_days' => function ($query) use ($cutoffDate) {
                $query->whereDate('attendance_date', '>=', $cutoffDate);
            }])
            ->withCount(['attendances as absent_days' => function ($query) use ($cutoffDate) {
                $query->whereDate('attendance_date', '>=', $cutoffDate)
                    ->where('status', 'absent');
            }])
            ->having('absent_days', '>=', $threshold)
            ->get()
            ->map(function ($student) use ($cutoffDate) {
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->user->full_name ?? $student->user->name,
                    'absent_days' => $student->absent_days,
                    'attendance_percentage' => $this->calculateAttendancePercentage($student->id, $cutoffDate),
                ];
            })
            ->toArray();

        return $chronicAbsentees;
    }

    public function generateAttendanceReport(string $classId, string $startDate, string $endDate): array
    {
        $attendances = StudentAttendance::byClass($classId)
            ->byDateRange($startDate, $endDate)
            ->with(['student', 'teacher'])
            ->get();

        $report = [
            'class_id' => $classId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'statistics' => $this->calculateClassStatistics($classId, $startDate),
            'daily_attendance' => [],
        ];

        $dailyData = [];

        foreach ($attendances as $attendance) {
            $dateKey = $attendance->attendance_date;

            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'date' => $attendance->attendance_date,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                    'total' => 0,
                ];
            }

            ++$dailyData[$dateKey]['total'];
            ++$dailyData[$dateKey][$attendance->status];
        }

        $report['daily_attendance'] = array_values($dailyData);

        return $report;
    }

    public function validateTeacherAccess(string $teacherId, string $classId): bool
    {
        $teacher = Teacher::where('user_id', $teacherId)
            ->where('class_id', $classId)
            ->first();

        return $teacher !== null;
    }

    private function calculateAttendancePercentage(string $studentId, string $sinceDate): float
    {
        $result = StudentAttendance::byStudent($studentId)
            ->whereDate('attendance_date', '>=', $sinceDate)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present
            ')->first();

        $total = (int) $result->total;
        $present = (int) $result->present;

        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }
}
