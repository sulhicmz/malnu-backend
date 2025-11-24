<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Api\BaseController;
use App\Models\Communication\Message;
use App\Models\Communication\MessageThread;
use App\Models\Communication\ThreadParticipant;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class MessagesController extends BaseController
{
    /**
     * Get user messages
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            // Get messages where user is a participant in the thread
            $messages = Message::whereHas('thread.participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['sender', 'thread', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            return $this->successResponse($messages, 'Messages retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error retrieving messages: ' . $e->getMessage());
        }
    }

    /**
     * Send a new message
     */
    public function store(RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            $content = $request->input('content');
            $threadId = $request->input('thread_id');
            $recipientId = $request->input('recipient_id');
            $messageType = $request->input('message_type', 'text');
            $fileUrl = $request->input('file_url');

            // Validate required fields
            if (empty($threadId) || empty($content)) {
                return $this->validationErrorResponse([
                    'thread_id' => ['The thread_id field is required.'],
                    'content' => ['The content field is required.']
                ]);
            }

            // Check if user is a participant in the thread
            $isParticipant = ThreadParticipant::where('thread_id', $threadId)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isParticipant) {
                return $this->forbiddenResponse('You are not a participant in this thread');
            }

            $message = Message::create([
                'thread_id' => $threadId,
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'content' => $content,
                'message_type' => $messageType,
                'file_url' => $fileUrl,
            ]);

            return $this->successResponse($message->load(['sender', 'thread']), 'Message sent successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error sending message: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific message
     */
    public function show(string $id, RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            $message = Message::where('id', $id)
                ->whereHas('thread.participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['sender', 'thread', 'category'])
                ->first();

            if (!$message) {
                return $this->notFoundResponse('Message not found');
            }

            return $this->successResponse($message, 'Message retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error retrieving message: ' . $e->getMessage());
        }
    }

    /**
     * Get conversation thread
     */
    public function getThread(string $threadId, RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            // Check if user is a participant in the thread
            $isParticipant = ThreadParticipant::where('thread_id', $threadId)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isParticipant) {
                return $this->forbiddenResponse('You are not a participant in this thread');
            }

            $messages = Message::where('thread_id', $threadId)
                ->with(['sender', 'category'])
                ->orderBy('created_at', 'asc')
                ->paginate(20);

            return $this->successResponse($messages, 'Thread messages retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error retrieving thread: ' . $e->getMessage());
        }
    }
}