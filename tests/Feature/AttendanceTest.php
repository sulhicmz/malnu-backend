<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;

class AttendanceTest extends TestCase
{
    private AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = $this->app->get(AttendanceService::class);
    }

    public function test_mark_student_attendance(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (!$student || !$class) {
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

    public function test_mark_bulk_attendance(): void
    {
        $students = Student::limit(2)->get();
        $class = ClassModel::first();

        if (!$class || count($students) < 2) {
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

    public function test_get_student_attendance(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $result = $this->attendanceService->getStudentAttendance($student->id);

        $this->assertIsArray($result->attendances);
        $this->assertArrayHasKey('statistics', (array) $result);
        $this->assertArrayHasKey('attendance_percentage', $result->statistics);
        $this->assertArrayHasKey('present_days', $result->statistics);
    }

    public function test_get_class_attendance(): void
    {
        $class = ClassModel::first();

        if (!$class) {
            $this->markTestSkipped('No class data available');
            return;
        }

        $result = $this->attendanceService->getClassAttendance($class->id);

        $this->assertIsArray($result->attendances);
        $this->assertArrayHasKey('statistics', (array) $result);
        $this->assertArrayHasKey('students', $result);
    }

    public function test_calculate_attendance_percentage(): void
    {
        $student = Student::first();

        if (!$student) {
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

    public function test_scope_by_student(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $query = StudentAttendance::query();

        $filteredQuery = $query->byStudent($student->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filteredQuery);
    }

    public function test_scope_by_class(): void
    {
        $class = ClassModel::first();

        if (!$class) {
            $this->markTestSkipped('No class data available');
            return;
        }

        $query = StudentAttendance::query();

        $filteredQuery = $query->byClass($class->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filteredQuery);
    }

    public function test_scope_by_status(): void
    {
        $query = StudentAttendance::query();

        $presentQuery = $query->byStatus('present');

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $presentQuery);
    }

    public function test_scope_present(): void
    {
        $query = StudentAttendance::query();

        $presentQuery = $query->present();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $presentQuery);
    }

    public function test_scope_absent(): void
    {
        $query = StudentAttendance::query();

        $absentQuery = $query->absent();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $absentQuery);
    }
}
