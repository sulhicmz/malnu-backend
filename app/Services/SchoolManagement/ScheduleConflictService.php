<?php

declare(strict_types=1);

namespace App\Services\SchoolManagement;

use App\Models\SchoolManagement\Schedule;
use App\Models\SchoolManagement\ClassSubject;

class ScheduleConflictService
{
    public function detectConflicts(array $scheduleData, ?string $excludeScheduleId = null): array
    {
        $classSubjectId = $scheduleData['class_subject_id'] ?? null;
        $dayOfWeek = (int) ($scheduleData['day_of_week'] ?? 0);
        $startTime = $scheduleData['start_time'] ?? null;
        $endTime = $scheduleData['end_time'] ?? null;
        $room = $scheduleData['room'] ?? null;

        $conflicts = [];

        if (!$classSubjectId || !$startTime || !$endTime) {
            return $conflicts;
        }

        $classSubject = ClassSubject::find($classSubjectId);
        if (!$classSubject) {
            return ['class_subject_id' => ['Class subject not found']];
        }

        $teacherId = $classSubject->teacher_id;

        $query = Schedule::with(['classSubject', 'classSubject.teacher'])
            ->where('day_of_week', $dayOfWeek);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $existingSchedules = $query->get();

        foreach ($existingSchedules as $existing) {
            if ($this->hasTimeOverlap($startTime, $endTime, $existing->start_time, $existing->end_time)) {
                if ($teacherId && $existing->classSubject->teacher_id === $teacherId) {
                    $conflicts[] = [
                        'type' => 'teacher_conflict',
                        'message' => sprintf(
                            'Teacher %s is already scheduled during this time slot on day %d',
                            $existing->classSubject->teacher->user->name ?? 'Unknown',
                            $dayOfWeek
                        ),
                        'conflicting_schedule_id' => $existing->id,
                    ];
                }

                if ($room && $existing->room === $room) {
                    $conflicts[] = [
                        'type' => 'room_conflict',
                        'message' => sprintf(
                            'Room %s is already booked during this time slot on day %d',
                            $room,
                            $dayOfWeek
                        ),
                        'conflicting_schedule_id' => $existing->id,
                    ];
                }
            }
        }

        if ($startTime >= $endTime) {
            $conflicts[] = [
                'type' => 'time_error',
                'message' => 'Start time must be before end time',
            ];
        }

        return $conflicts;
    }

    private function hasTimeOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}
