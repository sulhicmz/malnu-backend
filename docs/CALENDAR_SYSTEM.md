# Calendar and Event Management System

## Overview
The Calendar and Event Management System provides comprehensive scheduling, event management, and resource booking capabilities for the school management platform. This system enables administrators, teachers, and staff to manage academic calendars, events, and resources efficiently.

## Features

### Core Features
- **Calendar Management**: Create and manage multiple calendars (academic, staff, student, etc.)
- **Academic Term Management**: Manage academic terms, semesters, and enrollment periods
- **Holiday Management**: Track school holidays, breaks, and special dates
- **Event Creation**: Schedule one-time and recurring events with detailed information
- **Resource Booking**: Book rooms, equipment, and facilities with conflict detection
- **Multi-calendar Support**: Separate calendars for different departments and roles
- **Calendar Sharing**: Share calendars with specific permissions (view, edit, admin)
- **Event Registration**: Allow users to register for events with capacity limits
- **Event Attendance Tracking**: Check-in/check-out and attendance marking for events
- **Conflict Detection**: Automatic detection of scheduling conflicts
- **Event Reminders**: Integration with notification system for automated reminders

### Technical Implementation
- Database tables for calendars, events, registrations, and resource bookings
- Integration with existing user authentication and permissions
- API endpoints for calendar operations
- Background job processing for batch operations

## Database Schema

### Calendars Table
- `id`: UUID primary key
- `name`: Calendar name
- `description`: Calendar description
- `color`: Calendar color in hex format
- `type`: Calendar type (academic, staff, student, etc.)
- `is_public`: Whether the calendar is public
- `permissions`: JSON for role-based permissions
- `created_by`: User who created the calendar
- `updated_by`: User who last updated the calendar

### Calendar Events Table
- `id`: UUID primary key
- `calendar_id`: Foreign key to calendars table
- `title`: Event title
- `description`: Event description
- `start_date`: Event start date and time
- `end_date`: Event end date and time
- `location`: Event location
- `category`: Event category (event, holiday, exam, meeting, etc.)
- `priority`: Event priority (low, medium, high, critical)
- `is_all_day`: Whether the event is all-day
- `is_recurring`: Whether the event recurs
- `recurrence_pattern`: JSON for recurrence rules
- `recurrence_end_date`: When recurrence ends
- `max_attendees`: Maximum number of attendees
- `requires_registration`: Whether registration is required
- `registration_deadline`: Registration deadline
- `metadata`: Additional event data in JSON

### Calendar Event Registrations Table
- `id`: UUID primary key
- `event_id`: Foreign key to calendar_events table
- `user_id`: Foreign key to users table
- `status`: Registration status (registered, confirmed, cancelled, attended)
- `registration_date`: When the user registered
- `confirmation_date`: When the registration was confirmed
- `additional_data`: Additional registration data in JSON

### Calendar Shares Table
- `id`: UUID primary key
- `calendar_id`: Foreign key to calendars table
- `user_id`: Foreign key to users table
- `permission_type`: Permission type (view, edit, admin)
- `expires_at`: When the share expires

### Resource Bookings Table
- `id`: UUID primary key
- `resource_type`: Type of resource (room, equipment, facility)
- `resource_id`: ID of actual resource
- `event_id`: Foreign key to calendar_events table (nullable)
- `booked_by`: User who made booking
- `start_time`: Booking start time
- `end_time`: Booking end time
- `purpose`: Purpose of booking
- `status`: Booking status (pending, confirmed, cancelled, completed)
- `booking_data`: Additional booking information in JSON

### Academic Terms Table
- `id`: UUID primary key
- `name`: Term name
- `academic_year`: Academic year
- `term_number`: Term number (1, 2, 3, etc.)
- `start_date`: Term start date
- `end_date`: Term end date
- `is_current`: Whether this is the current active term
- `is_enrollment_open`: Whether enrollment is open for this term
- `notes`: Additional term information
- `created_by`: User who created the term
- `updated_by`: User who last updated the term

### Holidays Table
- `id`: UUID primary key
- `academic_term_id`: Foreign key to academic_terms table (nullable)
- `name`: Holiday name
- `start_date`: Holiday start date
- `end_date`: Holiday end date
- `type`: Holiday type (public, staff, school, etc.)
- `is_school_wide`: Whether holiday applies to entire school
- `description`: Holiday description
- `created_by`: User who created the holiday
- `updated_by`: User who last updated the holiday

### Event Attendance Table
- `id`: UUID primary key
- `event_id`: Foreign key to calendar_events table
- `user_id`: Foreign key to users table
- `check_in_time`: When user checked in to event
- `check_out_time`: When user checked out from event
- `status`: Attendance status (present, absent, late, not_attended)
- `notes`: Additional attendance notes
- `additional_data`: Additional attendance data in JSON

## API Endpoints

### Calendar Management
- `POST /api/calendar/calendars` - Create a new calendar
- `GET /api/calendar/calendars/{id}` - Get calendar by ID
- `PUT /api/calendar/calendars/{id}` - Update calendar
- `DELETE /api/calendar/calendars/{id}` - Delete calendar

### Event Management
- `POST /api/calendar/events` - Create a new event
- `GET /api/calendar/events/{id}` - Get event by ID
- `PUT /api/calendar/events/{id}` - Update event
- `DELETE /api/calendar/events/{id}` - Delete event
- `GET /api/calendar/calendars/{calendarId}/events` - Get events by date range

### Event Registration
- `POST /api/calendar/events/{eventId}/register` - Register for an event

### Calendar Sharing
- `POST /api/calendar/calendars/{calendarId}/share` - Share calendar with user

### Resource Booking
- `POST /api/calendar/resources/book` - Book a resource

### Academic Term Management
- `POST /api/calendar/academic-terms` - Create a new academic term
- `GET /api/calendar/academic-terms` - Get all academic terms
- `GET /api/calendar/academic-terms/current` - Get current active academic term
- `GET /api/calendar/academic-terms/{id}` - Get academic term by ID
- `PUT /api/calendar/academic-terms/{id}` - Update academic term
- `DELETE /api/calendar/academic-terms/{id}` - Delete academic term

### Holiday Management
- `POST /api/calendar/holidays` - Create a new holiday
- `GET /api/calendar/holidays` - Get holidays by date range
- `GET /api/calendar/holidays/upcoming` - Get upcoming holidays
- `GET /api/calendar/holidays/{id}` - Get holiday by ID
- `PUT /api/calendar/holidays/{id}` - Update holiday
- `DELETE /api/calendar/holidays/{id}` - Delete holiday

### Event Attendance
- `POST /api/calendar/events/{eventId}/checkin` - Check in to event
- `POST /api/calendar/events/{eventId}/checkout` - Check out from event
- `POST /api/calendar/events/{eventId}/attendance` - Mark event attendance
- `GET /api/calendar/events/{eventId}/attendance` - Get event attendance records
- `GET /api/calendar/events/{eventId}/attendance-stats` - Get attendance statistics

## Usage Examples

### Creating a Calendar
```json
{
    "name": "Academic Calendar",
    "description": "Official academic calendar for the school year",
    "color": "#3b82f6",
    "type": "academic",
    "is_public": true
}
```

### Creating an Event
```json
{
    "calendar_id": "uuid-of-calendar",
    "title": "Midterm Exams",
    "description": "Midterm examinations for all classes",
    "start_date": "2023-10-15 08:00:00",
    "end_date": "2023-10-20 17:00:00",
    "location": "Main Building",
    "category": "exam",
    "priority": "high",
    "is_all_day": false,
    "requires_registration": false
}
```

### Booking a Resource
```json
{
    "resource_type": "room",
    "resource_id": "room-101",
    "start_time": "2023-10-15 09:00:00",
    "end_time": "2023-10-15 11:00:00",
    "purpose": "Math class",
    "status": "confirmed"
}
```

## Integration Points

### With Notification System
- Event reminders sent via the notification system
- Registration confirmations and updates
- Calendar sharing notifications

### With User Authentication
- Role-based access control for calendars
- User-specific event views
- Permission management

### With School Management
- Integration with student and teacher data
- Academic calendar alignment with school terms
- Attendance tracking for events

## Security Considerations

- Access control based on user roles and calendar permissions
- Data validation for all input fields
- Protection against scheduling conflicts
- Secure handling of user registration data

## Performance Considerations

- Efficient querying for calendar views
- Caching of recurring event instances
- Optimized conflict detection algorithms
- Scalable to handle thousands of events