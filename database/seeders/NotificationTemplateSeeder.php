<?php

declare(strict_types=1);

use App\Models\Notification\NotificationTemplate;
use Hyperf\Database\Seeder\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Notification',
                'slug' => 'welcome-notification',
                'subject' => 'Welcome to our platform!',
                'body' => 'Hello {{name}}, welcome to our educational platform. We\'re excited to have you on board!',
                'type' => 'welcome',
                'channels' => ['email', 'in_app'],
                'placeholders' => ['name'],
            ],
            [
                'name' => 'Attendance Reminder',
                'slug' => 'attendance-reminder',
                'subject' => 'Attendance Reminder',
                'body' => 'Dear {{name}}, please remember to mark your attendance for today. Your attendance is important for tracking your progress.',
                'type' => 'reminder',
                'channels' => ['email', 'sms', 'in_app'],
                'placeholders' => ['name'],
            ],
            [
                'name' => 'Grade Notification',
                'slug' => 'grade-notification',
                'subject' => 'New Grade Available',
                'body' => 'Hello {{student_name}}, a new grade has been posted for {{subject}}. Your score is {{score}}.',
                'type' => 'grade',
                'channels' => ['email', 'in_app'],
                'placeholders' => ['student_name', 'subject', 'score'],
            ],
            [
                'name' => 'Assignment Due Reminder',
                'slug' => 'assignment-reminder',
                'subject' => 'Assignment Due Soon',
                'body' => 'Dear {{name}}, your assignment "{{assignment}}" is due in {{days}} days. Please submit it on time.',
                'type' => 'reminder',
                'channels' => ['email', 'in_app'],
                'placeholders' => ['name', 'assignment', 'days'],
            ],
            [
                'name' => 'Emergency Alert',
                'slug' => 'emergency-alert',
                'subject' => 'EMERGENCY: School Closure',
                'body' => 'ATTENTION: The school will be closed on {{date}} due to {{reason}}. All classes are canceled. Please stay safe.',
                'type' => 'emergency',
                'channels' => ['email', 'sms', 'push', 'in_app'],
                'placeholders' => ['date', 'reason'],
            ],
            [
                'name' => 'Event Notification',
                'slug' => 'event-notification',
                'subject' => 'Upcoming Event: {{event_name}}',
                'body' => 'Dear {{name}}, we\'re excited to invite you to {{event_name}} on {{date}} at {{time}}. Please join us for this special occasion.',
                'type' => 'event',
                'channels' => ['email', 'in_app'],
                'placeholders' => ['name', 'event_name', 'date', 'time'],
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