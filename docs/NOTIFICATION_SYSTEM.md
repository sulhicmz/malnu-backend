# Notification System Documentation

## Overview

The comprehensive notification and alert system provides multi-channel communication capabilities for the school management platform, supporting email, SMS, push notifications, and in-app notifications with advanced features like user preferences, templates, and delivery tracking.

## Features

- **Multi-channel delivery**: Email, SMS, push notifications, in-app
- **Priority levels**: Critical, high, medium, low
- **Notification templates**: Reusable templates with variable substitution
- **User preferences**: Granular control over notification channels and types
- **Delivery tracking**: Complete delivery status and read receipts
- **Emergency broadcasts**: Critical alerts that bypass user preferences
- **Scheduled notifications**: Send notifications at specific times
- **Bulk notifications**: Send to multiple recipients efficiently

## Database Schema

### Notifications
Stores notification records with metadata and delivery information.

### Notification Templates
Defines reusable notification templates with variable placeholders.

### Notification User Preferences
Stores user notification preferences for channels and types.

### Notification Recipients
Maps notifications to recipients with read status and delivery channels.

### Notification Delivery Logs
Tracks delivery status for each notification through each channel.

## API Endpoints

### Create Notification
```http
POST /api/notifications
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Notification Title",
  "message": "Notification message",
  "type": "general",
  "priority": "medium",
  "channels": ["email", "sms", "push", "in_app"],
  "recipients": ["user-uuid-1", "user-uuid-2"],
  "scheduled_at": "2024-01-01T10:00:00Z",
  "metadata": {}
}
```

### Send Notification
```http
POST /api/notifications/send
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Urgent Notice",
  "message": "This is an urgent notification",
  "type": "emergency",
  "priority": "critical",
  "recipients": ["user-uuid-1"]
}
```

### Send Emergency Notification
```http
POST /api/notifications/emergency
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "EMERGENCY ALERT",
  "message": "Critical emergency message",
  "recipients": ["user-uuid-1", "user-uuid-2"]
}
```

### Get User Notifications
```http
GET /api/notifications/my?user_id={uuid}&read=false&type=attendance&per_page=20
Authorization: Bearer {token}
```

### Mark Notification as Read
```http
PUT /api/notifications/{id}/read
Content-Type: application/json
Authorization: Bearer {token}

{
  "user_id": "user-uuid"
}
```

### Mark All Notifications as Read
```http
PUT /api/notifications/read-all
Content-Type: application/json
Authorization: Bearer {token}

{
  "user_id": "user-uuid"
}
```

### Get Notification Details
```http
GET /api/notifications/{id}
Authorization: Bearer {token}
```

### Get Notification Statistics
```http
GET /api/notifications/{id}/stats
Authorization: Bearer {token}
```

### Create Notification Template
```http
POST /api/notifications/templates
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Attendance Alert",
  "slug": "attendance_alert",
  "type": "attendance",
  "subject": "Attendance Update",
  "body": "Dear {parent_name}, your child {student_name} was marked {attendance_status} on {date}.",
  "variables": ["parent_name", "student_name", "attendance_status", "date"]
}
```

### Update User Preferences
```http
PUT /api/notifications/preferences
Content-Type: application/json
Authorization: Bearer {token}

{
  "user_id": "user-uuid",
  "email_enabled": true,
  "sms_enabled": true,
  "push_enabled": true,
  "in_app_enabled": true,
  "type_preferences": {
    "attendance": true,
    "grade": true,
    "event": false
  },
  "quiet_hours": {
    "start": "22:00",
    "end": "08:00"
  }
}
```

### Get User Preferences
```http
GET /api/notifications/preferences?user_id={uuid}
Authorization: Bearer {token}
```

## Notification Types

- `general` - General announcements
- `attendance` - Attendance updates and alerts
- `grade` - Grade postings and updates
- `event` - Event reminders and updates
- `emergency` - Emergency alerts
- `assignment` - Assignment deadlines
- `system` - System notifications

## Priority Levels

- `critical` - Emergency alerts, bypasses preferences, all channels
- `high` - High importance notifications
- `medium` - Standard priority (default)
- `low` - Low priority notifications

## Delivery Channels

- `email` - Email notifications
- `sms` - SMS text messages
- `push` - Push notifications (mobile/web)
- `in_app` - In-app notifications

## Service Methods

### NotificationService

```php
// Create a notification
$notification = $notificationService->createNotification([
    'title' => 'Title',
    'message' => 'Message',
    'recipients' => ['user-id-1', 'user-id-2'],
    'type' => 'general',
    'priority' => 'medium',
    'channels' => ['email', 'in_app'],
]);

// Send a notification
$notificationService->sendNotification($notification);

// Send bulk notification (create and send in one operation)
$notification = $notificationService->sendBulkNotification([
    'title' => 'Bulk Notification',
    'message' => 'Message',
    'recipients' => ['user-id-1', 'user-id-2'],
]);

// Send emergency notification (critical priority, all channels)
$notification = $notificationService->sendEmergencyNotification([
    'title' => 'EMERGENCY',
    'message' => 'Emergency message',
    'recipients' => ['user-id-1'],
]);

// Create template
$template = $notificationService->createTemplate([
    'name' => 'Template Name',
    'slug' => 'template-slug',
    'type' => 'attendance',
    'body' => 'Dear {name}, your child was {status} on {date}.',
    'variables' => ['name', 'status', 'date'],
]);

// Get user notifications
$notifications = $notificationService->getUserNotifications($userId, [
    'read' => false,
    'type' => 'attendance',
    'per_page' => 20,
]);

// Mark notification as read
$notificationService->markAsRead($notificationId, $userId);

// Mark all notifications as read
$count = $notificationService->markAllAsRead($userId);

// Update user preferences
$preference = $notificationService->updateUserPreference($userId, [
    'email_enabled' => true,
    'sms_enabled' => false,
    'push_enabled' => true,
    'in_app_enabled' => true,
]);

// Get delivery statistics
$stats = $notificationService->getDeliveryStats($notification);

// Process template with variables
$message = $notificationService->processTemplate($template, [
    'name' => 'John Doe',
    'status' => 'absent',
    'date' => '2024-01-01',
]);

// Send scheduled notifications
$count = $notificationService->sendScheduledNotifications();
```

## Integration Examples

### Sending an attendance notification
```php
$notificationService = new NotificationService();

$notification = $notificationService->sendBulkNotification([
    'title' => 'Attendance Update',
    'message' => 'Your child was marked absent today',
    'type' => 'attendance',
    'priority' => 'high',
    'recipients' => [$parentUserId],
    'channels' => ['email', 'sms', 'in_app'],
]);
```

### Using a template
```php
$template = NotificationTemplate::where('slug', 'attendance_alert')->first();

$message = $notificationService->processTemplate($template, [
    'parent_name' => 'John Doe',
    'student_name' => 'Jane Doe',
    'attendance_status' => 'absent',
    'date' => '2024-01-01',
]);

$notification = $notificationService->sendBulkNotification([
    'template_id' => $template->id,
    'title' => 'Attendance Update',
    'message' => $message,
    'type' => 'attendance',
    'recipients' => [$parentUserId],
]);
```

### Emergency broadcast
```php
$notificationService->sendEmergencyNotification([
    'title' => 'SCHOOL CLOSURE',
    'message' => 'School will be closed tomorrow due to severe weather',
    'recipients' => $allParentIds, // Array of all parent user IDs
]);
```

## Templates

Pre-defined templates available in the system:

1. **Attendance Alert** (`attendance_alert`) - Notify parents about attendance changes
2. **Grade Posted** (`grade_posted`) - Notify students when grades are posted
3. **Event Reminder** (`event_reminder`) - Remind about upcoming events
4. **Emergency Alert** (`emergency_alert`) - Critical emergency messages
5. **Assignment Due** (`assignment_due`) - Assignment deadline reminders

To seed these templates, run:
```bash
php artisan db:seed --class=NotificationTemplateSeeder
```

## Configuration

The notification system respects the following environment settings:

- `QUEUE_CONNECTION` - Queue driver for async processing (database/redis)
- `CACHE_DRIVER` - Cache driver for performance optimization

## Security Considerations

- All endpoints require JWT authentication
- User preferences control which notifications are received
- Emergency alerts bypass preferences but require proper authorization
- All notifications are logged for compliance and audit trails
- Personal contact information is protected in delivery logs

## Performance Optimization

- Use database queues for async processing
- Implement caching for user preferences
- Batch notifications for large recipient lists
- Use scheduled notifications to distribute load
- Monitor delivery logs for failed deliveries

## Troubleshooting

### Notifications not being delivered
1. Check user preferences for channel settings
2. Verify user has enabled notifications for the type
3. Check delivery logs for specific error messages
4. Ensure queue workers are running if using async processing

### Delivery failures
1. Review notification_delivery_logs table for error messages
2. Check retry counts and implement retry logic for failures
3. Verify email/SMS provider configurations
4. Ensure user contact information is valid

### Performance issues
1. Use pagination for large notification lists
2. Implement caching for frequently accessed preferences
3. Consider using Redis queues instead of database queues
4. Batch operations for large-scale notifications
