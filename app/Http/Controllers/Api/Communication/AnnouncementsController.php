<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Api\BaseController;
use App\Models\Communication\Announcement;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class AnnouncementsController extends BaseController
{
    /**
     * Get relevant announcements for the authenticated user
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            // Get announcements based on user role and target audience
            $announcements = Announcement::where('is_published', true)
                ->where(function ($query) use ($user) {
                    // Get announcements for all users
                    $query->whereNull('target_audience_id')
                          ->whereNull('target_audience_type');
                    
                    // Or get announcements for specific user roles/classes
                    // This would need to be expanded based on the specific targeting logic
                })
                ->with(['creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->successResponse($announcements, 'Announcements retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error retrieving announcements: ' . $e->getMessage());
        }
    }

    /**
     * Create a new announcement
     */
    public function store(RequestInterface $request): ResponseInterface
    {
        try {
            $user = $request->getAttribute('user');
            
            $title = $request->input('title');
            $content = $request->input('content');
            $type = $request->input('type', 'general');
            $targetAudienceId = $request->input('target_audience_id');
            $targetAudienceType = $request->input('target_audience_type');
            $publishDate = $request->input('publish_date');
            $expiryDate = $request->input('expiry_date');
            $isPinned = $request->input('is_pinned', false);

            // Validate required fields
            if (empty($title) || empty($content)) {
                return $this->validationErrorResponse([
                    'title' => ['The title field is required.'],
                    'content' => ['The content field is required.']
                ]);
            }

            $announcement = Announcement::create([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'created_by' => $user->id,
                'target_audience_id' => $targetAudienceId,
                'target_audience_type' => $targetAudienceType,
                'publish_date' => $publishDate,
                'expiry_date' => $expiryDate,
                'is_published' => true, // Auto-publish for now
                'is_pinned' => $isPinned,
            ]);

            return $this->successResponse($announcement->load(['creator']), 'Announcement created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error creating announcement: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific announcement
     */
    public function show(string $id, RequestInterface $request): ResponseInterface
    {
        try {
            $announcement = Announcement::where('id', $id)
                ->where('is_published', true)
                ->with(['creator'])
                ->first();

            if (!$announcement) {
                return $this->notFoundResponse('Announcement not found');
            }

            return $this->successResponse($announcement, 'Announcement retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error retrieving announcement: ' . $e->getMessage());
        }
    }
}