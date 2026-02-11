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

    public function testDetectChronicAbsenteeismWithNoAbsentees(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $result = $this->attendanceService->detectChronicAbsenteeism();

        $this->assertIsArray($result);
        $this->assertContainsOnly('array', $result);
    }

    public function testDetectChronicAbsenteeismWithOneAbsentee(): void
    {
        $student = Student::with('user')->first();

        if (! $student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $cutoffDate = date('Y-m-d', strtotime('-30 days'));

        $attendanceData = [];
        for ($i = 0; $i < 5; ++$i) {
            $attendanceData[] = [
                'student_id' => $student->id,
                'class_id' => $student->class_id ?? '1',
                'teacher_id' => '1',
                'attendance_date' => date('Y-m-d', strtotime("-{$i} days")),
                'status' => 'absent',
                'marked_by' => '1',
            ];
        }

        StudentAttendance::insert($attendanceData);

        $result = $this->attendanceService->detectChronicAbsenteeism();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $foundStudent = collect($result)->first(function ($absentee) use ($student) {
            return $absentee['student_id'] === $student->id;
        });

        if ($foundStudent) {
            $this->assertEquals($student->id, $foundStudent['student_id']);
            $this->assertGreaterThanOrEqual(3, $foundStudent['absent_days']);
            $this->assertArrayHasKey('attendance_percentage', $foundStudent);
        }

        StudentAttendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->delete();
    }

    public function testDetectChronicAbsenteeismWithMultipleAbsentees(): void
    {
        $students = Student::with('user')->limit(3)->get();

        if ($students->count() < 3) {
            $this->markTestSkipped('Insufficient student data for test');
            return;
        }

        $attendanceData = [];
        foreach ($students as $student) {
            for ($i = 0; $i < 5; ++$i) {
                $attendanceData[] = [
                    'student_id' => $student->id,
                    'class_id' => $student->class_id ?? '1',
                    'teacher_id' => '1',
                    'attendance_date' => date('Y-m-d', strtotime("-{$i} days")),
                    'status' => 'absent',
                    'marked_by' => '1',
                ];
            }
        }

        StudentAttendance::insert($attendanceData);

        $result = $this->attendanceService->detectChronicAbsenteeism();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(3, count($result));

        foreach ($result as $absentee) {
            $this->assertArrayHasKey('student_id', $absentee);
            $this->assertArrayHasKey('student_name', $absentee);
            $this->assertArrayHasKey('absent_days', $absentee);
            $this->assertArrayHasKey('attendance_percentage', $absentee);
            $this->assertGreaterThanOrEqual(3, $absentee['absent_days']);
            $this->assertGreaterThanOrEqual(0, $absentee['attendance_percentage']);
            $this->assertLessThanOrEqual(100, $absentee['attendance_percentage']);
        }

        StudentAttendance::whereIn('student_id', $students->pluck('id'))->delete();
    }
}
