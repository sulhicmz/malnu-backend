<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceServiceInterface;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;

class AttendanceService implements AttendanceServiceInterface
{
    private int $chronicAbsenceThreshold;

    private int $attendanceCutoffHour;

    private CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->chronicAbsenceThreshold = (int) config('attendance.chronic_absence_threshold', 3);
        $this->attendanceCutoffHour = (int) config('attendance.cutoff_hour', 14);
        $this->cache = $cache;
    }

    public function markAttendance(array $data): StudentAttendance
    {
        $attendance = StudentAttendance::create([
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

        $this->invalidateAttendanceCache($data['student_id'], $data['class_id']);

        return $attendance;
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

        $this->invalidateAttendanceCache(null, $classId);

        return $records;
    }

    public function getStudentAttendance(string $studentId, ?string $startDate = null, ?string $endDate = null): object
    {
        $cacheKey = $this->cache->generateKey('attendance:student', [
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('medium'), function () use ($studentId, $startDate, $endDate) {
            $query = StudentAttendance::byStudent($studentId)->withRelationships();

            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }

            return (object) [
                'attendances' => $query->orderBy('attendance_date', 'desc')->get(),
                'statistics' => $this->calculateAttendanceStatistics($studentId, $startDate, $endDate),
            ];
        });
    }

    public function getClassAttendance(string $classId, ?string $date = null): object
    {
        $cacheKey = $this->cache->generateKey('attendance:class', [
            'class_id' => $classId,
            'date' => $date,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('medium'), function () use ($classId, $date) {
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
        });
    }

    public function calculateAttendanceStatistics(string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $cacheKey = $this->cache->generateKey('attendance:statistics:student', [
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('medium'), function () use ($studentId, $startDate, $endDate) {
            $query = StudentAttendance::byStudent($studentId);

            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }

            $totalDays = $query->count();
            $presentDays = $query->present()->count();
            $absentDays = $query->absent()->count();
            $lateDays = $query->late()->count();
            $excusedDays = $query->excused()->count();

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
        });
    }

    public function calculateClassStatistics(string $classId, ?string $date = null): array
    {
        $cacheKey = $this->cache->generateKey('attendance:statistics:class', [
            'class_id' => $classId,
            'date' => $date,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('medium'), function () use ($classId, $date) {
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
        });
    }

    public function detectChronicAbsenteeism(): array
    {
        $cacheKey = $this->cache->generateKey('attendance:chronic_absentees', [
            'threshold' => $this->chronicAbsenceThreshold,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('short'), function () {
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
        });
    }

    public function generateAttendanceReport(string $classId, string $startDate, string $endDate): array
    {
        $cacheKey = $this->cache->generateKey('attendance:report', [
            'class_id' => $classId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $this->cache->remember($cacheKey, $this->cache->getTTL('long'), function () use ($classId, $startDate, $endDate) {
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

                if (! isset($dailyData[$dateKey])) {
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
        });
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
        $query = StudentAttendance::byStudent($studentId)
            ->whereDate('attendance_date', '>=', $sinceDate);

        $total = $query->count();
        $present = $query->present()->count();

        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }

    private function invalidateAttendanceCache(?string $studentId, ?string $classId): void
    {
        $prefixes = ['attendance:'];

        if ($studentId) {
            $this->cache->forgetByPrefix('attendance:student');
            $this->cache->forgetByPrefix('attendance:statistics:student');
        }

        if ($classId) {
            $this->cache->forgetByPrefix('attendance:class');
            $this->cache->forgetByPrefix('attendance:statistics:class');
        }

        $this->cache->forgetByPrefix('attendance:chronic_absentees');
        $this->cache->forgetByPrefix('attendance:report');
    }
}
