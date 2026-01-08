<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ParentPortal\ParentEngagementLog;
use App\Models\ParentPortal\ParentEventRegistration;
use App\Models\ParentPortal\ParentNotificationPreference;
use App\Models\ParentPortal\ParentVolunteerOpportunity;
use App\Models\ParentPortal\ParentVolunteerSignup;
use Ramsey\Uuid\Uuid;

class ParentEngagementService
{
    public function logEngagement(string $parentId, string $actionType, ?string $actionDetails = null, ?array $metadata = null, ?string $ipAddress = null, ?string $userAgent = null): ParentEngagementLog
    {
        return ParentEngagementLog::create([
            'id' => Uuid::uuid4()->toString(),
            'parent_id' => $parentId,
            'action_type' => $actionType,
            'action_details' => $actionDetails,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getEngagementMetrics(string $parentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ParentEngagementLog::where('parent_id', $parentId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $logs = $query->get();

        return [
            'total_actions' => $logs->count(),
            'actions_by_type' => $logs->groupBy('action_type')->map->count(),
            'last_activity' => $logs->max('created_at'),
            'engagement_score' => $this->calculateEngagementScore($logs),
        ];
    }

    public function registerForEvent(string $parentId, string $eventId, ?string $studentId = null, int $numberOfAttendees = 1, ?array $additionalInfo = null): ParentEventRegistration
    {
        $existingRegistration = ParentEventRegistration::where('parent_id', $parentId)
            ->where('event_id', $eventId)
            ->first();

        if ($existingRegistration) {
            throw new \RuntimeException('Already registered for this event');
        }

        return ParentEventRegistration::create([
            'id' => Uuid::uuid4()->toString(),
            'parent_id' => $parentId,
            'student_id' => $studentId,
            'event_id' => $eventId,
            'status' => 'registered',
            'number_of_attendees' => $numberOfAttendees,
            'additional_info' => $additionalInfo,
            'registered_at' => now(),
        ]);
    }

    public function cancelEventRegistration(string $parentId, string $eventId): bool
    {
        $registration = ParentEventRegistration::where('parent_id', $parentId)
            ->where('event_id', $eventId)
            ->first();

        if (!$registration) {
            return false;
        }

        $registration->status = 'cancelled';
        $registration->save();

        return true;
    }

    public function getParentRegistrations(string $parentId, ?string $status = null): array
    {
        $query = ParentEventRegistration::where('parent_id', $parentId)
            ->with(['event', 'student']);

        if ($status) {
            $query->where('status', $status);
        }

        $registrations = $query->orderBy('registered_at', 'desc')->get();

        return $registrations->map(function ($registration) {
            return [
                'id' => $registration->id,
                'event' => $registration->event ? [
                    'id' => $registration->event->id,
                    'title' => $registration->event->title,
                    'description' => $registration->event->description,
                    'start_date' => $registration->event->start_date,
                    'end_date' => $registration->event->end_date,
                ] : null,
                'student' => $registration->student ? [
                    'id' => $registration->student->id,
                    'name' => $registration->student->name,
                ] : null,
                'status' => $registration->status,
                'number_of_attendees' => $registration->number_of_attendees,
                'registered_at' => $registration->registered_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function getAvailableVolunteerOpportunities(?string $status = null): array
    {
        $query = ParentVolunteerOpportunity::query();

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'open');
        }

        $opportunities = $query->orderBy('created_at', 'desc')->get();

        return $opportunities->map(function ($opportunity) {
            return [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'description' => $opportunity->description,
                'event_date' => $opportunity->event_date,
                'location' => $opportunity->location,
                'slots_available' => $opportunity->slots_available,
                'slots_filled' => $opportunity->slots_filled,
                'available_slots' => $opportunity->availableSlots(),
                'status' => $opportunity->status,
                'requirements' => $opportunity->requirements,
            ];
        })->toArray();
    }

    public function signupForVolunteerOpportunity(string $parentId, string $opportunityId, ?string $notes = null): ParentVolunteerSignup
    {
        $opportunity = ParentVolunteerOpportunity::find($opportunityId);

        if (!$opportunity || $opportunity->status !== 'open') {
            throw new \RuntimeException('Volunteer opportunity not available');
        }

        if ($opportunity->availableSlots() <= 0) {
            throw new \RuntimeException('No slots available for this opportunity');
        }

        $existingSignup = ParentVolunteerSignup::where('parent_id', $parentId)
            ->where('opportunity_id', $opportunityId)
            ->first();

        if ($existingSignup) {
            throw new \RuntimeException('Already signed up for this opportunity');
        }

        $signup = ParentVolunteerSignup::create([
            'id' => Uuid::uuid4()->toString(),
            'parent_id' => $parentId,
            'opportunity_id' => $opportunityId,
            'status' => 'signed_up',
            'notes' => $notes,
            'signed_up_at' => now(),
        ]);

        $opportunity->slots_filled += 1;
        $opportunity->save();

        return $signup;
    }

    public function cancelVolunteerSignup(string $parentId, string $opportunityId): bool
    {
        $signup = ParentVolunteerSignup::where('parent_id', $parentId)
            ->where('opportunity_id', $opportunityId)
            ->first();

        if (!$signup) {
            return false;
        }

        $signup->status = 'cancelled';
        $signup->save();

        $opportunity = ParentVolunteerOpportunity::find($opportunityId);
        if ($opportunity && $opportunity->slots_filled > 0) {
            $opportunity->slots_filled -= 1;
            $opportunity->save();
        }

        return true;
    }

    public function getParentVolunteerSignups(string $parentId): array
    {
        $signups = ParentVolunteerSignup::where('parent_id', $parentId)
            ->with('opportunity')
            ->orderBy('signed_up_at', 'desc')
            ->get();

        return $signups->map(function ($signup) {
            return [
                'id' => $signup->id,
                'opportunity' => $signup->opportunity ? [
                    'id' => $signup->opportunity->id,
                    'title' => $signup->opportunity->title,
                    'description' => $signup->opportunity->description,
                    'event_date' => $signup->opportunity->event_date,
                    'location' => $signup->opportunity->location,
                ] : null,
                'status' => $signup->status,
                'notes' => $signup->notes,
                'signed_up_at' => $signup->signed_up_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function updateNotificationPreferences(string $parentId, string $notificationType, array $preferences): ParentNotificationPreference
    {
        $preference = ParentNotificationPreference::updateOrCreate(
            [
                'parent_id' => $parentId,
                'notification_type' => $notificationType,
            ],
            [
                'email_enabled' => $preferences['email_enabled'] ?? true,
                'sms_enabled' => $preferences['sms_enabled'] ?? false,
                'push_enabled' => $preferences['push_enabled'] ?? true,
                'in_app_enabled' => $preferences['in_app_enabled'] ?? true,
                'digest_mode' => $preferences['digest_mode'] ?? false,
                'digest_frequency' => $preferences['digest_frequency'] ?? 'daily',
            ]
        );

        return $preference;
    }

    public function getNotificationPreferences(string $parentId): array
    {
        $preferences = ParentNotificationPreference::where('parent_id', $parentId)->get();

        return $preferences->map(function ($preference) {
            return [
                'notification_type' => $preference->notification_type,
                'email_enabled' => $preference->email_enabled,
                'sms_enabled' => $preference->sms_enabled,
                'push_enabled' => $preference->push_enabled,
                'in_app_enabled' => $preference->in_app_enabled,
                'digest_mode' => $preference->digest_mode,
                'digest_frequency' => $preference->digest_frequency,
            ];
        })->toArray();
    }

    private function calculateEngagementScore($logs): int
    {
        if ($logs->isEmpty()) {
            return 0;
        }

        $score = 0;
        $actionWeights = [
            'view_dashboard' => 1,
            'view_grades' => 2,
            'view_attendance' => 1,
            'send_message' => 3,
            'read_message' => 1,
            'register_event' => 5,
            'schedule_conference' => 5,
            'volunteer_signup' => 5,
        ];

        foreach ($logs as $log) {
            $weight = $actionWeights[$log->action_type] ?? 1;
            $score += $weight;
        }

        return min($score, 100);
    }
}
