# Notification and Alert System

## Overview

The notification and alert system provides a comprehensive multi-channel communication platform for the school management system. It supports real-time notifications with priority levels and multiple delivery channels including email, SMS, push notifications, and in-app alerts.

## Features

### Core Features
- **Real-time notification engine** with priority levels (critical, high, medium, low)
- **Multi-channel delivery**: Email, SMS, in-app notifications, push notifications
- **Notification templates** for common scenarios (attendance, grades, events, emergencies)
- **User preference management** for channel selection and timing
- **Scheduled notifications** with recurring options
- **Bulk notification capabilities** for school-wide announcements
- **Notification history and tracking** with delivery status
- **Emergency broadcast system** with override capabilities

### Technical Implementation
- Database tables for notifications, user preferences, delivery logs
- Queue-based processing for reliable delivery
- Integration with existing user roles and permissions
- API endpoints for triggering and managing notifications
- Frontend components for notification center and preferences

## Database Schema

### notification_templates
- `id`: UUID primary key
- `name`: Template name
- `slug`: Unique identifier for the template
- `subject`: Subject line for email notifications
- `body`: Notification content with placeholders
- `placeholders`: JSON array of placeholder variables
- `type`: Notification type (email, sms, in_app, push)
- `is_active`: Boolean indicating if template is active
- `channels`: JSON array of delivery channels
- `timestamps`: Created and updated timestamps

### user_notification_preferences
- `id`: UUID primary key
- `user_id`: Foreign key to users table
- `preferences`: JSON object with notification preferences
- `email_enabled`: Boolean for email notifications
- `sms_enabled`: Boolean for SMS notifications
- `push_enabled`: Boolean for push notifications
- `in_app_enabled`: Boolean for in-app notifications
- `timezone`: User's timezone
- `timestamps`: Created and updated timestamps

### notifications
- `id`: UUID primary key
- `template_id`: Foreign key to notification_templates (nullable)
- `sender_id`: Foreign key to users table (nullable for system notifications)
- `title`: Notification title
- `content`: Notification content
- `type`: Notification type (emergency, alert, reminder, info)
- `priority`: Priority level (low, medium, high, critical)
- `data`: Additional data for the notification (JSON)
- `channels`: JSON array of delivery channels
- `scheduled_at`: Scheduled delivery time (nullable)
- `sent_at`: Time notification was sent (nullable)
- `expires_at`: Expiration time (nullable)
- `is_broadcast`: Boolean for system-wide notifications
- `is_read`: Boolean for in-app notifications
- `timestamps`: Created and updated timestamps

### notification_recipients
- `id`: UUID primary key
- `notification_id`: Foreign key to notifications table
- `user_id`: Foreign key to users table
- `delivery_status`: JSON object with status per channel
- `read_at`: Time recipient read the notification (nullable)
- `timestamps`: Created and updated timestamps

### notification_delivery_logs
- `id`: UUID primary key
- `notification_id`: Foreign key to notifications table
- `recipient_id`: Foreign key to notification_recipients table
- `channel`: Delivery channel (email, sms, push, in_app)
- `status`: Delivery status (pending, sent, failed, delivered)
- `response`: Response from the delivery service (nullable)
- `delivered_at`: Time of delivery (nullable)
- `timestamps`: Created and updated timestamps

## API Endpoints

### Authentication Required
All endpoints require JWT authentication.

### GET `/api/notifications`
Get user notifications with pagination and filtering.

**Query Parameters:**
- `limit` (optional, default: 20): Number of notifications to return
- `offset` (optional, default: 0): Offset for pagination
- `status` (optional, default: 'all'): Filter by status ('all', 'unread', 'read')

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 100
  }
}
```

### POST `/api/notifications/send`
Send a notification to specific users.

**Request Body:**
```json
{
  "title": "Notification Title",
  "content": "Notification Content",
  "user_ids": ["user-uuid-1", "user-uuid-2"],
  "type": "general", // optional
  "priority": "medium", // optional
  "channels": ["email", "in_app"], // optional
  "data": {} // optional
}
```

**Response:**
```json
{
  "success": true,
  "data": { notification_object }
}
```

### POST `/api/notifications/broadcast`
Send a broadcast notification to all users (admin only).

**Request Body:**
```json
{
  "title": "Broadcast Title",
  "content": "Broadcast Content",
  "type": "general", // optional
  "priority": "high", // optional
  "channels": ["email", "sms"], // optional
  "data": {} // optional
}
```

**Response:**
```json
{
  "success": true,
  "data": { notification_object }
}
```

### POST `/api/notifications/template`
Send notification from template.

**Request Body:**
```json
{
  "template_slug": "welcome-notification",
  "user_ids": ["user-uuid-1", "user-uuid-2"],
  "template_data": {
    "name": "John Doe"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": { notification_object }
}
```

### POST `/api/notifications/{id}/read`
Mark notification as read.

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

### GET `/api/notifications/templates`
Get all active notification templates.

**Response:**
```json
{
  "success": true,
  "data": [...]
}
```

### GET `/api/notifications/preferences`
Get user notification preferences.

**Response:**
```json
{
  "success": true,
  "data": { preferences_object }
}
```

### POST `/api/notifications/preferences`
Update user notification preferences.

**Request Body:**
```json
{
  "email_enabled": true,
  "sms_enabled": false,
  "push_enabled": true,
  "in_app_enabled": true,
  "timezone": "Asia/Jakarta",
  "preferences": {}
}
```

**Response:**
```json
{
  "success": true,
  "message": "Preferences updated successfully"
}
```

## Service Classes

### NotificationService
Handles all notification logic including:
- Sending notifications to users
- Processing templates
- Managing delivery status
- Updating user preferences
- Retrieving user notifications

### SendNotificationJob
Queue job for processing notifications in the background to avoid blocking the main thread.

## Default Templates

The system comes with several default templates:
- Welcome Notification
- Attendance Reminder
- Grade Notification
- Assignment Due Reminder
- Emergency Alert
- Event Notification

## Security Considerations

- Only authenticated users can access notification endpoints
- Broadcast notifications are restricted to admin users
- User data is protected and only accessible to the user themselves
- Rate limiting should be implemented to prevent spam

## Performance Considerations

- Queue-based processing for bulk notifications
- Efficient database queries with proper indexing
- Caching of user preferences
- Batch processing for large notification sets

## Testing

The notification system includes comprehensive testing for:
- Sending notifications via different channels
- Template processing with placeholders
- User preference management
- Bulk notification delivery
- Emergency broadcast functionality