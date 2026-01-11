<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Api\BaseController;
use App\Services\ParentCommunicationService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ParentCommunicationController extends BaseController
{
    public function __construct(
        private readonly ParentCommunicationService $communicationService
    ) {}

    #[PostMapping('/api/parent/messages')]
    public function sendMessage(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $recipientId = $request->input('recipient_id');
            $subject = $request->input('subject');
            $message = $request->input('message');
            $attachmentUrl = $request->input('attachment_url');
            $threadId = $request->input('thread_id');

            $message = $this->communicationService->sendMessage($userId, $recipientId, $subject, $message, $attachmentUrl, $threadId);

            return $this->successResponse($message->toArray(), 'Message sent successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MESSAGE_SEND_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/messages')]
    public function getMessages(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $threadId = $request->input('thread_id');
            $unreadOnly = $request->input('unread_only', false);
            $page = (int)$request->input('page', 1);
            $perPage = (int)$request->input('per_page', 20);

            $messages = $this->communicationService->getMessages($userId, $threadId, $unreadOnly, $page, $perPage);

            return $this->successResponse($messages, 'Messages retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MESSAGE_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/messages/threads/{threadId}')]
    public function getMessageThread(RequestInterface $request)
    {
        try {
            $threadId = $request->route('threadId');
            $thread = $this->communicationService->getMessageThread($threadId);

            return $this->successResponse($thread, 'Message thread retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'THREAD_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[PutMapping('/api/parent/messages/{messageId}/read')]
    public function markMessageAsRead(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $messageId = $request->route('messageId');

            $success = $this->communicationService->markMessageAsRead($userId, $messageId);

            if (!$success) {
                return $this->notFoundResponse('Message not found');
            }

            return $this->successResponse(null, 'Message marked as read successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MARK_READ_ERROR', null, 500);
        }
    }

    #[PutMapping('/api/parent/messages/read-all')]
    public function markAllAsRead(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $count = $this->communicationService->markAllAsRead($userId);

            return $this->successResponse(['marked_count' => $count], 'All messages marked as read successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MARK_ALL_READ_ERROR', null, 500);
        }
    }

    #[PostMapping('/api/parent/conferences')]
    public function scheduleConference(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $teacherId = $request->input('teacher_id');
            $studentId = $request->input('student_id');
            $scheduledDate = $request->input('scheduled_date');
            $durationMinutes = (int)$request->input('duration_minutes', 30);
            $notes = $request->input('notes');

            $conference = $this->communicationService->scheduleConference($userId, $teacherId, $studentId, $scheduledDate, $durationMinutes, $notes);

            return $this->successResponse($conference->toArray(), 'Conference scheduled successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CONFERENCE_SCHEDULE_ERROR', null, 500);
        }
    }

    #[PutMapping('/api/parent/conferences/{conferenceId}/status')]
    public function updateConferenceStatus(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $conferenceId = $request->route('conferenceId');
            $status = $request->input('status');
            $notes = $request->input('notes');

            $conference = $this->communicationService->updateConferenceStatus($userId, $conferenceId, $status, $notes);

            if (!$conference) {
                return $this->notFoundResponse('Conference not found');
            }

            return $this->successResponse($conference->toArray(), 'Conference status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CONFERENCE_UPDATE_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/conferences')]
    public function getConferences(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $status = $request->input('status');

            $conferences = $this->communicationService->getConferences($userId, $status);

            return $this->successResponse($conferences, 'Conferences retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CONFERENCES_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/conferences/upcoming')]
    public function getUpcomingConferences(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $conferences = $this->communicationService->getUpcomingConferences($userId);

            return $this->successResponse($conferences, 'Upcoming conferences retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'UPCOMING_CONFERENCES_ERROR', null, 500);
        }
    }

    private function getAuthenticatedUserId(): string
    {
        $user = $this->request->getAttribute('user');
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }
        return $user['id'];
    }
}
