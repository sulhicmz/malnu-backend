<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Models\Attendance\StudentAttendance;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\User;

class AttendanceOptimizationTest extends TestCase
{
    private AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = $this->app->get(AttendanceService::class);
    }

    public function test_calculateAttendanceStatistics_with_no_attendance(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $statistics = $this->attendanceService->calculateAttendanceStatistics($student->id);

        $this->assertArrayHasKey('total_days', $statistics);
        $this->assertArrayHasKey('present_days', $statistics);
        $this->assertArrayHasKey('absent_days', $statistics);
        $this->assertArrayHasKey('late_days', $statistics);
        $this->assertArrayHasKey('excused_days', $statistics);
        $this->assertArrayHasKey('attendance_percentage', $statistics);
        $this->assertArrayHasKey('is_chronic_absentee', $statistics);

        $this->assertEquals(0, $statistics['total_days']);
        $this->assertEquals(0, $statistics['present_days']);
        $this->assertEquals(0, $statistics['absent_days']);
        $this->assertEquals(0, $statistics['late_days']);
        $this->assertEquals(0, $statistics['excused_days']);
        $this->assertEquals(0.0, $statistics['attendance_percentage']);
        $this->assertFalse($statistics['is_chronic_absentee']);
    }

    public function test_calculateAttendanceStatistics_with_all_present(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (!$student || !$class) {
            $this->markTestSkipped('No student or class data available');
            return;
        }

        $attendanceDate = date('Y-m-d');

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => $attendanceDate,
            'status' => 'present',
            'marked_by' => $student->user_id,
        ]);

        $statistics = $this->attendanceService->calculateAttendanceStatistics($student->id);

        $this->assertEquals(1, $statistics['total_days']);
        $this->assertEquals(1, $statistics['present_days']);
        $this->assertEquals(0, $statistics['absent_days']);
        $this->assertEquals(0, $statistics['late_days']);
        $this->assertEquals(0, $statistics['excused_days']);
        $this->assertEquals(100.0, $statistics['attendance_percentage']);
    }

    public function test_calculateAttendanceStatistics_with_mixed_status(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (!$student || !$class) {
            $this->markTestSkipped('No student or class data available');
            return;
        }

        $attendanceDate = date('Y-m-d');

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => $attendanceDate,
            'status' => 'present',
            'marked_by' => $student->user_id,
        ]);

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => date('Y-m-d', strtotime('+1 day')),
            'status' => 'absent',
            'marked_by' => $student->user_id,
        ]);

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => date('Y-m-d', strtotime('+2 days')),
            'status' => 'late',
            'marked_by' => $student->user_id,
        ]);

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => date('Y-m-d', strtotime('+3 days')),
            'status' => 'excused',
            'marked_by' => $student->user_id,
        ]);

        $statistics = $this->attendanceService->calculateAttendanceStatistics($student->id);

        $this->assertEquals(4, $statistics['total_days']);
        $this->assertEquals(1, $statistics['present_days']);
        $this->assertEquals(1, $statistics['absent_days']);
        $this->assertEquals(1, $statistics['late_days']);
        $this->assertEquals(1, $statistics['excused_days']);
        $this->assertEquals(25.0, $statistics['attendance_percentage']);
    }

    public function test_calculateAttendanceStatistics_with_date_range(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (!$student || !$class) {
            $this->markTestSkipped('No student or class data available');
            return;
        }

        $date1 = date('Y-m-d');
        $date2 = date('Y-m-d', strtotime('+1 day'));
        $date3 = date('Y-m-d', strtotime('+2 days'));

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => $date1,
            'status' => 'present',
            'marked_by' => $student->user_id,
        ]);

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => $date2,
            'status' => 'present',
            'marked_by' => $student->user_id,
        ]);

        StudentAttendance::factory()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'teacher_id' => $student->user_id,
            'attendance_date' => $date3,
            'status' => 'absent',
            'marked_by' => $student->user_id,
        ]);

        $statistics = $this->attendanceService->calculateAttendanceStatistics(
            $student->id,
            $date1,
            $date2
        );

        $this->assertEquals(2, $statistics['total_days']);
        $this->assertEquals(2, $statistics['present_days']);
        $this->assertEquals(0, $statistics['absent_days']);
        $this->assertEquals(100.0, $statistics['attendance_percentage']);
    }

    public function test_calculateAttendanceStatistics_chronic_absentee_detection(): void
    {
        $student = Student::first();
        $class = ClassModel::first();

        if (!$student || !$class) {
            $this->markTestSkipped('No student or class data available');
            return;
        }

        for ($i = 0; $i < 3; $i++) {
            StudentAttendance::factory()->create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'teacher_id' => $student->user_id,
                'attendance_date' => date('Y-m-d', strtotime("+$i days")),
                'status' => 'absent',
                'marked_by' => $student->user_id,
            ]);
        }

        $statistics = $this->attendanceService->calculateAttendanceStatistics($student->id);

        $this->assertEquals(3, $statistics['absent_days']);
        $this->assertTrue($statistics['is_chronic_absentee']);
    }
}
