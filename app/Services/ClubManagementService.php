<?php

declare (strict_types = 1);

namespace App\Services;

use App\Models\ClubManagement\Club;
use App\Models\ClubManagement\ClubMembership;
use App\Models\ClubManagement\Activity;
use App\Models\ClubManagement\ActivityAttendance;
use App\Models\ClubManagement\ClubAdvisor;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Support\Facades\DB;

class ClubManagementService
{
    public function createClub(array $data): Club
    {
        return Club::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'academic',
            'max_members' => $data['max_members'] ?? null,
            'advisor_id' => $data['advisor_id'] ?? null,
            'status' => 'active',
        ]);
    }

    public function updateClub(string $id, array $data): ?Club
    {
        $club = Club::find($id);
        if (!$club) {
            return null;
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['category'])) {
            $updateData['category'] = $data['category'];
        }
        if (isset($data['max_members'])) {
            $updateData['max_members'] = $data['max_members'];
        }
        if (isset($data['advisor_id'])) {
            $updateData['advisor_id'] = $data['advisor_id'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        $club->update($updateData);
        return $club;
    }

    public function deleteClub(string $id): bool
    {
        $club = Club::find($id);
        if (!$club) {
            return false;
        }

        return $club->delete();
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

    public function updateMemberRole(string $clubId, string $studentId, string $role): ?ClubMembership
    {
        $membership = ClubMembership::where('club_id', $clubId)
            ->where('student_id', $studentId)
            ->first();

        if (!$membership) {
            return null;
        }

        $membership->role = $role;
        $membership->save();
        return $membership;
    }

    public function getClubMembers(string $clubId, string $role = null)
    {
        $query = ClubMembership::where('club_id', $clubId)
            ->with(['student', 'student.user']);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->get();
    }

    public function getStudentClubs(string $studentId)
    {
        return ClubMembership::where('student_id', $studentId)
            ->with('club')
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
        ]);
    }

    public function updateActivity(string $id, array $data): ?Activity
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return null;
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['start_date'])) {
            $updateData['start_date'] = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $updateData['end_date'] = $data['end_date'];
        }
        if (isset($data['location'])) {
            $updateData['location'] = $data['location'];
        }
        if (isset($data['max_attendees'])) {
            $updateData['max_attendees'] = $data['max_attendees'];
        }

        $activity->update($updateData);
        return $activity;
    }

    public function deleteActivity(string $id): bool
    {
        $activity = Activity::find($id);
        if (!$activity) {
            return false;
        }

        return $activity->delete();
    }

    public function markAttendance(string $activityId, string $studentId, string $status = 'present', ?string $notes = null): ActivityAttendance
    {
        return ActivityAttendance::updateOrCreate(
            [
                'activity_id' => $activityId,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'notes' => $notes,
            ]
        );
    }

    public function getActivityAttendances(string $activityId, string $status = null)
    {
        $query = ActivityAttendance::where('activity_id', $activityId)
            ->with(['student', 'student.user']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function assignAdvisor(string $clubId, string $teacherId): ClubAdvisor
    {
        return ClubAdvisor::create([
            'club_id' => $clubId,
            'teacher_id' => $teacherId,
            'assigned_date' => date('Y-m-d'),
        ]);
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
        return ClubAdvisor::where('club_id', $clubId)
            ->with(['teacher', 'teacher.user'])
            ->get();
    }

    public function checkClubCapacity(string $clubId): array
    {
        $club = Club::find($clubId);
        if (!$club) {
            return ['available' => 0, 'total' => 0, 'used' => 0];
        }

        $maxMembers = $club->max_members ?? 0;
        if ($maxMembers === 0) {
            return ['available' => -1, 'total' => -1, 'used' => 0];
        }

        $currentCount = ClubMembership::where('club_id', $clubId)->count();

        return [
            'available' => $maxMembers - $currentCount,
            'total' => $maxMembers,
            'used' => $currentCount,
        ];
    }
}
