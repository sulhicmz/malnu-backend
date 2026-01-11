<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Alumni\Alumni;
use App\Models\Alumni\AlumniCareer;
use App\Models\Alumni\AlumniDonation;
use App\Models\Alumni\AlumniEvent;
use App\Models\Alumni\AlumniEventRegistration;
use App\Models\Alumni\AlumniEngagement;
use App\Models\Alumni\AlumniMentorship;
use Exception;

class AlumniManagementService
{
    public function createAlumni(array $data): Alumni
    {
        return Alumni::create($data);
    }

    public function getAlumni(string $id): ?Alumni
    {
        return Alumni::find($id);
    }

    public function getAlumniByStudent(string $studentId): ?Alumni
    {
        return Alumni::where('student_id', $studentId)->first();
    }

    public function updateAlumni(string $id, array $data): bool
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return false;
        }

        return $alumni->update($data);
    }

    public function deleteAlumni(string $id): bool
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return false;
        }

        return $alumni->delete();
    }

    public function getAllAlumni(array $filters = [])
    {
        $query = Alumni::with(['student', 'user']);

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }

        if (isset($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (isset($filters['graduation_year'])) {
            $query->where('graduation_year', $filters['graduation_year']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('current_company', 'like', "%{$search}%")
                ->orWhere('current_position', 'like', "%{$search}%")
                ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('graduation_year', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function createCareer(array $data): AlumniCareer
    {
        if ($data['is_current'] ?? false) {
            AlumniCareer::where('alumni_id', $data['alumni_id'])
                        ->where('is_current', true)
                        ->update(['is_current' => false]);
        }

        return AlumniCareer::create($data);
    }

    public function updateCareer(string $id, array $data): bool
    {
        $career = AlumniCareer::find($id);
        if (!$career) {
            return false;
        }

        return $career->update($data);
    }

    public function deleteCareer(string $id): bool
    {
        $career = AlumniCareer::find($id);
        if (!$career) {
            return false;
        }

        return $career->delete();
    }

    public function createDonation(array $data): AlumniDonation
    {
        return AlumniDonation::create($data);
    }

    public function updateDonation(string $id, array $data): bool
    {
        $donation = AlumniDonation::find($id);
        if (!$donation) {
            return false;
        }

        return $donation->update($data);
    }

    public function getDonations(array $filters = [])
    {
        $query = AlumniDonation::with(['alumni']);

        if (isset($filters['alumni_id'])) {
            $query->where('alumni_id', $filters['alumni_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['campaign'])) {
            $query->where('campaign', $filters['campaign']);
        }

        if (isset($filters['donation_type'])) {
            $query->where('donation_type', $filters['donation_type']);
        }

        return $query->orderBy('donation_date', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function createEvent(array $data): AlumniEvent
    {
        return AlumniEvent::create($data);
    }

    public function updateEvent(string $id, array $data): bool
    {
        $event = AlumniEvent::find($id);
        if (!$event) {
            return false;
        }

        return $event->update($data);
    }

    public function deleteEvent(string $id): bool
    {
        $event = AlumniEvent::find($id);
        if (!$event) {
            return false;
        }

        return $event->delete();
    }

    public function getEvents(array $filters = [])
    {
        $query = AlumniEvent::with(['registrations']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['upcoming']) && $filters['upcoming']) {
            $query->upcoming();
        }

        if (isset($filters['past']) && $filters['past']) {
            $query->past();
        }

        return $query->orderBy('event_date', $filters['order'] ?? 'asc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function registerForEvent(array $data): AlumniEventRegistration
    {
        $event = AlumniEvent::find($data['event_id']);
        if (!$event) {
            throw new Exception('Event not found');
        }

        if ($event->isFullyBooked()) {
            throw new Exception('Event is fully booked');
        }

        if (isset($data['alumni_id'])) {
            $existing = AlumniEventRegistration::where('event_id', $data['event_id'])
                                               ->where('alumni_id', $data['alumni_id'])
                                               ->first();
            if ($existing) {
                throw new Exception('Already registered for this event');
            }
        }

        $data['registration_date'] = now();

        $registration = AlumniEventRegistration::create($data);

        $event->increment('current_attendees', $registration->total_attendees);

        return $registration;
    }

    public function cancelEventRegistration(string $id): bool
    {
        $registration = AlumniEventRegistration::find($id);
        if (!$registration) {
            return false;
        }

        $event = AlumniEvent::find($registration->event_id);
        if ($event) {
            $event->decrement('current_attendees', $registration->total_attendees);
        }

        return $registration->delete();
    }

    public function checkInAttendee(string $id): bool
    {
        $registration = AlumniEventRegistration::find($id);
        if (!$registration) {
            return false;
        }

        $registration->markAsCheckedIn();

        return true;
    }

    public function createMentorship(array $data): AlumniMentorship
    {
        return AlumniMentorship::create($data);
    }

    public function updateMentorship(string $id, array $data): bool
    {
        $mentorship = AlumniMentorship::find($id);
        if (!$mentorship) {
            return false;
        }

        return $mentorship->update($data);
    }

    public function activateMentorship(string $id): bool
    {
        $mentorship = AlumniMentorship::find($id);
        if (!$mentorship) {
            return false;
        }

        $mentorship->activate();

        return true;
    }

    public function completeMentorship(string $id): bool
    {
        $mentorship = AlumniMentorship::find($id);
        if (!$mentorship) {
            return false;
        }

        $mentorship->complete();

        return true;
    }

    public function getMentorships(array $filters = [])
    {
        $query = AlumniMentorship::with(['mentor', 'mentor.student', 'mentor.user']);

        if (isset($filters['mentor_id'])) {
            $query->where('mentor_id', $filters['mentor_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['focus_area'])) {
            $query->where('focus_area', $filters['focus_area']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function findAvailableMentors(array $criteria = [])
    {
        $query = Alumni::availableForMentorship()
                        ->verified()
                        ->public();

        if (isset($criteria['industry'])) {
            $query->where('industry', $criteria['industry']);
        }

        if (isset($criteria['field_of_study'])) {
            $query->where('field_of_study', $criteria['field_of_study']);
        }

        return $query->with(['user', 'student'])
                    ->get();
    }

    public function createEngagement(array $data): AlumniEngagement
    {
        return AlumniEngagement::create($data);
    }

    public function updateEngagement(string $id, array $data): bool
    {
        $engagement = AlumniEngagement::find($id);
        if (!$engagement) {
            return false;
        }

        return $engagement->update($data);
    }

    public function getEngagements(array $filters = [])
    {
        $query = AlumniEngagement::with(['alumni']);

        if (isset($filters['alumni_id'])) {
            $query->where('alumni_id', $filters['alumni_id']);
        }

        if (isset($filters['engagement_type'])) {
            $query->where('engagement_type', $filters['engagement_type']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('engagement_date', $filters['year']);
        }

        return $query->orderBy('engagement_date', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function getEngagementReport(array $filters = [])
    {
        $query = AlumniEngagement::query();

        if (isset($filters['start_date'])) {
            $query->where('engagement_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('engagement_date', '<=', $filters['end_date']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('engagement_date', $filters['year']);
        }

        $engagements = $query->get();

        return [
            'total_engagements' => $engagements->count(),
            'by_type' => $engagements->groupBy('engagement_type')
                                     ->map(fn($group) => $group->count()),
            'by_category' => $engagements->groupBy('category')
                                       ->map(fn($group) => $group->count()),
            'by_month' => $engagements->groupBy(function ($item) {
                return $item->engagement_date->format('Y-m');
            })->map(fn($group) => $group->count()),
        ];
    }

    public function getDonationReport(array $filters = [])
    {
        $query = AlumniDonation::completed();

        if (isset($filters['start_date'])) {
            $query->where('donation_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('donation_date', '<=', $filters['end_date']);
        }

        if (isset($filters['campaign'])) {
            $query->where('campaign', $filters['campaign']);
        }

        $donations = $query->get();

        return [
            'total_donations' => $donations->count(),
            'total_amount' => $donations->sum('amount'),
            'average_donation' => $donations->avg('amount'),
            'by_campaign' => $donations->groupBy('campaign')
                                    ->map(function ($group) {
                                        return [
                                            'count' => $group->count(),
                                            'total' => $group->sum('amount'),
                                        ];
                                    }),
            'by_type' => $donations->groupBy('donation_type')
                                  ->map(function ($group) {
                                      return [
                                          'count' => $group->count(),
                                          'total' => $group->sum('amount'),
                                      ];
                                  }),
            'recurring_count' => $donations->where('is_recurring', true)->count(),
        ];
    }

    public function verifyAlumni(string $id): bool
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return false;
        }

        return $alumni->update(['is_verified' => true]);
    }

    public function getAlumniDirectory(array $filters = [])
    {
        return $this->getAllAlumni(array_merge($filters, ['is_public' => true]));
    }

    public function updatePrivacySettings(string $id, array $settings): bool
    {
        $alumni = Alumni::find($id);
        if (!$alumni) {
            return false;
        }

        return $alumni->update([
            'is_public' => $settings['is_public'] ?? $alumni->is_public,
            'allow_contact' => $settings['allow_contact'] ?? $alumni->allow_contact,
            'newsletter_subscription' => $settings['newsletter_subscription'] ?? $alumni->newsletter_subscription,
            'mentor_availability' => $settings['mentor_availability'] ?? $alumni->mentor_availability,
            'privacy_settings' => $settings['privacy_settings'] ?? $alumni->privacy_settings,
        ]);
    }
}
