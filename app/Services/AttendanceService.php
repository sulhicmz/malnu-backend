<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\User;
use App\Models\SchoolManagement\Teacher;

class AttendanceService
{
    private int $chronicAbsenceThreshold;
    private int $attendanceCutoffHour;

    public function __construct()
    {
        $this->chronicAbsenceThreshold = (int) config('attendance.chronic_absence_threshold', 3);
        $this->attendanceCutoffHour = (int) config('attendance.cutoff_hour', 14);
    }

    public function markAttendance(array $data): StudentAttendance
    {
        return StudentAttendance::create([
            'student_id' => $data['student_id'],
            'class_id' => $data['class_id'],
            'teacher_id' => $data['teacher_id'],
            'attendance_date' => $data['attendance_date'] ?? date('Y-m-d'),
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'check_in_time' => $data['check_in_time'] ?? null,
            'check_out_time' => $data['check_out_time'] ?? null,
            'marked_by' => $data['marked_by'],
        ]);
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

    public function detectChronicAbsenteeism(): array
    {
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));

        $students = Student::with(['attendances' => function ($query) use ($cutoffDate) {
            $query->whereDate('attendance_date', '>=', $cutoffDate);
        }])->get();

        $chronicAbsentees = [];

        foreach ($students as $student) {
            $absentDays = $student->attendances->where('status', 'absent')->count();

            if ($absentDays >= $this->chronicAbsenceThreshold) {
                $chronicAbsentees[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->user->full_name ?? $student->user->name,
                    'absent_days' => $absentDays,
                    'attendance_percentage' => $this->calculateAttendancePercentage($student->id, $cutoffDate),
                ];
            }
        }

        return $chronicAbsentees;
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

            $dailyData[$dateKey]['total']++;
            $dailyData[$dateKey][$attendance->status]++;
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
}
