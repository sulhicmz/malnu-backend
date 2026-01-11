<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Schedule;
use Hyperf\DbConnection\Db;
use Exception;

class TimetableService
{
    public function generateTimetable(array $options = []): array
    {
        $constraints = $options['constraints'] ?? [];
        $classId = $options['class_id'] ?? null;
        $teacherId = $options['teacher_id'] ?? null;

        $schedules = [];

        if ($classId) {
            $schedules = $this->generateForClass($classId, $constraints);
        } elseif ($teacherId) {
            $schedules = $this->generateForTeacher($teacherId, $constraints);
        } else {
            throw new Exception('Either class_id or teacher_id must be provided');
        }

        return $schedules;
    }

    public function detectConflicts(array $scheduleData): array
    {
        $conflicts = [];

        $teacherConflicts = $this->detectTeacherConflicts($scheduleData);
        if (!empty($teacherConflicts)) {
            $conflicts['teacher_conflicts'] = $teacherConflicts;
        }

        $roomConflicts = $this->detectRoomConflicts($scheduleData);
        if (!empty($roomConflicts)) {
            $conflicts['room_conflicts'] = $roomConflicts;
        }

        $classConflicts = $this->detectClassConflicts($scheduleData);
        if (!empty($classConflicts)) {
            $conflicts['class_conflicts'] = $classConflicts;
        }

        return $conflicts;
    }

    public function validateSchedule(array $scheduleData): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        if (empty($scheduleData['class_subject_id'])) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Class subject ID is required';
        }

        if (!isset($scheduleData['day_of_week']) || $scheduleData['day_of_week'] < 1 || $scheduleData['day_of_week'] > 7) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Day of week must be between 1 and 7 (Monday=1, Sunday=7)';
        }

        if (empty($scheduleData['start_time'])) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Start time is required';
        }

        if (empty($scheduleData['end_time'])) {
            $validation['valid'] = false;
            $validation['errors'][] = 'End time is required';
        }

        if (!empty($scheduleData['start_time']) && !empty($scheduleData['end_time'])) {
            $startTime = strtotime($scheduleData['start_time']);
            $endTime = strtotime($scheduleData['end_time']);

            if ($startTime >= $endTime) {
                $validation['valid'] = false;
                $validation['errors'][] = 'End time must be after start time';
            }

            $duration = ($endTime - $startTime) / 60;
            if ($duration < 30 || $duration > 180) {
                $validation['warnings'][] = 'Schedule duration is unusual (' . $duration . ' minutes). Recommended: 45-90 minutes';
            }
        }

        $conflicts = $this->detectConflicts($scheduleData);
        if (!empty($conflicts)) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Schedule has conflicts: ' . json_encode($conflicts);
        }

        return $validation;
    }

    public function createSchedule(array $data): Schedule
    {
        $validation = $this->validateSchedule($data);
        if (!$validation['valid']) {
            throw new Exception('Schedule validation failed: ' . implode(', ', $validation['errors']));
        }

        return Schedule::create($data);
    }

    public function updateSchedule(string $scheduleId, array $data): Schedule
    {
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            throw new Exception('Schedule not found');
        }

        $updatedData = array_merge($schedule->toArray(), $data);
        $validation = $this->validateSchedule($updatedData);
        if (!$validation['valid']) {
            throw new Exception('Schedule validation failed: ' . implode(', ', $validation['errors']));
        }

        $schedule->update($data);
        return $schedule->fresh();
    }

    public function deleteSchedule(string $scheduleId): bool
    {
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            throw new Exception('Schedule not found');
        }

        return $schedule->delete();
    }

    public function getScheduleByClass(string $classId, array $filters = []): array
    {
        $query = Schedule::with(['classSubject.class', 'classSubject.subject', 'classSubject.teacher'])
            ->whereHas('classSubject', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });

        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        return $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }

    public function getScheduleByTeacher(string $teacherId, array $filters = []): array
    {
        $query = Schedule::with(['classSubject.class', 'classSubject.subject'])
            ->whereHas('classSubject', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            });

        if (!empty($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        return $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }

    public function getClassScheduleByDay(string $classId, int $dayOfWeek): array
    {
        return $this->getScheduleByClass($classId, ['day_of_week' => $dayOfWeek]);
    }

    public function getTeacherScheduleByDay(string $teacherId, int $dayOfWeek): array
    {
        return $this->getScheduleByTeacher($teacherId, ['day_of_week' => $dayOfWeek]);
    }

    public function findAvailableSlots(array $constraints = []): array
    {
        $dayOfWeek = $constraints['day_of_week'] ?? 1;
        $classId = $constraints['class_id'] ?? null;
        $teacherId = $constraints['teacher_id'] ?? null;

        $standardTimeSlots = [
            ['start' => '07:30', 'end' => '08:15'],
            ['start' => '08:15', 'end' => '09:00'],
            ['start' => '09:00', 'end' => '09:45'],
            ['start' => '10:00', 'end' => '10:45'],
            ['start' => '10:45', 'end' => '11:30'],
            ['start' => '11:30', 'end' => '12:15'],
            ['start' => '13:00', 'end' => '13:45'],
            ['start' => '13:45', 'end' => '14:30']
        ];

        $occupiedSlots = [];

        if ($classId) {
            $classSchedules = $this->getClassScheduleByDay($classId, $dayOfWeek);
            foreach ($classSchedules as $schedule) {
                $occupiedSlots[] = [
                    'start' => substr($schedule['start_time'], 0, 5),
                    'end' => substr($schedule['end_time'], 0, 5)
                ];
            }
        }

        if ($teacherId) {
            $teacherSchedules = $this->getTeacherScheduleByDay($teacherId, $dayOfWeek);
            foreach ($teacherSchedules as $schedule) {
                $occupiedSlots[] = [
                    'start' => substr($schedule['start_time'], 0, 5),
                    'end' => substr($schedule['end_time'], 0, 5)
                ];
            }
        }

        $availableSlots = [];
        foreach ($standardTimeSlots as $slot) {
            $isOccupied = false;
            foreach ($occupiedSlots as $occupied) {
                if ($this->timeRangesOverlap(
                    $slot['start'],
                    $slot['end'],
                    $occupied['start'],
                    $occupied['end']
                )) {
                    $isOccupied = true;
                    break;
                }
            }
            if (!$isOccupied) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }

    private function detectTeacherConflicts(array $scheduleData): array
    {
        if (empty($scheduleData['class_subject_id'])) {
            return [];
        }

        $classSubject = ClassSubject::find($scheduleData['class_subject_id']);
        if (!$classSubject || !$classSubject->teacher_id) {
            return [];
        }

        $teacherId = $classSubject->teacher_id;

        $conflicts = Schedule::whereHas('classSubject', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->where('day_of_week', $scheduleData['day_of_week'])
        ->where('id', '!=', $scheduleData['id'] ?? '0')
        ->get();

        $conflictingSchedules = [];
        foreach ($conflicts as $conflict) {
            if ($this->timeRangesOverlap(
                $scheduleData['start_time'],
                $scheduleData['end_time'],
                $conflict->start_time,
                $conflict->end_time
            )) {
                $conflictingSchedules[] = [
                    'schedule_id' => $conflict->id,
                    'type' => 'teacher',
                    'message' => 'Teacher is already scheduled during this time',
                    'conflicting_schedule' => $conflict->toArray()
                ];
            }
        }

        return $conflictingSchedules;
    }

    private function detectRoomConflicts(array $scheduleData): array
    {
        if (empty($scheduleData['room'])) {
            return [];
        }

        $room = $scheduleData['room'];

        $conflicts = Schedule::where('room', $room)
            ->where('day_of_week', $scheduleData['day_of_week'])
            ->where('id', '!=', $scheduleData['id'] ?? '0')
            ->get();

        $conflictingSchedules = [];
        foreach ($conflicts as $conflict) {
            if ($this->timeRangesOverlap(
                $scheduleData['start_time'],
                $scheduleData['end_time'],
                $conflict->start_time,
                $conflict->end_time
            )) {
                $conflictingSchedules[] = [
                    'schedule_id' => $conflict->id,
                    'type' => 'room',
                    'message' => 'Room is already booked during this time',
                    'conflicting_schedule' => $conflict->toArray()
                ];
            }
        }

        return $conflictingSchedules;
    }

    private function detectClassConflicts(array $scheduleData): array
    {
        if (empty($scheduleData['class_subject_id'])) {
            return [];
        }

        $classSubject = ClassSubject::find($scheduleData['class_subject_id']);
        if (!$classSubject || !$classSubject->class_id) {
            return [];
        }

        $classId = $classSubject->class_id;

        $conflicts = Schedule::whereHas('classSubject', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })
        ->where('day_of_week', $scheduleData['day_of_week'])
        ->where('id', '!=', $scheduleData['id'] ?? '0')
        ->get();

        $conflictingSchedules = [];
        foreach ($conflicts as $conflict) {
            if ($this->timeRangesOverlap(
                $scheduleData['start_time'],
                $scheduleData['end_time'],
                $conflict->start_time,
                $conflict->end_time
            )) {
                $conflictingSchedules[] = [
                    'schedule_id' => $conflict->id,
                    'type' => 'class',
                    'message' => 'Class is already scheduled during this time',
                    'conflicting_schedule' => $conflict->toArray()
                ];
            }
        }

        return $conflictingSchedules;
    }

    private function timeRangesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $s1 = strtotime($start1);
        $e1 = strtotime($end1);
        $s2 = strtotime($start2);
        $e2 = strtotime($end2);

        return !($e1 <= $s2 || $s1 >= $e2);
    }

    private function generateForClass(string $classId, array $constraints = []): array
    {
        $classSubjects = ClassSubject::where('class_id', $classId)->get();
        $generatedSchedules = [];

        $daysOfWeek = [1, 2, 3, 4, 5];
        $timeSlots = [
            ['start' => '07:30', 'end' => '08:15'],
            ['start' => '08:15', 'end' => '09:00'],
            ['start' => '09:00', 'end' => '09:45'],
            ['start' => '10:00', 'end' => '10:45'],
            ['start' => '10:45', 'end' => '11:30'],
            ['start' => '11:30', 'end' => '12:15'],
            ['start' => '13:00', 'end' => '13:45'],
            ['start' => '13:45', 'end' => '14:30']
        ];

        $subjectIndex = 0;
        foreach ($daysOfWeek as $day) {
            foreach ($timeSlots as $slot) {
                if ($subjectIndex >= count($classSubjects)) {
                    break 2;
                }

                $classSubject = $classSubjects[$subjectIndex];
                $availableSlots = $this->findAvailableSlots([
                    'day_of_week' => $day,
                    'class_id' => $classId,
                    'teacher_id' => $classSubject->teacher_id
                ]);

                $slotAvailable = false;
                foreach ($availableSlots as $available) {
                    if ($available['start'] === $slot['start'] && $available['end'] === $slot['end']) {
                        $slotAvailable = true;
                        break;
                    }
                }

                if ($slotAvailable) {
                    $generatedSchedules[] = [
                        'class_subject_id' => $classSubject->id,
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'room' => $this->assignRoom($constraints['preferred_rooms'] ?? [])
                    ];
                    $subjectIndex++;
                }
            }
        }

        return $generatedSchedules;
    }

    private function generateForTeacher(string $teacherId, array $constraints = []): array
    {
        $classSubjects = ClassSubject::where('teacher_id', $teacherId)->get();
        $generatedSchedules = [];

        $daysOfWeek = [1, 2, 3, 4, 5];
        $timeSlots = [
            ['start' => '07:30', 'end' => '08:15'],
            ['start' => '08:15', 'end' => '09:00'],
            ['start' => '09:00', 'end' => '09:45'],
            ['start' => '10:00', 'end' => '10:45'],
            ['start' => '10:45', 'end' => '11:30'],
            ['start' => '11:30', 'end' => '12:15'],
            ['start' => '13:00', 'end' => '13:45'],
            ['start' => '13:45', 'end' => '14:30']
        ];

        $subjectIndex = 0;
        foreach ($daysOfWeek as $day) {
            foreach ($timeSlots as $slot) {
                if ($subjectIndex >= count($classSubjects)) {
                    break 2;
                }

                $classSubject = $classSubjects[$subjectIndex];
                $availableSlots = $this->findAvailableSlots([
                    'day_of_week' => $day,
                    'class_id' => $classSubject->class_id,
                    'teacher_id' => $teacherId
                ]);

                $slotAvailable = false;
                foreach ($availableSlots as $available) {
                    if ($available['start'] === $slot['start'] && $available['end'] === $slot['end']) {
                        $slotAvailable = true;
                        break;
                    }
                }

                if ($slotAvailable) {
                    $generatedSchedules[] = [
                        'class_subject_id' => $classSubject->id,
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'room' => $this->assignRoom($constraints['preferred_rooms'] ?? [])
                    ];
                    $subjectIndex++;
                }
            }
        }

        return $generatedSchedules;
    }

    private function assignRoom(array $preferredRooms = []): string
    {
        if (!empty($preferredRooms)) {
            return $preferredRooms[array_rand($preferredRooms)];
        }

        $rooms = ['R-101', 'R-102', 'R-103', 'R-104', 'R-105', 'Lab-1', 'Lab-2'];
        return $rooms[array_rand($rooms)];
    }
}
