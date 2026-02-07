<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AttendanceService;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\AttendanceService
 */
class AttendanceServiceTest extends TestCase
{
    private AttendanceService $attendanceService;

    private $mockStudentAttendanceModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceService = new AttendanceService();
        $this->mockStudentAttendanceModel = $this->createMock(StudentAttendance::class);
    }

    public function testMarkAttendanceCreatesRecord(): void
    {
        $data = [
            'student_id' => 'student123',
            'class_id' => 'class456',
            'teacher_id' => 'teacher789',
            'status' => 'present',
            'notes' => 'Attended class',
            'check_in_time' => '08:00',
            'check_out_time' => '14:00',
            'marked_by' => 'teacher789',
        ];

        $result = $this->attendanceService->markAttendance($data);

        $this->assertInstanceOf(StudentAttendance::class, $result);
    }

    public function testMarkAttendanceWithDefaultDate(): void
    {
        $data = [
            'student_id' => 'student123',
            'class_id' => 'class456',
            'teacher_id' => 'teacher789',
            'status' => 'present',
            'marked_by' => 'teacher789',
        ];

        $result = $this->attendanceService->markAttendance($data);

        $this->assertInstanceOf(StudentAttendance::class, $result);
    }

    public function testMarkAttendanceWithDefaultTimes(): void
    {
        $data = [
            'student_id' => 'student123',
            'class_id' => 'class456',
            'teacher_id' => 'teacher789',
            'status' => 'present',
            'marked_by' => 'teacher789',
        ];

        $result = $this->attendanceService->markAttendance($data);

        $this->assertInstanceOf(StudentAttendance::class, $result);
    }

    public function testMarkBulkAttendanceCreatesMultipleRecords(): void
    {
        $classId = 'class456';
        $teacherId = 'teacher789';
        $markedBy = 'teacher789';

        $attendanceData = [
            [
                'student_id' => 'student1',
                'status' => 'present',
            ],
            [
                'student_id' => 'student2',
                'status' => 'absent',
            ],
            [
                'student_id' => 'student3',
                'status' => 'late',
            ],
        ];

        $result = $this->attendanceService->markBulkAttendance($classId, $attendanceData, $teacherId, $markedBy);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testMarkBulkAttendanceWithDefaultDate(): void
    {
        $classId = 'class456';
        $teacherId = 'teacher789';
        $markedBy = 'teacher789';

        $attendanceData = [
            ['student_id' => 'student1', 'status' => 'present'],
            ['student_id' => 'student2', 'status' => 'absent'],
        ];

        $result = $this->attendanceService->markBulkAttendance($classId, $attendanceData, $teacherId, $markedBy);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testMarkBulkAttendanceWithCustomNotes(): void
    {
        $classId = 'class456';
        $teacherId = 'teacher789';
        $markedBy = 'teacher789';

        $attendanceData = [
            ['student_id' => 'student1', 'status' => 'present', 'notes' => 'Good attendance'],
            ['student_id' => 'student2', 'status' => 'absent', 'notes' => 'No notes'],
        ];

        $result = $this->attendanceService->markBulkAttendance($classId, $attendanceData, $teacherId, $markedBy);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetStudentAttendanceReturnsDataAndStatistics(): void
    {
        $studentId = 'student123';
        $startDate = '2026-01-01';
        $endDate = '2026-01-31';

        $result = $this->attendanceService->getStudentAttendance($studentId, $startDate, $endDate);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('attendances', $result);
        $this->assertObjectHasProperty('statistics', $result);
    }

    public function testGetStudentAttendanceWithoutDateRange(): void
    {
        $studentId = 'student123';

        $result = $this->attendanceService->getStudentAttendance($studentId);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('attendances', $result);
        $this->assertObjectHasProperty('statistics', $result);
    }

    public function testGetClassAttendanceReturnsDataAndStatistics(): void
    {
        $classId = 'class456';
        $date = '2026-02-07';

        $result = $this->attendanceService->getClassAttendance($classId, $date);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('attendances', $result);
        $this->assertObjectHasProperty('statistics', $result);
        $this->assertObjectHasProperty('students', $result);
    }

    public function testGetClassAttendanceWithoutDate(): void
    {
        $classId = 'class456';

        $result = $this->attendanceService->getClassAttendance($classId);

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('attendances', $result);
        $this->assertObjectHasProperty('statistics', $result);
        $this->assertObjectHasProperty('students', $result);
    }
}
