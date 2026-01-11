<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Schedule;
use App\Models\User;
use App\Services\TimetableService;
use Tymon\JWTAuth\Facades\JWTAuth;

class TimetableTest extends TestCase
{
    protected $user;
    protected $timetableService;
    protected $classModel;
    protected $subject;
    protected $teacher;
    protected $classSubject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->timetableService = $this->app->get(TimetableService::class);

        $this->classModel = ClassModel::create([
            'name' => 'X-A',
            'grade_level' => '10',
            'academic_year' => '2024-2025',
        ]);

        $this->subject = Subject::create([
            'name' => 'Matematika',
            'code' => 'MAT',
            'description' => 'Matematika',
        ]);

        $this->teacher = Teacher::create([
            'name' => 'Test Teacher',
            'nip' => '198506152008011001',
            'subject_id' => $this->subject->id,
            'join_date' => '2020-01-01',
        ]);

        $this->classSubject = ClassSubject::create([
            'class_id' => $this->classModel->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_create_schedule()
    {
        $token = JWTAuth::fromUser($this->user);

        $scheduleData = [
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/schedules', $scheduleData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Schedule created successfully'
            ]);

        $this->assertDatabaseHas('schedules', [
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);
    }

    public function test_detect_teacher_conflicts()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $conflictData = [
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:15',
            'end_time' => '09:00',
            'room' => 'R-102',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/conflicts', $conflictData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'teacher_conflicts'
                ]
            ]);
    }

    public function test_detect_room_conflicts()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $classSubject2 = ClassSubject::create([
            'class_id' => $this->classModel->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
        ]);

        $token = JWTAuth::fromUser($this->user);

        $conflictData = [
            'class_subject_id' => $classSubject2->id,
            'day_of_week' => 1,
            'start_time' => '08:30',
            'end_time' => '09:15',
            'room' => 'R-101',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/conflicts', $conflictData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'room_conflicts'
                ]
            ]);
    }

    public function test_validate_schedule_with_valid_data()
    {
        $token = JWTAuth::fromUser($this->user);

        $validSchedule = [
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/validate', $validSchedule);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => true
                ]
            ]);
    }

    public function test_validate_schedule_with_invalid_times()
    {
        $token = JWTAuth::fromUser($this->user);

        $invalidSchedule = [
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '09:00',
            'room' => 'R-101',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/validate', $invalidSchedule);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => false
                ]
            ]);
    }

    public function test_get_class_schedule()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/school/timetable/class/{$this->classModel->id}/schedule");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_get_teacher_schedule()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/school/timetable/teacher/{$this->teacher->id}/schedule");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_get_available_slots()
    {
        Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/school/timetable/available-slots?day_of_week=1&class_id=' . $this->classModel->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);

        $occupiedSlots = array_filter($data, function ($slot) {
            return $slot['start'] === '08:00' && $slot['end'] === '08:45';
        });

        $this->assertEmpty($occupiedSlots, 'Occupied slot should not appear in available slots');
    }

    public function test_update_schedule()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $updateData = [
            'room' => 'R-102',
            'start_time' => '09:00',
            'end_time' => '09:45',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/school/timetable/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Schedule updated successfully'
            ]);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'room' => 'R-102',
            'start_time' => '09:00',
            'end_time' => '09:45',
        ]);
    }

    public function test_delete_schedule()
    {
        $schedule = Schedule::create([
            'class_subject_id' => $this->classSubject->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
            'room' => 'R-101',
        ]);

        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/school/timetable/schedules/{$schedule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_generate_timetable_for_class()
    {
        $token = JWTAuth::fromUser($this->user);

        $generateData = [
            'class_id' => $this->classModel->id,
            'constraints' => [
                'preferred_rooms' => ['R-101', 'R-102']
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/school/timetable/generate', $generateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_time_ranges_overlap()
    {
        $this->assertTrue(
            $this->timetableService->timeRangesOverlap('08:00', '09:00', '08:30', '09:30')
        );

        $this->assertTrue(
            $this->timetableService->timeRangesOverlap('08:00', '09:00', '08:00', '09:00')
        );

        $this->assertFalse(
            $this->timetableService->timeRangesOverlap('08:00', '09:00', '09:00', '10:00')
        );

        $this->assertFalse(
            $this->timetableService->timeRangesOverlap('08:00', '09:00', '07:00', '08:00')
        );
    }
}
