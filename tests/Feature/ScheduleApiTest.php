<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Testing\TestClient;

/**
 * @internal
 * @coversNothing
 */
class ScheduleApiTest extends Client
{
    protected ?string $classSubjectId = null;

    protected ?string $teacherId = null;

    protected ?string $subjectId = null;

    protected ?string $classId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classId = ClassModel::create(['name' => 'Test Class', 'level' => '10', 'academic_year' => '2026'])->id;
        $this->subjectId = Subject::create(['code' => 'MATH101', 'name' => 'Mathematics', 'credit_hours' => 4])->id;
        $this->teacherId = Teacher::create([
            'nip' => '12345',
            'status' => 'active',
        ])->id;
        $this->classSubjectId = ClassSubject::create([
            'class_id' => $this->classId,
            'subject_id' => $this->subjectId,
            'teacher_id' => $this->teacherId,
        ])->id;
    }

    public function testIndexSchedules()
    {
        $response = $this->get('/api/schedules');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function testIndexWithClassFilter()
    {
        $response = $this->get('/api/schedules?class_id=' . $this->classId);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function testIndexWithDayFilter()
    {
        $response = $this->get('/api/schedules?day_of_week=1');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function testShowSchedule()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room 101',
        ]);

        $response = $this->get('/api/schedules/' . $schedule->id);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
            ],
        ]);
    }

    public function testShowNonexistentSchedule()
    {
        $response = $this->get('/api/schedules/nonexistent-id');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Schedule not found',
        ]);
    }

    public function testStoreSchedule()
    {
        $data = [
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room' => 'Room 201',
        ];

        $response = $this->post('/api/schedules', $data);
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Schedule created successfully',
        ]);

        $this->assertDatabaseHas('schedules', [
            'day_of_week' => 2,
            'room' => 'Room 201',
        ]);
    }

    public function testStoreMissingRequiredFields()
    {
        $data = [
            'day_of_week' => 2,
        ];

        $response = $this->post('/api/schedules', $data);
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function testStoreWithTeacherConflict()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 3,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'room' => 'Room 301',
        ]);

        $data = [
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 3,
            'start_time' => '14:30',
            'end_time' => '15:30',
            'room' => 'Room 302',
        ];

        $response = $this->post('/api/schedules', $data);
        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Schedule conflicts detected',
            'error_code' => 'SCHEDULE_CONFLICT',
        ]);
    }

    public function testStoreWithRoomConflict()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 4,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room 401',
        ]);

        $data = [
            'class_subject_id' => ClassSubject::create([
                'class_id' => $this->classId,
                'subject_id' => Subject::create(['code' => 'ENG101', 'name' => 'English', 'credit_hours' => 3])->id,
                'teacher_id' => $this->teacherId,
            ])->id,
            'day_of_week' => 4,
            'start_time' => '08:30',
            'end_time' => '09:30',
            'room' => 'Room 401',
        ];

        $response = $this->post('/api/schedules', $data);
        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Schedule conflicts detected',
            'error_code' => 'SCHEDULE_CONFLICT',
        ]);
    }

    public function testUpdateSchedule()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 5,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room' => 'Room 501',
        ]);

        $data = [
            'room' => 'Room 502',
        ];

        $response = $this->put('/api/schedules/' . $schedule->id, $data);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Schedule updated successfully',
        ]);
    }

    public function testUpdateWithConflict()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 6,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'room' => 'Room 601',
        ]);

        Schedule::create([
            'class_subject_id' => ClassSubject::create([
                'class_id' => $this->classId,
                'subject_id' => Subject::create(['code' => 'SCI101', 'name' => 'Science', 'credit_hours' => 4])->id,
                'teacher_id' => $this->teacherId,
            ])->id,
            'day_of_week' => 6,
            'start_time' => '12:30',
            'end_time' => '13:30',
            'room' => 'Room 602',
        ]);

        $data = [
            'start_time' => '12:15',
            'end_time' => '13:15',
            'room' => 'Room 602',
        ];

        $response = $this->put('/api/schedules/' . $schedule->id, $data);
        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Schedule conflicts detected',
            'error_code' => 'SCHEDULE_CONFLICT',
        ]);
    }

    public function testDestroySchedule()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubjectId,
            'day_of_week' => 1,
            'start_time' => '16:00',
            'end_time' => '17:00',
            'room' => 'Room 701',
        ]);

        $response = $this->delete('/api/schedules/' . $schedule->id);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Schedule deleted successfully',
        ]);

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }
}
