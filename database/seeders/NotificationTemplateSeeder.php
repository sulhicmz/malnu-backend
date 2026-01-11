<?php

declare(strict_types=1);

use App\Models\Notification\NotificationTemplate;
use Hyperf\Database\Seeder\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Attendance Alert',
                'slug' => 'attendance_alert',
                'type' => 'attendance',
                'subject' => 'Attendance Update',
                'body' => 'Dear {parent_name}, your child {student_name} was marked {attendance_status} on {date}.',
                'variables' => ['parent_name', 'student_name', 'attendance_status', 'date'],
                'is_active' => true,
            ],
            [
                'name' => 'Grade Posted',
                'slug' => 'grade_posted',
                'type' => 'grade',
                'subject' => 'New Grade Posted',
                'body' => 'Dear {student_name}, a new grade has been posted for {subject}. Grade: {grade}.',
                'variables' => ['student_name', 'subject', 'grade'],
                'is_active' => true,
            ],
            [
                'name' => 'Event Reminder',
                'slug' => 'event_reminder',
                'type' => 'event',
                'subject' => 'Upcoming Event Reminder',
                'body' => 'Reminder: {event_name} is scheduled for {event_date} at {event_location}.',
                'variables' => ['event_name', 'event_date', 'event_location'],
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Alert',
                'slug' => 'emergency_alert',
                'type' => 'emergency',
                'subject' => 'EMERGENCY ALERT',
                'body' => 'IMPORTANT: {emergency_message}. Please take immediate action. Location: {location}. Time: {time}.',
                'variables' => ['emergency_message', 'location', 'time'],
                'is_active' => true,
            ],
            [
                'name' => 'Assignment Due',
                'slug' => 'assignment_due',
                'type' => 'assignment',
                'subject' => 'Assignment Due Reminder',
                'body' => 'Reminder: Assignment {assignment_name} is due on {due_date}.',
                'variables' => ['assignment_name', 'due_date'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::firstOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
