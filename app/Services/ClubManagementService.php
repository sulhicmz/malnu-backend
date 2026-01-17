<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Extracurricular\Club;
use App\Models\Extracurricular\ClubMembership;
use App\Models\Extracurricular\Activity;
use App\Models\Extracurricular\ActivityAttendance;
use App\Models\Extracurricular\ClubAdvisor;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hyperf\DbConnection\Db;

class ClubManagementService
{
    public function createClub(array $data): Club
    {
        return Club::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'max_members' => $data['max_members'] ?? null,
            'advisor_id' => $data['advisor_id'] ?? null,
        ]);
    }

    public function updateClub(string $clubId, array $data): Club
    {
        $club = Club::find($clubId);
        if (!$club) {
            throw new \Exception('Club not found');
        }

        $club->update(array_filter($data, fn($v) => $v !== null));

        return $club->refresh();
    }

    public function deleteClub(string $clubId): bool
    {
        $club = Club::find($clubId);
        if (!$club) {
            throw new \Exception('Club not found');
        }

        return $club->delete();
    }

    public function getClub(string $clubId): Club
    {
        return Club::with(['advisor', 'memberships.student', 'activities'])->find($clubId);
    }

    public function getClubs(array $filters = [])
    {
        $query = Club::with(['advisor', 'memberships', 'activities']);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['advisor_id'])) {
            $query->where('advisor_id', $filters['advisor_id']);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function addMember(string $clubId, string $studentId, string $role = 'member'): ClubMembership
    {
        return ClubMembership::create([
            'club_id' => $clubId,
            'student_id' => $studentId,
            'role' => $role,
            'joined_date' => date('Y-m-d'),
        ]);
    }

    public function removeMember(string $clubId, string $studentId): bool
    {
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('student_id', $studentId)
            ->first();

        if (!$membership) {
            return false;
        }

        return $membership->delete();
    }

    public function updateMemberRole(string $clubId, string $studentId, string $role): ClubMembership
    {
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('student_id', $studentId)
            ->first();

        if (!$membership) {
            throw new \Exception('Membership not found');
        }

        $membership->update(['role' => $role]);

        return $membership->refresh();
    }

    public function getClubMembers(string $clubId)
    {
        return ClubMembership::with('student')
            ->where('club_id', $clubId)
            ->get();
    }

    public function getStudentMemberships(string $studentId)
    {
        return ClubMembership::with('club')
            ->where('student_id', $studentId)
            ->get();
    }

    public function createActivity(string $clubId, array $data): Activity
    {
        return Activity::create([
            'club_id' => $clubId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'location' => $data['location'] ?? null,
            'max_attendees' => $data['max_attendees'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    public function updateActivity(string $activityId, array $data): Activity
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            throw new \Exception('Activity not found');
        }

        $activity->update(array_filter($data, fn($v) => $v !== null));

        return $activity->refresh();
    }

    public function deleteActivity(string $activityId): bool
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            throw new \Exception('Activity not found');
        }

        return $activity->delete();
    }

    public function getActivity(string $activityId): Activity
    {
        return Activity::with(['club', 'attendances.student'])->find($activityId);
    }

    public function getClubActivities(string $clubId, string $status = null)
    {
        $query = Activity::where('club_id', $clubId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('start_date', 'asc')->get();
    }

    public function getUpcomingActivities(int $limit = 10)
    {
        return Activity::with('club')
            ->where('status', 'scheduled')
            ->where('start_date', '>=', date('Y-m-d H:i:s'))
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get();
    }

    public function markAttendance(string $activityId, string $studentId, string $status, ?string $notes = null): ActivityAttendance
    {
        $attendance = ActivityAttendance::updateOrCreate(
            [
                'activity_id' => $activityId,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'notes' => $notes,
            ]
        );

        return $attendance;
    }

    public function getActivityAttendance(string $activityId)
    {
        return ActivityAttendance::with('student')
            ->where('activity_id', $activityId)
            ->get();
    }

    public function assignAdvisor(string $clubId, string $teacherId, ?string $notes = null): ClubAdvisor
    {
        return ClubAdvisor::updateOrCreate(
            [
                'club_id' => $clubId,
                'teacher_id' => $teacherId,
            ],
            [
                'assigned_date' => date('Y-m-d'),
                'notes' => $notes,
            ]
        );
    }

    public function removeAdvisor(string $clubId, string $teacherId): bool
    {
        $advisor = ClubAdvisor::where('club_id', $clubId)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$advisor) {
            return false;
        }

        return $advisor->delete();
    }

    public function getClubAdvisors(string $clubId)
    {
        return ClubAdvisor::with('teacher')
            ->where('club_id', $clubId)
            ->get();
    }

    public function getTeacherAdvisories(string $teacherId)
    {
        return ClubAdvisor::with('club')
            ->where('teacher_id', $teacherId)
            ->get();
    }

    public function getClubStatistics(string $clubId): array
    {
        $club = Club::find($clubId);
        if (!$club) {
            throw new \Exception('Club not found');
        }

        $totalMembers = ClubMembership::where('club_id', $clubId)->count();
        $totalActivities = Activity::where('club_id', $clubId)->count();
        $upcomingActivities = Activity::where('club_id', $clubId)
            ->where('status', 'scheduled')
            ->where('start_date', '>=', date('Y-m-d'))
            ->count();

        return [
            'total_members' => $totalMembers,
            'total_activities' => $totalActivities,
            'upcoming_activities' => $upcomingActivities,
            'capacity_utilization' => $club->max_members && $club->max_members > 0
                ? round(($totalMembers / $club->max_members) * 100, 2)
                : null,
        ];
    }

    public function getActivityAttendanceStatistics(string $activityId): array
    {
        $attendances = ActivityAttendance::where('activity_id', $activityId)->get();

        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $excused = $attendances->where('status', 'excused')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    public function getStudentParticipation(string $studentId): array
    {
        $memberships = ClubMembership::with('club')
            ->where('student_id', $studentId)
            ->get();

        $activities = ActivityAttendance::with('activity.club')
            ->where('student_id', $studentId)
            ->get();

        return [
            'memberships' => $memberships,
            'total_clubs' => $memberships->count(),
            'activities_attended' => $activities->count(),
            'activity_details' => $activities,
        ];
    }
}
