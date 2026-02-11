<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Teacher;

/**
 * Repository interface for Attendance data access operations.
 *
 * This interface provides an abstraction layer for attendance data operations,
 * enabling dependency injection, mocking in tests, and potential
 * data source switching (Eloquent, MongoDB, Redis, etc.).
 */
interface AttendanceRepositoryInterface
{
    /**
     * Mark attendance for a single student.
     *
     * @param array $data Attendance data (student_id, class_id, teacher_id, status, etc.)
     * @return StudentAttendance The created attendance record
     */
    public function markAttendance(array $data): StudentAttendance;

    /**
     * Mark attendance for multiple students at once (bulk operation).
     *
     * @param string $classId The class ID
     * @param array $attendanceData Array of attendance records
     * @param string $teacherId The teacher marking attendance
     * @param string $markedBy User ID of who marked attendance
     * @return array The created attendance records
     */
    public function markBulkAttendance(string $classId, array $attendanceData, string $teacherId, string $markedBy): array;

    /**
     * Get attendance records for a specific student.
     *
     * @param string $studentId The student ID
     * @param string|null $startDate Optional start date filter
     * @param string|null $endDate Optional end date filter
     * @return object Object containing attendances array and statistics
     */
    public function getStudentAttendance(string $studentId, ?string $startDate = null, ?string $endDate = null): object;

    /**
     * Get attendance records for a specific class.
     *
     * @param string $classId The class ID
     * @param string|null $date Optional date filter
     * @return object Object containing attendances array and statistics
     */
    public function getClassAttendance(string $classId, ?string $date = null): object;

    /**
     * Calculate attendance statistics for a student.
     *
     * @param string $studentId The student ID
     * @param string|null $startDate Optional start date filter
     * @param string|null $endDate Optional end date filter
     * @return array Statistics array (total_days, present_days, absent_days, etc.)
     */
    public function calculateAttendanceStatistics(string $studentId, ?string $startDate = null, ?string $endDate = null): array;

    /**
     * Calculate attendance statistics for a class.
     *
     * @param string $classId The class ID
     * @param string|null $date Optional date filter
     * @return array Statistics array (total_students, present_count, etc.)
     */
    public function calculateClassStatistics(string $classId, ?string $date = null): array;

    /**
     * Detect students with chronic absenteeism.
     *
     * @param int $threshold Minimum absent days to be considered chronic (default: 3)
     * @param int $days Number of days to look back (default: 30)
     * @return array Array of chronic absentee students with their data
     */
    public function detectChronicAbsenteeism(int $threshold = 3, int $days = 30): array;

    /**
     * Generate attendance report for a class.
     *
     * @param string $classId The class ID
     * @param string $startDate Report start date
     * @param string $endDate Report end date
     * @return array Complete attendance report with daily breakdown
     */
    public function generateAttendanceReport(string $classId, string $startDate, string $endDate): array;

    /**
     * Validate that a teacher has access to a class.
     *
     * @param string $teacherId The teacher user ID
     * @param string $classId The class ID
     * @return bool True if teacher has access, false otherwise
     */
    public function validateTeacherAccess(string $teacherId, string $classId): bool;
}
