<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttendanceRepositoryInterface;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;

class AttendanceService implements \App\Contracts\AttendanceServiceInterface
{
    private int $chronicAbsenceThreshold;

    private int $attendanceCutoffHour;

    private AttendanceRepositoryInterface $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->chronicAbsenceThreshold = 3;
        $this->attendanceCutoffHour = 14;
    }

    public function markAttendance(array $data): StudentAttendance
    {
        return $this->attendanceRepository->markAttendance($data);
    }

    public function markBulkAttendance(string $classId, array $attendanceData, string $teacherId, string $markedBy): array
    {
        return $this->attendanceRepository->markBulkAttendance($classId, $attendanceData, $teacherId, $markedBy);
    }

    public function getStudentAttendance(string $studentId, ?string $startDate = null, ?string $endDate = null): object
    {
        return $this->attendanceRepository->getStudentAttendance($studentId, $startDate, $endDate);
    }

    public function getClassAttendance(string $classId, ?string $date = null): object
    {
        return $this->attendanceRepository->getClassAttendance($classId, $date);
    }

    public function calculateAttendanceStatistics(string $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->attendanceRepository->calculateAttendanceStatistics($studentId, $startDate, $endDate);
    }

    public function calculateClassStatistics(string $classId, ?string $date = null): array
    {
        return $this->attendanceRepository->calculateClassStatistics($classId, $date);
    }

    public function detectChronicAbsenteeism(): array
    {
        return $this->attendanceRepository->detectChronicAbsenteeism($this->chronicAbsenceThreshold, 30);
    }

    public function generateAttendanceReport(string $classId, string $startDate, string $endDate): array
    {
        return $this->attendanceRepository->generateAttendanceReport($classId, $startDate, $endDate);
    }

    public function validateTeacherAccess(string $teacherId, string $classId): bool
    {
        return $this->attendanceRepository->validateTeacherAccess($teacherId, $classId);
    }
}
