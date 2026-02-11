# Notification System

This document describes the comprehensive notification and alert system implementation for Malnu Backend.

## Overview

The notification system provides real-time multi-channel delivery of messages, alerts, and notifications to students, parents, teachers, and staff across the school management platform.

## Features

### Multi-Channel Delivery
- **Email Notifications** - Send emails for important announcements, grade updates, attendance alerts
- **SMS Notifications** - Mobile text messages for urgent alerts and reminders
- **In-App Notifications** - Real-time notifications within the application
- **Push Notifications** - Browser push notifications for immediate awareness

### Priority Levels
- **Critical** - Emergency alerts, bypasses user preferences
- **High** - Important announcements and deadlines
- **Medium** - Standard notifications (default)
- **Low** - Informational messages

### User Preferences
Users can customize their notification experience:
- Enable/disable channels (email, SMS, push, in-app)
- Set preferences by notification type (attendance, grades, events, etc.)
- Configure quiet hours to suppress non-urgent notifications

### Template System
Pre-built templates for common notification types:
- Attendance alerts
- Grade postings
- Event reminders
- Emergency broadcasts
- Assignment due reminders
- System maintenance notices

### Delivery Tracking
Complete tracking of all notification delivery:
- Per-channel status (pending, sent, delivered, failed)
- Error logging for failed deliveries
- Delivery statistics aggregation

## API Endpoints

### Notification Management
- `POST /api/notifications` - Create a new notification
- `POST /api/notifications/send` - Send a notification to specific users
- `POST /api/notifications/emergency` - Send emergency broadcast (bypasses preferences)
- `GET /api/notifications/my` - Get user's notifications with filtering
- `GET /api/notifications/{id}` - Get notification details
- `PUT /api/notifications/{id}/read` - Mark notification as read
- `PUT /api/notifications/read-all` - Mark all notifications as read
- `GET /api/notifications/{id}/stats` - Get delivery statistics

### Template Management
- `POST /api/notifications/templates` - Create notification template
- `GET /api/notifications/templates` - Get all templates (with optional type filter)
- `POST /api/notifications/process-template` - Process template with variables

### User Preferences
- `PUT /api/notifications/preferences` - Update user notification preferences
- `GET /api/notifications/preferences` - Get user preferences (with optional type filter)

## Notification Types

The system supports the following notification types:

| Type | Description | Default Channels |
|-------|-------------|----------------|
| `attendance` | Attendance alerts, leave updates | email, in-app |
| `grade` | Grade postings, score updates | email, in-app |
| `event` | Event reminders, schedule changes | email, sms, in-app |
| `exam` | Exam schedules, results | email, in-app |
| `assignment` | Assignment due dates, submissions | email, in-app |
| `emergency` | Emergency broadcasts, critical alerts | email, sms, push, in-app |
| `info` | General information, announcements | in-app |

## Priority System

| Priority | Description | Bypasses Preferences? |
|----------|-------------|---------------------|
| `critical` | Emergency alerts, safety threats | YES |
| `high` | Important deadlines, urgent updates | NO |
| `medium` | Standard notifications (default) | NO |
| `low` | Informational messages | NO |

## User Preference Structure

```json
{
  "user_id": "uuid",
  "type": "all|attendance|grade|event|exam|assignment|emergency|info",
  "email_enabled": true,
  "sms_enabled": true,
  "push_enabled": true,
  "in_app_enabled": true,
  "quiet_hours_start": "22:00",
  "quiet_hours_end": "07:00"
}
```

## Quiet Hours

Users can configure quiet hours to suppress notifications during specific time periods (e.g., nighttime). Emergency notifications bypass quiet hours.

## Template Variables

Templates support variable substitution using `{variable}` syntax:

Example template body:
```
You have been marked as {status} for {date}.
```

Variables:
- `status` - Attendance status (present/absent)
- `date` - Attendance date
- `subject` - Subject name
- `score` - Grade score
- `event_name` - Event name
- `event_date` - Event date
- `event_time` - Event time
- `assignment_name` - Assignment name
- `due_date` - Assignment due date

## Delivery Status Tracking

Each notification delivery is tracked with the following statuses:

| Status | Description |
|---------|-------------|
| `pending` | Queued for delivery |
| `sent` | Successfully sent to channel provider |
| `delivered` | Confirmed delivered to user |
| `failed` | Delivery failed (error message available) |

## Security Considerations

1. **Authentication**: All notification endpoints require JWT authentication
2. **Authorization**: Create and send operations require appropriate roles
3. **Data Privacy**: Notifications respect user preferences and quiet hours
4. **Rate Limiting**: All endpoints protected by rate limiting middleware
5. **Audit Trail**: All notification operations are logged

## Integration Points

The notification system integrates with:

- **Authentication System** - Uses JWT for user identification
- **User Management** - References users table for recipients
- **Email Provider** - Placeholder for SMTP/email service integration
- **SMS Provider** - Placeholder for SMS gateway integration
- **Push Provider** - Placeholder for push notification service
- **Database Queue** - Ready for async processing with Redis

## Usage Examples

### Create and Send a Notification

```bash
# Create notification
curl -X POST http://localhost:9501/api/notifications \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Grade Posted",
    "message": "Your grade for Math has been posted. Score: 85",
    "type": "grade",
    "priority": "medium"
  }'

# Send to specific users
curl -X POST http://localhost:9501/api/notifications/send \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "notification_id": "{notification_id}",
    "user_ids": ["user_id_1", "user_id_2"]
  }'
```

### Emergency Broadcast

```bash
curl -X POST http://localhost:9501/api/notifications/emergency \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "School Closure",
    "message": "School will be closed tomorrow due to severe weather",
    "priority": "critical"
  }'
```

### Get User Notifications

```bash
# Get all notifications
curl -X GET http://localhost:9501/api/notifications/my \
  -H "Authorization: Bearer {jwt_token}"

# Get filtered notifications
curl -X GET "http://localhost:9501/api/notifications/my?type=grade&limit=10&offset=0" \
  -H "Authorization: Bearer {jwt_token}"

# Get only unread notifications
curl -X GET "http://localhost:9501/api/notifications/my?read=false" \
  -H "Authorization: Bearer {jwt_token}"
```

### Mark Notification as Read

```bash
curl -X PUT http://localhost:9501/api/notifications/{id}/read \
  -H "Authorization: Bearer {jwt_token}"
```

### Update User Preferences

```bash
curl -X PUT http://localhost:9501/api/notifications/preferences \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "attendance",
    "email_enabled": true,
    "sms_enabled": false,
    "push_enabled": true,
    "in_app_enabled": true,
    "quiet_hours_start": "22:00",
    "quiet_hours_end": "07:00"
  }'
```

### Get Delivery Statistics

```bash
curl -X GET http://localhost:9501/api/notifications/{id}/stats \
  -H "Authorization: Bearer {jwt_token}"

# Response
{
  "success": true,
  "data": {
    "total": 100,
    "email": {
      "sent": 95,
      "delivered": 90,
      "failed": 5
    },
    "sms": {
      "sent": 100,
      "delivered": 98,
      "failed": 2
    },
    "push": {
      "sent": 100,
      "delivered": 100,
      "failed": 0
    },
    "in_app": {
      "sent": 100,
      "delivered": 100,
      "failed": 0
    }
  },
  "message": "Delivery statistics retrieved successfully",
  "timestamp": "2024-01-12T10:30:00Z"
}
```

## Service Architecture

### NotificationService

The `NotificationService` class provides:

- `create(array $data)` - Create new notification
- `send(string $notificationId, ?array $userIds)` - Send notification to users
- `markAsRead(string $notificationId, string $userId)` - Mark notification as read
- `getUserNotifications(string $userId, ?int $limit, ?int $offset)` - Get user notifications
- `getDeliveryStatistics(string $notificationId)` - Get delivery statistics
- `getUserPreference(string $userId, string $type)` - Get user preferences
- `updateUserPreference(string $userId, array $preferences)` - Update preferences
- `getNotificationTemplates(?string $type)` - Get templates
- `createTemplate(array $data)` - Create template
- `processTemplate(NotificationTemplate $template, array $variables)` - Process template with variables

### Key Features

- **Priority System** - Critical, high, medium, low
- **Emergency Bypass** - Critical notifications ignore user preferences
- **Multi-Channel Support** - Email, SMS, push, in-app
- **Quiet Hours** - Respects user quiet hour preferences
- **Variable Substitution** - Template processing with {variable} syntax
- **Delivery Tracking** - Complete per-channel status tracking

## Database Schema

### notifications
Main notification records table:
- `id` (UUID, primary) - Unique notification identifier
- `template_id` (UUID, nullable) - Reference to template
- `title` (string) - Notification title
- `message` (text) - Notification content
- `type` (string) - Notification type
- `priority` (string) - Priority level
- `data` (json, nullable) - Additional metadata
- `scheduled_at` (timestamp, nullable) - For scheduled notifications
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

### notification_templates
Reusable notification templates:
- `id` (UUID, primary) - Unique template identifier
- `name` (string) - Template name
- `type` (string) - Template type
- `subject` (string, nullable) - Email subject
- `body` (text) - Template body with {variable} placeholders
- `variables` (json, nullable) - Available variables
- `is_active` (boolean) - Template active status
- `created_at` (timestamp)
- `updated_at` (timestamp)

### notification_user_preferences
User notification preferences:
- `id` (UUID, primary) - Unique preference identifier
- `user_id` (UUID, foreign) - Reference to users table
- `type` (string) - Preference type (all or specific)
- `email_enabled` (boolean) - Email channel enabled
- `sms_enabled` (boolean) - SMS channel enabled
- `push_enabled` (boolean) - Push channel enabled
- `in_app_enabled` (boolean) - In-app channel enabled
- `quiet_hours_start` (time, nullable) - Quiet period start
- `quiet_hours_end` (time, nullable) - Quiet period end
- `created_at` (timestamp)
- `updated_at` (timestamp)

### notification_recipients
Links notifications to users:
- `id` (UUID, primary) - Unique recipient identifier
- `notification_id` (UUID, foreign) - Reference to notifications
- `user_id` (UUID, foreign) - Reference to users table
- `read` (boolean) - Read status
- `read_at` (timestamp, nullable) - Read timestamp
- `created_at` (timestamp)
- `updated_at` (timestamp)

### notification_delivery_logs
Delivery tracking records:
- `id` (UUID, primary) - Unique log identifier
- `notification_id` (UUID, foreign) - Reference to notifications
- `recipient_id` (UUID, foreign) - Reference to notification_recipients
- `channel` (string) - Delivery channel (email, sms, push, in_app)
- `status` (enum) - Delivery status (pending, sent, delivered, failed)
- `error_message` (text, nullable) - Error details for failed deliveries
- `sent_at` (timestamp, nullable) - When delivery was attempted
- `created_at` (timestamp)
- `updated_at` (timestamp)

## Future Enhancements

1. **Provider Integration** - Implement actual email, SMS, and push service providers
2. **Queue Processing** - Integrate with Redis queue for async processing
3. **Notification Batching** - Support bulk notification creation
4. **Scheduled Notifications** - Cron job for processing scheduled notifications
5. **Webhooks** - Webhook support for delivery status updates from providers
6. **Notification Categories** - Additional categorization beyond current types
7. **Rich Content** - Support for HTML, images, and attachments
8. **Analytics Dashboard** - Admin dashboard for notification analytics
9. **Localization** - Multi-language support for templates
10. **Smart Notifications** - AI-powered notification timing and content optimization

## Troubleshooting

### Notifications Not Being Delivered

1. Check notification recipient table for records
2. Check delivery logs for error messages
3. Verify user preferences are respected
4. Check if user is in quiet hours
5. Review logs in storage/logs/notification.log

### High Delivery Failure Rate

1. Check provider credentials (email, SMS, push)
2. Verify API quotas and rate limits
3. Check for malformed email addresses
4. Review error patterns in delivery logs
5. Consider implementing retry logic with exponential backoff

### Template Variables Not Working

1. Verify template body uses {variable} syntax
2. Check variables array is properly formatted JSON
3. Test template processing with test data
4. Verify variable names match what's passed in processTemplate

## Testing

Run the notification system tests:

```bash
vendor/bin/phpunit tests/Feature/NotificationTest.php
```

This will run all test cases covering:
- Notification creation
- Multi-user sending
- Emergency notifications
- Mark as read
- User notifications retrieval
- Filtering by type
- Template management
- User preferences
- Delivery statistics
- Template variable processing
