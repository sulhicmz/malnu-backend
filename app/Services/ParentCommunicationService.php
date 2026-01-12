<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ParentPortal\ParentConference;
use App\Models\ParentPortal\ParentMessage;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;

class ParentCommunicationService
{
    public function sendMessage(string $senderId, string $recipientId, string $subject, string $message, ?string $attachmentUrl = null, ?string $threadId = null): ParentMessage
    {
        if (!$threadId) {
            $threadId = Uuid::uuid4()->toString();
        }

        $parentMessage = ParentMessage::create([
            'id' => Uuid::uuid4()->toString(),
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'message' => $message,
            'type' => 'individual',
            'thread_id' => $threadId,
            'is_read' => false,
            'attachment_url' => $attachmentUrl,
        ]);

        return $parentMessage;
    }

    public function sendAnnouncement(string $senderId, string $subject, string $message, array $recipientIds, ?string $attachmentUrl = null): array
    {
        $threadId = Uuid::uuid4()->toString();
        $messages = [];

        foreach ($recipientIds as $recipientId) {
            $messages[] = ParentMessage::create([
                'id' => Uuid::uuid4()->toString(),
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'subject' => $subject,
                'message' => $message,
                'type' => 'announcement',
                'thread_id' => $threadId,
                'is_read' => false,
                'attachment_url' => $attachmentUrl,
            ]);
        }

        return $messages;
    }

    public function getMessages(string $userId, ?string $threadId = null, ?bool $unreadOnly = false, int $page = 1, int $perPage = 20): array
    {
        $query = ParentMessage::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('recipient_id', $userId);
        })->with(['sender', 'recipient']);

        if ($threadId) {
            $query->where('thread_id', $threadId);
        }

        if ($unreadOnly) {
            $query->where('recipient_id', $userId)->where('is_read', false);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                    ],
                    'recipient' => [
                        'id' => $message->recipient->id,
                        'name' => $message->recipient->name,
                    ],
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'type' => $message->type,
                    'thread_id' => $message->thread_id,
                    'is_read' => $message->is_read,
                    'read_at' => $message->read_at,
                    'attachment_url' => $message->attachment_url,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $query->count(),
            ],
        ];
    }

    public function markMessageAsRead(string $userId, string $messageId): bool
    {
        $message = ParentMessage::where('id', $messageId)
            ->where('recipient_id', $userId)
            ->first();

        if (!$message) {
            return false;
        }

        $message->is_read = true;
        $message->read_at = now();
        $message->save();

        return true;
    }

    public function markAllAsRead(string $userId): int
    {
        return ParentMessage::where('recipient_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function getMessageThread(string $threadId): array
    {
        $messages = ParentMessage::where('thread_id', $threadId)
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                ],
                'recipient' => [
                    'id' => $message->recipient->id,
                    'name' => $message->recipient->name,
                ],
                'subject' => $message->subject,
                'message' => $message->message,
                'type' => $message->type,
                'is_read' => $message->is_read,
                'read_at' => $message->read_at,
                'attachment_url' => $message->attachment_url,
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function scheduleConference(string $parentId, string $teacherId, string $studentId, string $scheduledDate, int $durationMinutes = 30, ?string $notes = null): ParentConference
    {
        $conference = ParentConference::create([
            'id' => Uuid::uuid4()->toString(),
            'parent_id' => $parentId,
            'teacher_id' => $teacherId,
            'student_id' => $studentId,
            'scheduled_date' => $scheduledDate,
            'duration_minutes' => $durationMinutes,
            'status' => 'scheduled',
            'notes' => $notes,
        ]);

        return $conference;
    }

    public function updateConferenceStatus(string $userId, string $conferenceId, string $status, ?string $notes = null): ?ParentConference
    {
        $conference = ParentConference::where('id', $conferenceId)
            ->where(function ($q) use ($userId) {
                $q->where('parent_id', $userId)
                  ->orWhere('teacher_id', $userId);
            })
            ->first();

        if (!$conference) {
            return null;
        }

        $conference->status = $status;

        if ($userId === $conference->parent_id && $notes) {
            $conference->parent_notes = $notes;
        } elseif ($userId === $conference->teacher_id && $notes) {
            $conference->teacher_notes = $notes;
        }

        $conference->save();

        return $conference;
    }

    public function getConferences(string $userId, ?string $status = null): array
    {
        $query = ParentConference::where(function ($q) use ($userId) {
            $q->where('parent_id', $userId)
              ->orWhere('teacher_id', $userId);
        })->with(['parent', 'teacher', 'student']);

        if ($status) {
            $query->where('status', $status);
        }

        $conferences = $query->orderBy('scheduled_date', 'asc')->get();

        return $conferences->map(function ($conference) {
            return [
                'id' => $conference->id,
                'parent' => [
                    'id' => $conference->parent->id,
                    'name' => $conference->parent->name,
                ],
                'teacher' => [
                    'id' => $conference->teacher->id,
                    'name' => $conference->teacher->name,
                ],
                'student' => [
                    'id' => $conference->student->id,
                    'name' => $conference->student->name,
                ],
                'scheduled_date' => $conference->scheduled_date->format('Y-m-d H:i:s'),
                'duration_minutes' => $conference->duration_minutes,
                'status' => $conference->status,
                'notes' => $conference->notes,
                'teacher_notes' => $conference->teacher_notes,
                'parent_notes' => $conference->parent_notes,
            ];
        })->toArray();
    }

    public function getUpcomingConferences(string $userId): array
    {
        return $this->getConferences($userId, 'scheduled');
    }
}
