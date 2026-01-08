# Parent Portal Implementation

## Overview

This document describes the comprehensive Parent Engagement and Communication Portal implementation for the Malnu School Management System. The portal provides parents with real-time access to their children's academic progress, direct communication channels with teachers, and community engagement opportunities.

## Features Implemented

### 1. Student Information & Progress Dashboard

#### Children Management
- **Endpoint**: `GET /api/parent/children`
- **Description**: Retrieve all children associated with the parent
- **Data Returned**:
  - Student ID, name, email
  - Grade level and enrollment status
  - Relationship type and primary contact status
  - Custody information

#### Student Dashboard
- **Endpoint**: `GET /api/parent/children/{studentId}/dashboard`
- **Description**: Comprehensive view of student's academic performance
- **Includes**:
  - Student information
  - Current grades by subject
  - GPA calculation
  - Attendance summary
  - Upcoming assignments
  - Behavioral records

#### Academic Progress
- **Endpoint**: `GET /api/parent/children/{studentId}/progress`
- **Optional Query Parameters**: `term` - Filter by academic term
- **Features**:
  - Detailed performance metrics
  - Grade trends analysis
  - Areas of strength identification
  - Areas needing attention
  - Comparative performance analysis

#### Transcript Generation
- **Endpoint**: `GET /api/parent/children/{studentId}/transcript`
- **Description**: Official academic transcript with all records
- **Format**: Complete academic history with semester-wise data

### 2. Communication System

#### Messaging
- **Send Message**: `POST /api/parent/messages`
  - Support for individual messages
  - Thread-based conversation
  - File attachment support

- **Retrieve Messages**: `GET /api/parent/messages`
  - Query parameters: `thread_id`, `unread_only`, `page`, `per_page`
  - Pagination support

- **Message Thread**: `GET /api/parent/messages/threads/{threadId}`
  - Complete conversation history

- **Mark as Read**: `PUT /api/parent/messages/{messageId}/read`
- **Mark All as Read**: `PUT /api/parent/messages/read-all`

#### Parent-Teacher Conferences
- **Schedule Conference**: `POST /api/parent/conferences`
  - Date, time, duration configuration
  - Optional notes for meeting

- **Update Status**: `PUT /api/parent/conferences/{conferenceId}/status`
  - Status options: scheduled, confirmed, completed, cancelled
  - Add parent or teacher notes

- **List Conferences**: `GET /api/parent/conferences`
  - Query parameters: `status`

- **Upcoming Conferences**: `GET /api/parent/conferences/upcoming`

### 3. Engagement Features

#### Event Management
- **Register for Event**: `POST /api/parent/events/registrations`
  - Support for multiple attendees
  - Additional information capture

- **Cancel Registration**: `DELETE /api/parent/events/registrations/{eventId}`

- **View Registrations**: `GET /api/parent/events/registrations`
  - Query parameters: `status`
  - Event details included

#### Volunteer Opportunities
- **Browse Opportunities**: `GET /api/parent/volunteer/opportunities`
  - Available slots tracking
  - Requirements display

- **Sign Up**: `POST /api/parent/volunteer/signups`
  - Notes and confirmation

- **Cancel Signup**: `DELETE /api/parent/volunteer/signups/{opportunityId}`

- **My Signups**: `GET /api/parent/volunteer/signups`

#### Notification Preferences
- **Update Preferences**: `PUT /api/parent/notifications/preferences`
  - Granular control by notification type
  - Channel preferences (email, SMS, push, in-app)
  - Digest mode configuration

- **View Preferences**: `GET /api/parent/notifications/preferences`

#### Engagement Metrics
- **Get Metrics**: `GET /api/parent/engagement/metrics`
  - Total actions count
  - Actions by type breakdown
  - Last activity timestamp
  - Engagement score calculation

## Database Schema

### Tables Created

1. **parent_student_relationships**
   - Parent-child mappings with custody info
   - Relationship type and primary contact flags
   - Contact preferences (JSON)

2. **parent_messages**
   - Secure messaging between parents and teachers
   - Thread-based conversations
   - Read status tracking
   - Attachment support

3. **parent_conferences**
   - Scheduled parent-teacher meetings
   - Status tracking
   - Teacher and parent notes

4. **parent_notification_preferences**
   - Granular notification settings
   - Channel preferences by type
   - Digest mode configuration

5. **parent_engagement_logs**
   - Parent activity tracking
   - IP and user agent logging
   - Action type categorization

6. **parent_event_registrations**
   - Event registration management
   - Status tracking
   - Attendee count

7. **parent_volunteer_opportunities**
   - Available volunteer opportunities
   - Slot management
   - Requirements storage

8. **parent_volunteer_signups**
   - Parent volunteer signups
   - Status tracking

## Security & Privacy

### Data Isolation
- **Parent-Child Verification**: All student data access verified through `parent_student_relationships` table
- **Service-Layer Enforcement**: Data isolation enforced in all service methods
- **Role-Based Access**: Parent portal middleware ensures only parents can access endpoints

### Authentication
- **JWT Required**: All endpoints protected with JWT authentication middleware
- **User Context**: User information extracted from JWT token for authorization

### Privacy Features
- **Strict Data Access**: Parents only see their own children's data
- **Audit Logging**: All parent actions logged in `parent_engagement_logs`
- **Secure Messaging**: Messages encrypted in transit, access restricted to sender/recipient

## Integration Points

### Student Information System (SIS)
- **Used**: Grade retrieval, GPA calculation, transcript generation
- **Service**: `ParentPortalService` integrates with `SISService`

### Calendar System
- **Used**: Event registration, calendar event details
- **Model**: `ParentEventRegistration` relates to `CalendarEvent`

### Notification System
- **Used**: Notification preference management
- **Model**: `ParentNotificationPreference` for granular settings

## API Response Format

All endpoints follow standard response format:

```json
{
  "success": true,
  "data": { /* response data */ },
  "message": "Operation successful",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

Error responses:
```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": { /* optional details */ }
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

## Performance Considerations

### Pagination
- **Messages**: Default 20 per page
- **Events**: All registrations
- **Conferences**: All scheduled

### Caching
- Consider caching frequently accessed student data (grades, attendance)
- Cache user notification preferences

### Database Optimization
- Indexes on frequently queried fields:
  - `parent_student_relationships`: `(parent_id, student_id)`
  - `parent_messages`: `(recipient_id, is_read)`, `(thread_id)`
  - `parent_conferences`: `(scheduled_date, status)`
  - `parent_engagement_logs`: `(parent_id, created_at)`

## Future Enhancements

### Planned Features
1. **Real-Time Updates**: WebSocket integration for live notifications
2. **Mobile App**: Native mobile application for on-the-go access
3. **AI-Powered Insights**: Learning analytics and recommendations
4. **Fee Management**: Online fee payment integration
5. **Advanced Analytics**: Detailed engagement reports and trends

### Integration Opportunities
1. **Calendar Integration**: Sync with personal calendars (Google, Outlook)
2. **SMS Gateway**: Two-way SMS communication
3. **Email Templates**: Customizable email notifications
4. **Video Conferencing**: Integrated virtual meeting support

## Testing

### Test Coverage
- **Feature Tests**: `tests/Feature/ParentPortalTest.php`
- **Coverage Areas**:
  - Parent-child relationship verification
  - Data isolation enforcement
  - Messaging functionality
  - Conference scheduling
  - Event registration
  - Notification preference management
  - Engagement metrics calculation

### Running Tests
```bash
# Run all parent portal tests
composer test -- filter=ParentPortalTest

# Run specific test
composer test -- filter=test_parent_can_get_their_children
```

## Troubleshooting

### Common Issues

1. **Parent Cannot Access Child Data**
   - **Cause**: No `parent_student_relationship` record exists
   - **Solution**: Create relationship record in database

2. **Message Not Delivered**
   - **Cause**: Recipient not found or invalid ID
   - **Solution**: Verify recipient user exists and is active

3. **Conference Scheduling Fails**
   - **Cause**: Invalid date or teacher not found
   - **Solution**: Validate inputs and check teacher availability

4. **Event Registration Fails**
   - **Cause**: Event not found or already registered
   - **Solution**: Check event status and existing registrations

## Support & Maintenance

### Logging
- **Service Methods**: Log errors and exceptions
- **Audit Trail**: All parent actions logged to `parent_engagement_logs`
- **Error Tracking**: Centralized error logging via BaseController

### Monitoring
- **Engagement Metrics**: Track parent portal usage
- **API Performance**: Monitor endpoint response times
- **Error Rates**: Track and alert on error rate increases

## API Endpoint Reference

### Student Information
| Method | Endpoint | Description | Auth Required |
|---------|----------|-------------|----------------|
| GET | `/api/parent/children` | List parent's children | Yes |
| GET | `/api/parent/children/{id}/dashboard` | Get student dashboard | Yes |
| GET | `/api/parent/children/{id}/progress` | Get student progress | Yes |
| GET | `/api/parent/children/{id}/transcript` | Get student transcript | Yes |
| GET | `/api/parent/children/{id}/attendance` | Get attendance records | Yes |
| GET | `/api/parent/children/{id}/assignments` | Get assignments | Yes |
| GET | `/api/parent/children/{id}/behavior` | Get behavior records | Yes |

### Communication
| Method | Endpoint | Description | Auth Required |
|---------|----------|-------------|----------------|
| POST | `/api/parent/messages` | Send message | Yes |
| GET | `/api/parent/messages` | Get messages | Yes |
| GET | `/api/parent/messages/threads/{id}` | Get message thread | Yes |
| PUT | `/api/parent/messages/{id}/read` | Mark as read | Yes |
| PUT | `/api/parent/messages/read-all` | Mark all as read | Yes |
| POST | `/api/parent/conferences` | Schedule conference | Yes |
| PUT | `/api/parent/conferences/{id}/status` | Update conference | Yes |
| GET | `/api/parent/conferences` | List conferences | Yes |
| GET | `/api/parent/conferences/upcoming` | Upcoming conferences | Yes |

### Engagement
| Method | Endpoint | Description | Auth Required |
|---------|----------|-------------|----------------|
| GET | `/api/parent/engagement/metrics` | Get engagement metrics | Yes |
| POST | `/api/parent/events/registrations` | Register for event | Yes |
| DELETE | `/api/parent/events/registrations/{id}` | Cancel registration | Yes |
| GET | `/api/parent/events/registrations` | Get registrations | Yes |
| GET | `/api/parent/volunteer/opportunities` | Get volunteer opportunities | Yes |
| POST | `/api/parent/volunteer/signups` | Sign up for opportunity | Yes |
| DELETE | `/api/parent/volunteer/signups/{id}` | Cancel signup | Yes |
| GET | `/api/parent/volunteer/signups` | Get signups | Yes |
| PUT | `/api/parent/notifications/preferences` | Update preferences | Yes |
| GET | `/api/parent/notifications/preferences` | Get preferences | Yes |

## License & Credits

- **Implementation Date**: January 8, 2026
- **Version**: 1.0.0
- **Issue Reference**: #232 - Implement comprehensive parent engagement and communication portal
- **Repository**: https://github.com/sulhicmz/malnu-backend

---

*This implementation addresses all requirements specified in issue #232 and provides a comprehensive foundation for parent engagement and communication.*
