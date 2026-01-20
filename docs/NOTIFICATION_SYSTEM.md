# Notification and Email System

## Overview

The notification system provides comprehensive communication capabilities for the school management system, including email notifications, in-app notifications, and support for future channels like SMS and push notifications.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│              Notification System Architecture               │
├─────────────────────────────────────────────────────────────┤
│                                                           │
│  ┌─────────────┐    ┌──────────────┐    │
│  │   Email      │    │  Notification  │    │
│  │   Service    │───▶│  Service     │    │
│  └─────────────┘    └──────┬───────┘    │
│                            │             │            │
│                            │             │            │
│                            ▼             ▼            │
│                     ┌───────────────┐            │
│                     │ Notification  │            │
│                     │    Models     │            │
│                     └───────────────┘            │
│                           │                      │
│                           ▼                      │
│              ┌──────────────────────┐          │
│              │   Notification       │          │
│              │   Controller       │          │
│              └──────────────────────┘          │
│                                                  │
└──────────────────────────────────────────────────┘
```

## Database Schema

### Notifications
Stores all notification records.

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| template_id | UUID | Reference to template (optional) |
| title | string | Notification title |
| message | text | Notification message body |
| type | string | Notification type (info, attendance, grade, event, exam, emergency) |
| priority | string | Priority level (low, medium, high, critical) |
| data | json | Additional data (optional) |
| scheduled_at | datetime | When to send (optional) |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

### Notification Templates
Email template definitions for common school communications.

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| name | string | Template name |
| type | string | Template type (attendance, grade, event, emergency, etc.) |
| subject | string | Email subject |
| body | text | Email body with {variable} placeholders |
| variables | json | List of variables used |
| is_active | boolean | Template active status |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

### Notification Recipients
Links notifications to users who should receive them.

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| notification_id | UUID | Reference to notification |
| user_id | UUID | Reference to user |
| read | boolean | Read status |
| read_at | datetime | Read timestamp |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

### Notification Delivery Logs
Tracks delivery status for each channel.

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| notification_id | UUID | Reference to notification |
| recipient_id | UUID | Reference to notification_recipient |
| channel | string | Channel used (email, sms, push, in_app) |
| status | string | Delivery status (sent, delivered, failed) |
| error_message | text | Error message if failed |
| sent_at | datetime | Send timestamp |
| created_at | datetime | Creation timestamp |

### Notification User Preferences
User notification preferences by type.

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| user_id | UUID | Reference to user |
| type | string | Notification type |
| email_enabled | boolean | Receive email notifications |
| sms_enabled | boolean | Receive SMS notifications |
| push_enabled | boolean | Receive push notifications |
| in_app_enabled | boolean | Receive in-app notifications |
| quiet_hours_start | time | Quiet hours start |
| quiet_hours_end | time | Quiet hours end |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

## Email Templates

The system includes pre-configured templates for common school communications:

### Attendance Alert
- **Type**: `attendance`
- **Subject**: Attendance Notification
- **Body**: "You have been marked as {status} for {date}."

### Grade Posted
- **Type**: `grade`
- **Subject**: Grade Posted
- **Body**: "Your grade for {subject} has been posted. Score: {score}"

### Event Reminder
- **Type**: `event`
- **Subject**: Upcoming Event Reminder
- **Body**: "Reminder: You have an upcoming event \"{event_name}\" on {event_date} at {event_time}."

### Emergency Alert
- **Type**: `emergency`
- **Subject**: Emergency Alert
- **Body**: "{message}"

### Assignment Due
- **Type**: `assignment`
- **Subject**: Assignment Due Soon
- **Body**: "Reminder: Assignment \"{assignment_name}\" is due on {due_date}."

### System Maintenance
- **Type**: `info`
- **Subject**: System Maintenance Notice
- **Body**: "The system will undergo maintenance on {maintenance_date} from {start_time} to {end_time}."

## API Endpoints

### Create Notification
```
POST /api/notifications
```

**Request Body**:
```json
{
  "title": "School Closed Tomorrow",
  "message": "Due to weather conditions, school will be closed tomorrow.",
  "type": "emergency",
  "priority": "critical"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Notification created successfully",
  "data": {
    "id": "uuid",
    "title": "School Closed Tomorrow",
    "message": "...",
    "type": "emergency",
    "priority": "critical"
  }
}
```

### Send Notification
```
POST /api/notifications/send
```

**Request Body**:
```json
{
  "notification_id": "uuid",
  "user_ids": ["user1-uuid", "user2-uuid"]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Notification sent successfully",
  "data": null
}
```

### Send Emergency Notification
```
POST /api/notifications/emergency
```

Sends to all active users immediately, bypassing quiet hours.

**Request Body**:
```json
{
  "title": "Emergency: School Evacuation",
  "message": "Please proceed to nearest exit immediately."
}
```

**Response**:
```json
{
  "success": true,
  "message": "Emergency notification sent successfully",
  "data": { ... }
}
```

### Get User Notifications
```
GET /api/notifications/my?limit=20&offset=0&type=grade&read=false
```

**Query Parameters**:
- `limit`: Number of notifications (default: 20)
- `offset`: Pagination offset (default: 0)
- `type`: Filter by type (optional)
- `read`: Filter by read status (true/false, optional)

**Response**:
```json
{
  "success": true,
  "data": {
    "notifications": [...],
    "total": 45,
    "offset": 0,
    "limit": 20
  }
}
```

### Mark Notification as Read
```
PUT /api/notifications/{id}/read
```

**Response**:
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": null
}
```

### Mark All as Read
```
PUT /api/notifications/read-all
```

**Response**:
```json
{
  "success": true,
  "message": "All notifications marked as read",
  "data": null
}
```

### Get Delivery Statistics
```
GET /api/notifications/{id}/stats
```

**Response**:
```json
{
  "success": true,
  "data": {
    "total": 150,
    "email": { "sent": 150, "delivered": 145, "failed": 5 },
    "sms": { "sent": 0, "delivered": 0, "failed": 0 },
    "push": { "sent": 0, "delivered": 0, "failed": 0 },
    "in_app": { "sent": 150, "delivered": 150, "failed": 0 }
  }
}
```

### Create Template
```
POST /api/notifications/templates
```

**Request Body**:
```json
{
  "name": "New Student Welcome",
  "type": "info",
  "subject": "Welcome to Our School!",
  "body": "<p>Hello {name}!</p><p>Welcome to our school.</p>",
  "variables": ["name"]
}
```

### Get Templates
```
GET /api/notifications/templates?type=attendance
```

### Update Preferences
```
PUT /api/notifications/preferences
```

**Request Body**:
```json
{
  "type": "attendance",
  "email_enabled": true,
  "sms_enabled": false,
  "push_enabled": true,
  "in_app_enabled": true,
  "quiet_hours_start": "22:00:00",
  "quiet_hours_end": "07:00:00"
}
```

### Get Preferences
```
GET /api/notifications/preferences?type=attendance
```

## Configuration

### Email Configuration

Add these to your `.env` file:

```env
# Email Configuration
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Malnu School Management
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Frontend URL (for reset links, etc.)
FRONTEND_URL=http://localhost:3000
```

### SMTP Provider Setup

**Common SMTP Providers**:
- Gmail: smtp.gmail.com:587
- SendGrid: smtp.sendgrid.net:587
- Mailgun: smtp.mailgun.org:587
- Amazon SES: email-smtp.us-east-1.amazonaws.com:587

## Usage Examples

### Send Attendance Alert

```php
$notificationService = new NotificationService();

// Create attendance alert
$notification = $notificationService->create([
    'title' => 'Absent Today',
    'message' => 'Your child was absent from school today.',
    'type' => 'attendance',
    'priority' => 'high',
]);

// Send to parent
$notificationService->send($notification->id, ['parent-uuid']);
```

### Send Grade Notification

```php
// Use template
$template = NotificationTemplate::where('type', 'grade')->first();

$notification = $notificationService->create([
    'title' => 'Math Grade Posted',
    'message' => 'Your math test grade has been posted.',
    'type' => 'grade',
    'priority' => 'medium',
    'template_id' => $template->id,
]);

// Send to student
$notificationService->send($notification->id, ['student-uuid']);
```

### Send Emergency Notification

```php
$notification = $notificationService->create([
    'title' => 'School Closure',
    'message' => 'School will be closed tomorrow due to weather.',
    'type' => 'emergency',
    'priority' => 'critical',
]);

// Send to ALL active users immediately
$notificationService->send($notification->id);
```

### User Notification Preferences

```php
// Set user preferences for attendance alerts
$notificationService->updateUserPreference('user-uuid', [
    [
        'type' => 'attendance',
        'email_enabled' => true,
        'sms_enabled' => false,
        'push_enabled' => true,
        'in_app_enabled' => true,
        'quiet_hours_start' => '22:00:00',
        'quiet_hours_end' => '07:00:00',
    ],
]);
```

## Channels

### Email
- Uses SMTP configuration
- HTML email support
- Template-based emails
- Variable substitution

### SMS
- Placeholder for future implementation
- Channel defined in system

### Push
- Placeholder for future implementation
- For mobile app notifications

### In-App
- Stored in database
- Mark as read/unread
- Filtering by type and status

## Quiet Hours

Users can set quiet hours during which non-emergency notifications won't be sent.

**Example**: Parent doesn't want notifications between 10 PM and 7 AM
```php
[
    'type' => 'all',
    'quiet_hours_start' => '22:00:00',
    'quiet_hours_end' => '07:00:00',
]
```

**Behavior**:
- Emergency notifications (priority=critical) always sent
- Normal notifications respect quiet hours
- In-app notifications delivered immediately but marked as unread

## Security Considerations

1. **Rate Limiting**: Configure rate limits for email sending
2. **User Verification**: Only send to verified email addresses
3. **Content Sanitization**: Sanitize all user-generated content
4. **Access Control**: API endpoints require JWT authentication
5. **Privacy**: Don't expose recipient information to other users

## Best Practices

1. **Use Templates**: Create templates for recurring notifications
2. **Variable Substitution**: Use `{variable}` syntax in templates
3. **Respect Preferences**: Always check user preferences
4. **Handle Failures**: Log delivery failures for troubleshooting
5. **Monitor Statistics**: Track delivery rates and success rates
6. **Emergency Protocol**: Emergency notifications bypass all preferences

## Testing

Run notification tests:

```bash
# Run all notification tests
vendor/bin/co-phpunit tests/Feature/NotificationTest.php

# Run specific test
vendor/bin/co-phpunit tests/Feature/NotificationTest.php --filter testEmailServiceSendNotificationEmail

# Run with coverage
vendor/bin/co-phpunit tests/Feature/NotificationTest.php --coverage-html
```

## Troubleshooting

### Emails Not Sending

1. Check SMTP configuration in `.env`
2. Verify SMTP credentials
3. Check firewall settings
4. Review application logs: `storage/logs/app.log`

### Templates Not Working

1. Verify template `is_active` is `true`
2. Check variable names match `{variable}` syntax
3. Test template rendering in isolation

### Notifications Not Received

1. Check user has recipient record
2. Verify user preferences allow channel
3. Check quiet hours configuration
4. Review delivery logs for errors

## Integration Points

### User Management
- Uses `User` model for recipient lookups
- User email from user record

### Attendance System
- Attendance alerts trigger notifications
- Uses attendance template

### Grading System
- Grade postings trigger notifications
- Uses grade template

### Calendar System
- Event reminders sent before events
- Uses event template

## Future Enhancements

1. **SMS Integration**: Add SMS provider integration (Twilio, etc.)
2. **Push Notifications**: Integrate with mobile app push service
3. **Email Queue**: Use background queues for bulk sending
4. **Scheduled Notifications**: Cron-based scheduled notification sending
5. **Rich Templates**: Support for more complex email templates
6. **Analytics Dashboard**: UI for notification statistics
7. **Bulk Actions**: Bulk mark as read, bulk delete, etc.
