<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification\NotificationTemplate;
use Hyperf\DbConnection\Db;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Attendance Alert',
                'type' => 'attendance',
                'subject' => 'Attendance Notification',
                'body' => 'You have been marked as {status} for {date}.',
                'variables' => json_encode(['status', 'date']),
                'is_active' => true,
            ],
            [
                'name' => 'Grade Posted',
                'type' => 'grade',
                'subject' => 'Grade Posted',
                'body' => 'Your grade for {subject} has been posted. Score: {score}',
                'variables' => json_encode(['subject', 'score']),
                'is_active' => true,
            ],
            [
                'name' => 'Event Reminder',
                'type' => 'event',
                'subject' => 'Upcoming Event Reminder',
                'body' => 'Reminder: You have an upcoming event "{event_name}" on {event_date} at {event_time}.',
                'variables' => json_encode(['event_name', 'event_date', 'event_time']),
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Alert',
                'type' => 'emergency',
                'subject' => 'Emergency Alert',
                'body' => '{message}',
                'variables' => json_encode(['message']),
                'is_active' => true,
            ],
            [
                'name' => 'Assignment Due',
                'type' => 'assignment',
                'subject' => 'Assignment Due Soon',
                'body' => 'Reminder: Assignment "{assignment_name}" is due on {due_date}.',
                'variables' => json_encode(['assignment_name', 'due_date']),
                'is_active' => true,
            ],
            [
                'name' => 'System Maintenance',
                'type' => 'info',
                'subject' => 'System Maintenance Notice',
                'body' => 'The system will undergo maintenance on {maintenance_date} from {start_time} to {end_time}.',
                'variables' => json_encode(['maintenance_date', 'start_time', 'end_time']),
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(['name' => $template['name'], 'type' => $template['type']], $template);
        }

        $this->command->info('Notification templates seeded successfully.');
    }
}
