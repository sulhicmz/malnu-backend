<?php

declare(strict_types=1);

namespace App\Services;

use App\Notifications\MobileNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Send a notification to specific users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        $users = User::whereIn('id', $userIds)->get();
        
        foreach ($users as $user) {
            $notification = new MobileNotification($title, $body, $data, $userIds);
            $user->notify($notification);
        }
    }

    /**
     * Send a notification to users by role
     */
    public function sendToRole(string $role, string $title, string $body, array $data = []): void
    {
        $users = $this->getUsersByRole($role);
        
        foreach ($users as $user) {
            $notification = new MobileNotification($title, $body, $data, $users->pluck('id')->toArray());
            $user->notify($notification);
        }
    }

    /**
     * Send a notification to a specific user
     */
    public function sendToUser(string $userId, string $title, string $body, array $data = []): void
    {
        $user = User::find($userId);
        if ($user) {
            $notification = new MobileNotification($title, $body, $data, [$userId]);
            $user->notify($notification);
        }
    }

    /**
     * Get users by role
     */
    private function getUsersByRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        switch ($role) {
            case 'student':
                return User::whereHas('student')->get();
            case 'parent':
                return User::whereHas('parent')->get();
            case 'teacher':
                return User::whereHas('teacher')->get();
            case 'admin':
                return User::whereHas('staff')->get();
            default:
                return collect();
        }
    }

    /**
     * Send grade notification to student and parent
     */
    public function sendGradeNotification(string $studentId, string $subject, float $grade, string $assignment): void
    {
        // Get the student
        $studentUser = User::whereHas('student', function($query) use ($studentId) {
            $query->where('id', $studentId);
        })->first();

        if (!$studentUser) {
            return;
        }

        // Send notification to student
        $this->sendToUser(
            $studentUser->id,
            'New Grade Posted',
            "You received a {$grade} in {$subject} for {$assignment}",
            [
                'type' => 'grade',
                'student_id' => $studentId,
                'subject' => $subject,
                'grade' => $grade,
                'assignment' => $assignment,
            ]
        );

        // Find parent of the student and notify them too
        $parentUser = User::whereHas('parent.students', function($query) use ($studentId) {
            $query->where('id', $studentId);
        })->first();

        if ($parentUser) {
            $this->sendToUser(
                $parentUser->id,
                'Your Child\'s Grade Updated',
                "Your child received a {$grade} in {$subject} for {$assignment}",
                [
                    'type' => 'grade',
                    'student_id' => $studentId,
                    'subject' => $subject,
                    'grade' => $grade,
                    'assignment' => $assignment,
                ]
            );
        }
    }

    /**
     * Send assignment notification
     */
    public function sendAssignmentNotification(string $classId, string $title, string $description): void
    {
        // Get all students in the class
        $studentIds = \App\Models\SchoolManagement\Student::where('class_id', $classId)
            ->pluck('user_id')
            ->toArray();

        if (!empty($studentIds)) {
            $this->sendToUsers(
                $studentIds,
                'New Assignment',
                "A new assignment '{$title}' has been posted",
                [
                    'type' => 'assignment',
                    'class_id' => $classId,
                    'title' => $title,
                    'description' => $description,
                ]
            );
        }
    }
}