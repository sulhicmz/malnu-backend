<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Attendance\StudentAttendance;

interface AttendanceServiceInterface
{
    public function markAttendance(array $data): StudentAttendance;

    public function markBulkAttendance(string $classId, array $attendanceData, string $teacherId, string $markedBy): array;

    public function getStudentAttendance(string $studentId, ?string $startDate = null, ?string $endDate = null): object;

    public function getClassAttendance(string $classId, ?string $date = null): object;

    public function calculateAttendanceStatistics(string $studentId, ?string $startDate = null, ?string $endDate = null): array;

    public function calculateClassStatistics(string $classId, ?string $date = null): array;

    public function detectChronicAbsenteeism(): array;

    public function generateAttendanceReport(string $classId, string $startDate, string $endDate): array;

    public function validateTeacherAccess(string $teacherId, string $classId): bool;
}