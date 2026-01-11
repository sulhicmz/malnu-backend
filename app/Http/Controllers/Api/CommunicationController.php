<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Communication\Announcement;
use App\Models\Communication\AnnouncementReadStatus;
use App\Models\Communication\Message;
use App\Models\Communication\MessageParticipant;
use App\Models\Communication\MessageTemplate;
use App\Models\Communication\MessageThread;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use function Hyperf\Support\make;

class CommunicationController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    private function getCurrentUserId(): ?string
    {
        $user = $this->request->getAttribute('user');
        return $user ? $user->id : null;
    }

    public function getMessages()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 20);

            $threads = MessageThread::with(['lastMessage', 'participants'])
                ->whereHas('participants', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('has_left', false);
                })
                ->orderBy('updated_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($threads, 'Messages retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function sendMessage()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $data = $this->request->all();
            $errors = [];

            if (empty($data['content'])) {
                $errors['content'] = ['Content is required'];
            }

            if (empty($data['recipient_ids']) || !is_array($data['recipient_ids'])) {
                $errors['recipient_ids'] = ['Recipient IDs are required and must be an array'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $subject = $data['subject'] ?? null;
            $threadId = $data['thread_id'] ?? null;

            Db::beginTransaction();

            if ($threadId) {
                $thread = MessageThread::findOrFail($threadId);

                $isParticipant = MessageParticipant::where('thread_id', $threadId)
                    ->where('user_id', $userId)
                    ->where('has_left', false)
                    ->exists();

                if (!$isParticipant) {
                    Db::rollBack();
                    return $this->forbiddenResponse('You are not a participant in this conversation');
                }
            } else {
                $thread = MessageThread::create([
                    'subject' => $subject,
                    'type' => count($data['recipient_ids']) > 1 ? 'group' : 'direct',
                    'created_by' => $userId,
                ]);

                MessageParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => $userId,
                    'is_admin' => true,
                ]);

                foreach ($data['recipient_ids'] as $recipientId) {
                    MessageParticipant::create([
                        'thread_id' => $thread->id,
                        'user_id' => $recipientId,
                        'is_admin' => false,
                    ]);
                }
            }

            $message = Message::create([
                'thread_id' => $thread->id,
                'sender_id' => $userId,
                'content' => $data['content'],
                'attachment_url' => $data['attachment_url'] ?? null,
                'attachment_type' => $data['attachment_type'] ?? null,
            ]);

            Db::commit();

            return $this->successResponse($message->load('thread'), 'Message sent successfully');
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getThread(string $id)
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 50);

            $thread = MessageThread::with(['participants.user'])
                ->with(['messages' => function ($query) use ($page, $limit) {
                    $query->orderBy('created_at', 'desc')
                          ->paginate($limit, ['*'], 'page', $page);
                }])
                ->findOrFail($id);

            $isParticipant = MessageParticipant::where('thread_id', $id)
                ->where('user_id', $userId)
                ->where('has_left', false)
                ->exists();

            if (!$isParticipant) {
                return $this->forbiddenResponse('You are not a participant in this conversation');
            }

            Message::where('thread_id', $id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => date('Y-m-d H:i:s')]);

            return $this->successResponse($thread, 'Thread retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createThread()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $data = $this->request->all();
            $errors = [];

            if (empty($data['recipient_ids']) || !is_array($data['recipient_ids'])) {
                $errors['recipient_ids'] = ['Recipient IDs are required and must be an array'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            Db::beginTransaction();

            $thread = MessageThread::create([
                'subject' => $data['subject'] ?? null,
                'type' => $data['type'] ?? 'direct',
                'created_by' => $userId,
            ]);

            MessageParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $userId,
                'is_admin' => true,
            ]);

            foreach ($data['recipient_ids'] as $recipientId) {
                MessageParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => $recipientId,
                    'is_admin' => false,
                ]);
            }

            if (!empty($data['content'])) {
                Message::create([
                    'thread_id' => $thread->id,
                    'sender_id' => $userId,
                    'content' => $data['content'],
                ]);
            }

            Db::commit();

            return $this->successResponse($thread->load('participants'), 'Thread created successfully');
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAnnouncements()
    {
        try {
            $userId = $this->getCurrentUserId();

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);
            $type = $this->request->query('type');
            $unreadOnly = $this->request->query('unread_only', 'false') === 'true';

            $query = Announcement::with('creator')
                ->where('is_active', true)
                ->where('published_at', '<=', date('Y-m-d H:i:s'));

            if ($type) {
                $query->where('type', $type);
            }

            $query->where(function ($q) {
                $q->where('target_type', 'all')
                  ->orWhere('expires_at', '>', date('Y-m-d H:i:s'))
                  ->orWhereNull('expires_at');
            });

            if ($unreadOnly && $userId) {
                $readAnnouncementIds = AnnouncementReadStatus::where('user_id', $userId)
                    ->pluck('announcement_id')
                    ->toArray();
                $query->whereNotIn('id', $readAnnouncementIds);
            }

            $announcements = $query->orderBy('published_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            if ($userId) {
                foreach ($announcements->items() as $announcement) {
                    $isTargeted = $announcement->isTargetedForUser(User::find($userId));
                    $announcement->is_targeted = $isTargeted;

                    $readStatus = AnnouncementReadStatus::where('announcement_id', $announcement->id)
                        ->where('user_id', $userId)
                        ->first();
                    $announcement->is_read_by_user = (bool) $readStatus;
                }
            }

            return $this->successResponse($announcements, 'Announcements retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createAnnouncement()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $data = $this->request->all();
            $errors = [];

            if (empty($data['title'])) {
                $errors['title'] = ['Title is required'];
            }

            if (empty($data['content'])) {
                $errors['content'] = ['Content is required'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $announcement = Announcement::create([
                'created_by' => $userId,
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'] ?? 'general',
                'target_type' => $data['target_type'] ?? 'all',
                'target_roles' => $data['target_roles'] ?? null,
                'target_classes' => $data['target_classes'] ?? null,
                'target_users' => $data['target_users'] ?? null,
                'published_at' => !empty($data['published_at']) ? $data['published_at'] : date('Y-m-d H:i:s'),
                'expires_at' => $data['expires_at'] ?? null,
                'attachment_url' => $data['attachment_url'] ?? null,
            ]);

            return $this->successResponse($announcement->load('creator'), 'Announcement created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateAnnouncement(string $id)
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $announcement = Announcement::findOrFail($id);

            if ($announcement->created_by !== $userId) {
                return $this->forbiddenResponse('You can only update your own announcements');
            }

            $data = $this->request->all();
            $errors = [];

            if (empty($data['title'])) {
                $errors['title'] = ['Title is required'];
            }

            if (empty($data['content'])) {
                $errors['content'] = ['Content is required'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $announcement->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'] ?? $announcement->type,
                'target_type' => $data['target_type'] ?? $announcement->target_type,
                'target_roles' => $data['target_roles'] ?? $announcement->target_roles,
                'target_classes' => $data['target_classes'] ?? $announcement->target_classes,
                'target_users' => $data['target_users'] ?? $announcement->target_users,
                'published_at' => $data['published_at'] ?? $announcement->published_at,
                'expires_at' => $data['expires_at'] ?? $announcement->expires_at,
                'attachment_url' => $data['attachment_url'] ?? $announcement->attachment_url,
            ]);

            return $this->successResponse($announcement->load('creator'), 'Announcement updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function deleteAnnouncement(string $id)
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $announcement = Announcement::findOrFail($id);

            if ($announcement->created_by !== $userId) {
                return $this->forbiddenResponse('You can only delete your own announcements');
            }

            $announcement->delete();

            return $this->successResponse(null, 'Announcement deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAnnouncementRead(string $id)
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $announcement = Announcement::findOrFail($id);

            $existingReadStatus = AnnouncementReadStatus::where('announcement_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$existingReadStatus) {
                AnnouncementReadStatus::create([
                    'announcement_id' => $id,
                    'user_id' => $userId,
                    'read_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $existingReadStatus->update(['read_at' => date('Y-m-d H:i:s')]);
            }

            return $this->successResponse(null, 'Announcement marked as read');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getTemplates()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);
            $category = $this->request->query('category');

            $query = MessageTemplate::where('is_active', true)
                ->where('created_by', $userId);

            if ($category) {
                $query->where('category', $category);
            }

            $templates = $query->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($templates, 'Templates retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createTemplate()
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->unauthorizedResponse();
            }

            $data = $this->request->all();
            $errors = [];

            if (empty($data['name'])) {
                $errors['name'] = ['Name is required'];
            }

            if (empty($data['content'])) {
                $errors['content'] = ['Content is required'];
            }

            if (empty($data['category'])) {
                $errors['category'] = ['Category is required'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $template = MessageTemplate::create([
                'created_by' => $userId,
                'name' => $data['name'],
                'category' => $data['category'],
                'subject' => $data['subject'] ?? null,
                'content' => $data['content'],
                'variables' => $data['variables'] ?? null,
            ]);

            return $this->successResponse($template->load('creator'), 'Template created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
