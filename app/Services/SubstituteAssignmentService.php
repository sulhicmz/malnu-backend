<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance\SubstituteTeacher;
use App\Models\Attendance\SubstituteAssignment;
use App\Models\Attendance\LeaveRequest;
use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\Teacher;

class SubstituteAssignmentService
{
    public function findAvailableSubstitutes(string $subjectId = null, string $classId = null, string $date = null): array
    {
        $query = SubstituteTeacher::with(['teacher' => function ($q) {
            $q->with('user');
        }])->where('is_active', true);

        if ($subjectId) {
            $query->whereJsonContains('available_subjects', $subjectId);
        }

        if ($classId) {
            $query->whereJsonContains('available_classes', $classId);
        }

        $substitutes = $query->get()->toArray();

        $availableSubstitutes = [];

        foreach ($substitutes as $substitute) {
            $hasConflict = $this->checkSubstituteAvailability($substitute['id'], $date);
            if (!$hasConflict) {
                $availableSubstitutes[] = $substitute;
            }
        }

        return $availableSubstitutes;
    }

    public function assignSubstitute(string $leaveRequestId, string $substituteTeacherId, string $classSubjectId = null): bool
    {
        $leaveRequest = LeaveRequest::find($leaveRequestId);
        if (!$leaveRequest) {
            return false;
        }

        if ($leaveRequest->status !== 'approved') {
            return false;
        }

        $substituteTeacher = SubstituteTeacher::find($substituteTeacherId);
        if (!$substituteTeacher || !$substituteTeacher->is_active) {
            return false;
        }

        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
        $dates = [];
        foreach ($period as $day) {
            $dates[] = $day->format('Y-m-d');
        }

        $assignments = [];
        foreach ($dates as $date) {
            $assignment = SubstituteAssignment::create([
                'leave_request_id' => $leaveRequestId,
                'substitute_teacher_id' => $substituteTeacherId,
                'class_subject_id' => $classSubjectId,
                'assignment_date' => $date,
                'status' => 'pending',
                'payment_amount' => $substituteTeacher->hourly_rate * 8,
            ]);
            $assignments[] = $assignment->id;
        }

        $leaveRequest->update(['substitute_assigned_id' => $substituteTeacherId]);

        return true;
    }

    public function checkSubstituteAvailability(string $substituteTeacherId, string $date): bool
    {
        $existingAssignment = SubstituteAssignment::where('substitute_teacher_id', $substituteTeacherId)
            ->where('assignment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->first();

        return $existingAssignment !== null;
    }

    public function calculatePayment(string $substituteAssignmentId): float
    {
        $assignment = SubstituteAssignment::with('substituteTeacher')->find($substituteAssignmentId);
        if (!$assignment) {
            return 0.0;
        }

        $substituteTeacher = $assignment->substituteTeacher;
        $hourlyRate = $substituteTeacher->hourly_rate ?? 0;

        $leaveRequest = LeaveRequest::find($assignment->leave_request_id);
        if (!$leaveRequest) {
            return $hourlyRate * 8;
        }

        $totalDays = $leaveRequest->total_days;
        return $totalDays * $hourlyRate * 8;
    }

    public function getSubstituteAssignmentsByDateRange(string $startDate, string $endDate): array
    {
        $assignments = SubstituteAssignment::with([
            'substituteTeacher' => function ($q) {
                $q->with(['teacher.user']);
            },
            'leaveRequest',
            'classSubject'
        ])->whereBetween('assignment_date', [$startDate, $endDate])
            ->orderBy('assignment_date', 'asc')
            ->get()
            ->toArray();

        return $assignments;
    }

    public function getSubstituteStatistics(string $substituteTeacherId): array
    {
        $assignments = SubstituteAssignment::where('substitute_teacher_id', $substituteTeacherId)
            ->where('status', 'completed')
            ->get();

        $totalAssignments = $assignments->count();
        $totalPayment = $assignments->sum('payment_amount');

        $assignmentsByMonth = [];
        foreach ($assignments->toArray() as $item) {
            $month = date('Y-m', strtotime($item['assignment_date']));
            if (!isset($assignmentsByMonth[$month])) {
                $assignmentsByMonth[$month] = [];
            }
            $assignmentsByMonth[$month][] = $item;
        }

        return [
            'total_assignments' => $totalAssignments,
            'total_earnings' => (float) $totalPayment,
            'assignments_by_month' => $assignmentsByMonth,
        ];
    }
}
