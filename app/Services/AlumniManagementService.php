<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AlumniManagement\AlumniProfile;
use App\Models\AlumniManagement\AlumniCareer;
use App\Models\AlumniManagement\AlumniAchievement;
use App\Models\AlumniManagement\AlumniMentorship;
use App\Models\AlumniManagement\AlumniDonation;
use App\Models\AlumniManagement\AlumniEvent;
use App\Models\AlumniManagement\AlumniEventRegistration;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use Carbon\Carbon;
use Exception;

class AlumniManagementService
{
    /**
     * Create alumni profile
     */
    public function createProfile(array $data): AlumniProfile
    {
        return AlumniProfile::create($data);
    }

    /**
     * Get alumni profile by ID
     */
    public function getProfile(string $id): ?AlumniProfile
    {
        return AlumniProfile::with(['careers', 'achievements', 'mentorshipsAsMentor', 'donations'])
            ->find($id);
    }

    /**
     * Update alumni profile
     */
    public function updateProfile(string $id, array $data): bool
    {
        $profile = AlumniProfile::find($id);
        if (!$profile) {
            return false;
        }
        return $profile->update($data);
    }

    /**
     * Delete alumni profile
     */
    public function deleteProfile(string $id): bool
    {
        $profile = AlumniProfile::find($id);
        if (!$profile) {
            return false;
        }
        return $profile->delete();
    }

    /**
     * Get alumni directory with filters
     */
    public function getAlumniDirectory(array $filters = []): array
    {
        $query = AlumniProfile::query();

        if (isset($filters['public_only'])) {
            $query->where('public_profile', true);
        }

        if (isset($filters['allow_contact'])) {
            $query->where('allow_contact', true);
        }

        if (isset($filters['graduation_year'])) {
            $query->where('graduation_year', $filters['graduation_year']);
        }

        if (isset($filters['field_of_study'])) {
            $query->where('field_of_study', 'like', "%{$filters['field_of_study']}%");
        }

        return $query->with(['careers', 'achievements'])
            ->orderBy('graduation_year', 'desc')
            ->paginate(20)
            ->toArray();
    }

    /**
     * Add career to alumni profile
     */
    public function addCareer(string $alumniId, array $data): AlumniCareer
    {
        return AlumniCareer::create(array_merge($data, ['alumni_id' => $alumniId]));
    }

    /**
     * Update career
     */
    public function updateCareer(string $id, array $data): bool
    {
        $career = AlumniCareer::find($id);
        if (!$career) {
            return false;
        }
        return $career->update($data);
    }

    /**
     * Delete career
     */
    public function deleteCareer(string $id): bool
    {
        $career = AlumniCareer::find($id);
        if (!$career) {
            return false;
        }
        return $career->delete();
    }

    /**
     * Add achievement
     */
    public function addAchievement(string $alumniId, array $data): AlumniAchievement
    {
        return AlumniAchievement::create(array_merge($data, ['alumni_id' => $alumniId]));
    }

    /**
     * Update achievement
     */
    public function updateAchievement(string $id, array $data): bool
    {
        $achievement = AlumniAchievement::find($id);
        if (!$achievement) {
            return false;
        }
        return $achievement->update($data);
    }

    /**
     * Delete achievement
     */
    public function deleteAchievement(string $id): bool
    {
        $achievement = AlumniAchievement::find($id);
        if (!$achievement) {
            return false;
        }
        return $achievement->delete();
    }

    /**
     * Create mentorship match
     */
    public function createMentorship(array $data): AlumniMentorship
    {
        return AlumniMentorship::create($data);
    }

    /**
     * Update mentorship status
     */
    public function updateMentorship(string $id, array $data): bool
    {
        $mentorship = AlumniMentorship::find($id);
        if (!$mentorship) {
            return false;
        }
        return $mentorship->update($data);
    }

    /**
     * Get alumni's mentorships
     */
    public function getMentorships(string $alumniId): array
    {
        return AlumniMentorship::where('alumni_id', $alumniId)
            ->with('student')
            ->get()
            ->toArray();
    }

    /**
     * Get student's mentorships
     */
    public function getStudentMentorships(string $studentId): array
    {
        return AlumniMentorship::where('student_id', $studentId)
            ->with('alumni')
            ->where('status', 'active')
            ->get()
            ->toArray();
    }

    /**
     * Record donation
     */
    public function recordDonation(string $alumniId, array $data): AlumniDonation
    {
        return AlumniDonation::create(array_merge($data, ['alumni_id' => $alumniId]));
    }

    /**
     * Get alumni donations
     */
    public function getDonations(string $alumniId): array
    {
        return AlumniDonation::where('alumni_id', $alumniId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Create alumni event
     */
    public function createEvent(array $data): AlumniEvent
    {
        return AlumniEvent::create($data);
    }

    /**
     * Update event
     */
    public function updateEvent(string $id, array $data): bool
    {
        $event = AlumniEvent::find($id);
        if (!$event) {
            return false;
        }
        return $event->update($data);
    }

    /**
     * Delete event
     */
    public function deleteEvent(string $id): bool
    {
        $event = AlumniEvent::find($id);
        if (!$event) {
            return false;
        }
        return $event->delete();
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents(): array
    {
        return AlumniEvent::upcoming()
            ->with('registrations')
            ->orderBy('event_date', 'asc')
            ->paginate(10)
            ->toArray();
    }

    /**
     * Register alumni for event
     */
    public function registerForEvent(string $eventId, string $alumniId, array $data = []): AlumniEventRegistration
    {
        return AlumniEventRegistration::create(array_merge($data, [
            'event_id' => $eventId,
            'alumni_id' => $alumniId,
            'registration_time' => Carbon::now(),
            'attendance_status' => 'registered'
        ]));
    }

    /**
     * Update registration
     */
    public function updateRegistration(string $id, array $data): bool
    {
        $registration = AlumniEventRegistration::find($id);
        if (!$registration) {
            return false;
        }
        return $registration->update($data);
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(string $id): bool
    {
        $registration = AlumniEventRegistration::find($id);
        if (!$registration) {
            return false;
        }
        return $registration->update(['attendance_status' => 'cancelled']);
    }

    /**
     * Get event registrations
     */
    public function getEventRegistrations(string $eventId): array
    {
        return AlumniEventRegistration::where('event_id', $eventId)
            ->with('alumni')
            ->get()
            ->toArray();
    }

    /**
     * Get alumni event registrations
     */
    public function getAlumniRegistrations(string $alumniId): array
    {
        return AlumniEventRegistration::where('alumni_id', $alumniId)
            ->with('event')
            ->get()
            ->toArray();
    }

    /**
     * Get alumni statistics
     */
    public function getAlumniStatistics(): array
    {
        $totalAlumni = AlumniProfile::count();
        $publicProfiles = AlumniProfile::where('public_profile', true)->count();
        $activeMentorships = AlumniMentorship::whereIn('status', ['active', 'completed'])->count();
        $totalDonations = AlumniDonation::count();
        $upcomingEvents = AlumniEvent::upcoming()->count();

        return [
            'total_alumni' => $totalAlumni,
            'public_profiles' => $publicProfiles,
            'active_mentorships' => $activeMentorships,
            'total_donations' => $totalDonations,
            'upcoming_events' => $upcomingEvents,
        ];
    }
}
