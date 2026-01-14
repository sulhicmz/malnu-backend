<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Services\AttendanceService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AttendanceTest extends TestCase
{
    private AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = $this->app->get(AttendanceService::class);
    }

    public function testMarkStudentAttendance(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (! $student || ! $class) {
            $this->markTestSkipped('No student or class data available');
            return;
        }

        $data = [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'marked_by' => $student->user_id,
            'status' => 'present',
            'attendance_date' => date('Y-m-d'),
            'check_in_time' => '08:00:00',
        ];

        $attendance = $this->attendanceService->markAttendance($data);

        $this->assertDatabaseHas('student_attendances', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'present',
        ]);
    }

    public function testMarkBulkAttendance(): void
    {
        $students = Student::limit(2)->get();
        $class = ClassModel::first();

        if (! $class || count($students) < 2) {
            $this->markTestSkipped('Insufficient data for bulk attendance test');
            return;
        }

        $attendanceData = [
            [
                'student_id' => $students[0]->id,
                'status' => 'present',
            ],
            [
                'student_id' => $students[1]->id,
                'status' => 'absent',
            ],
        ];

        $records = $this->attendanceService->markBulkAttendance(
            $class->id,
            $attendanceData,
            $students[0]->user_id,
            $students[0]->user_id
        );

        $this->assertCount(2, $records);
        $this->assertEquals($records[0]['status'], 'present');
        $this->assertEquals($records[1]['status'], 'absent');
    }

    public function testGetStudentAttendance(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $result = $this->attendanceService->getStudentAttendance($student->id);

        $this->assertIsArray($result->attendances);
        $this->assertArrayHasKey('statistics', (array) $result);
        $this->assertArrayHasKey('attendance_percentage', $result->statistics);
        $this->assertArrayHasKey('present_days', $result->statistics);
    }

    public function testGetClassAttendance(): void
    {
        $class = ClassModel::first();

        if (! $class) {
            $this->markTestSkipped('No class data available');
            return;
        }

        $result = $this->attendanceService->getClassAttendance($class->id);

        $this->assertIsArray($result->attendances);
        $this->assertArrayHasKey('statistics', (array) $result);
        $this->assertArrayHasKey('students', $result);
    }

    public function testCalculateAttendancePercentage(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $statistics = $this->attendanceService->calculateAttendanceStatistics($student->id);

        $this->assertArrayHasKey('total_days', $statistics);
        $this->assertArrayHasKey('present_days', $statistics);
        $this->assertArrayHasKey('attendance_percentage', $statistics);
        $this->assertIsFloat($statistics['attendance_percentage']);
        $this->assertGreaterThanOrEqual(0.0, $statistics['attendance_percentage']);
        $this->assertLessThanOrEqual(100.0, $statistics['attendance_percentage']);
    }

    public function testScopeByStudent(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $query = StudentAttendance::query();

        $filteredQuery = $query->byStudent($student->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filteredQuery);
    }

    public function testScopeByClass(): void
    {
        $class = ClassModel::first();

        if (! $class) {
            $this->markTestSkipped('No class data available');
            return;
        }

        $query = StudentAttendance::query();

        $filteredQuery = $query->byClass($class->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filteredQuery);
    }

    public function testScopeByStatus(): void
    {
        $query = StudentAttendance::query();

        $presentQuery = $query->byStatus('present');

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $presentQuery);
    }

    public function testScopePresent(): void
    {
        $query = StudentAttendance::query();

        $presentQuery = $query->present();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $presentQuery);
    }

    public function testScopeAbsent(): void
    {
        $query = StudentAttendance::query();

        $absentQuery = $query->absent();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $absentQuery);
    }
}
