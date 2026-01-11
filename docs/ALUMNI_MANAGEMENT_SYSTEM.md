# Alumni Management System

## Overview

The Alumni Management System provides comprehensive tools for managing relationships with school graduates, tracking their achievements, fostering networking, and managing alumni engagement programs including mentorship, donations, and events.

## Features

### 1. Alumni Profiles
- Comprehensive graduate profiles with education and career information
- Graduation year, class, degree, and field of study tracking
- Current employment and industry information
- Professional bio and achievements documentation
- LinkedIn profile integration
- Public/Private profile settings
- Contact preferences and privacy controls
- Mentor availability status

### 2. Career Tracking
- Complete employment history with company, position, and industry
- Start and end date tracking
- Current position indicator
- Career achievements documentation
- Job description and responsibilities

### 3. Donation Management
- Alumni donation tracking and recording
- Multiple donation types (one-time, recurring, campaign-specific)
- Campaign-based donation management
- Payment method and transaction ID tracking
- Anonymous donation support
- Donation receipts and confirmation
- Donation status tracking (pending, completed, failed)

### 4. Event Management
- Alumni event creation and management
- Event types: reunions, networking, meetups, fundraising
- Virtual and in-person event support
- Event capacity management
- Event registration system
- Attendee check-in functionality
- Event analytics and reporting

### 5. Event Registration
- Alumni event registration with RSVP
- Guest support
- Dietary requirements and special requests
- Registration confirmation and cancellation
- Waitlist management for full events

### 6. Mentorship Program
- Alumni-student mentorship matching
- Focus area and goal setting
- Session tracking and progress monitoring
- Mentorship status management (pending, active, completed)
- Notes and feedback documentation
- Available mentors directory

### 7. Engagement Tracking
- Record all alumni interactions and engagements
- Categorize engagements by type and category
- Date-based engagement tracking
- Engagement analytics and reporting
- Identify highly engaged alumni

### 8. Privacy and Consent Management
- GDPR-compliant privacy controls
- Consent management for data sharing
- Opt-out mechanisms for communications
- Profile visibility settings
- Contact preference management

## API Endpoints

### Alumni Profiles

#### Create Alumni Profile
```
POST /api/alumni/profiles
```

**Request Body:**
```json
{
  "student_id": "string (required)",
  "user_id": "string (required)",
  "graduation_year": "integer (required)",
  "graduation_class": "string",
  "degree": "string",
  "field_of_study": "string",
  "current_company": "string",
  "current_position": "string",
  "industry": "string",
  "linkedin_url": "string",
  "bio": "text",
  "achievements": "text",
  "is_public": "boolean",
  "allow_contact": "boolean",
  "newsletter_subscription": "boolean",
  "mentor_availability": "boolean"
}
```

#### Get All Alumni
```
GET /api/alumni/profiles
```

**Query Parameters:**
- `is_public` (boolean): Filter by public profile status
- `is_verified` (boolean): Filter by verification status
- `industry` (string): Filter by industry
- `graduation_year` (integer): Filter by graduation year
- `search` (string): Search by name, email, company, position
- `per_page` (integer): Items per page (default: 20)

#### Get Single Alumni
```
GET /api/alumni/profiles/{id}
```

#### Update Alumni Profile
```
PUT /api/alumni/profiles/{id}
```

#### Delete Alumni Profile
```
DELETE /api/alumni/profiles/{id}
```

#### Verify Alumni
```
POST /api/alumni/profiles/{id}/verify
```

#### Update Privacy Settings
```
PUT /api/alumni/profiles/{id}/privacy
```

**Request Body:**
```json
{
  "is_public": "boolean",
  "allow_contact": "boolean",
  "newsletter_subscription": "boolean",
  "mentor_availability": "boolean",
  "privacy_settings": "object"
}
```

### Career Management

#### Create Career Entry
```
POST /api/alumni/careers
```

**Request Body:**
```json
{
  "alumni_id": "string (required)",
  "company_name": "string (required)",
  "position": "string (required)",
  "industry": "string",
  "start_date": "date (required)",
  "end_date": "date",
  "is_current": "boolean",
  "description": "text",
  "achievements": "text"
}
```

#### Update Career Entry
```
PUT /api/alumni/careers/{id}
```

#### Delete Career Entry
```
DELETE /api/alumni/careers/{id}
```

### Donation Management

#### Create Donation
```
POST /api/alumni/donations
```

**Request Body:**
```json
{
  "alumni_id": "string",
  "donor_name": "string",
  "email": "string",
  "phone": "string",
  "amount": "decimal (required)",
  "currency": "string",
  "donation_type": "string (required)",
  "campaign": "string",
  "is_recurring": "boolean",
  "recurring_frequency": "string",
  "donation_date": "date (required)",
  "payment_method": "text",
  "transaction_id": "string",
  "is_anonymous": "boolean",
  "message": "text",
  "status": "string"
}
```

#### Get Donations
```
GET /api/alumni/donations
```

**Query Parameters:**
- `alumni_id` (string): Filter by alumni
- `status` (string): Filter by status
- `campaign` (string): Filter by campaign
- `donation_type` (string): Filter by donation type
- `per_page` (integer): Items per page

### Event Management

#### Create Event
```
POST /api/alumni/events
```

**Request Body:**
```json
{
  "name": "string (required)",
  "description": "text",
  "event_type": "string (required)",
  "event_date": "datetime (required)",
  "end_date": "datetime",
  "location": "string",
  "virtual_link": "string",
  "is_virtual": "boolean",
  "max_capacity": "integer",
  "status": "string",
  "image_url": "text",
  "organizer_name": "string",
  "contact_email": "text"
}
```

#### Get Events
```
GET /api/alumni/events
```

**Query Parameters:**
- `status` (string): Filter by status (upcoming, past, completed)
- `event_type` (string): Filter by event type
- `upcoming` (boolean): Get upcoming events only
- `past` (boolean): Get past events only
- `order` (string): Order direction (asc/desc, default: asc)
- `per_page` (integer): Items per page

#### Update Event
```
PUT /api/alumni/events/{id}
```

#### Delete Event
```
DELETE /api/alumni/events/{id}
```

### Event Registration

#### Register for Event
```
POST /api/alumni/event-registrations
```

**Request Body:**
```json
{
  "event_id": "string (required)",
  "alumni_id": "string",
  "name": "string (required)",
  "email": "string (required)",
  "phone": "string",
  "guests": "integer",
  "dietary_requirements": "text",
  "special_requests": "text"
}
```

#### Cancel Registration
```
DELETE /api/alumni/event-registrations/{id}
```

#### Check In Attendee
```
POST /api/alumni/event-registrations/{id}/check-in
```

### Mentorship Management

#### Create Mentorship
```
POST /api/alumni/mentorships
```

**Request Body:**
```json
{
  "mentor_id": "string (required)",
  "student_id": "string",
  "mentee_name": "string",
  "mentee_email": "string",
  "status": "string",
  "focus_area": "string",
  "goals": "text",
  "start_date": "date",
  "end_date": "date",
  "sessions_count": "integer",
  "notes": "array",
  "feedback": "array",
  "match_criteria": "array"
}
```

#### Get Mentorships
```
GET /api/alumni/mentorships
```

**Query Parameters:**
- `mentor_id` (string): Filter by mentor
- `student_id` (string): Filter by student
- `status` (string): Filter by status
- `focus_area` (string): Filter by focus area
- `per_page` (integer): Items per page

#### Update Mentorship
```
PUT /api/alumni/mentorships/{id}
```

#### Activate Mentorship
```
POST /api/alumni/mentorships/{id}/activate
```

#### Complete Mentorship
```
POST /api/alumni/mentorships/{id}/complete
```

#### Find Available Mentors
```
GET /api/alumni/available-mentors
```

**Query Parameters:**
- `industry` (string): Filter by industry
- `field_of_study` (string): Filter by field of study

### Engagement Tracking

#### Create Engagement
```
POST /api/alumni/engagements
```

**Request Body:**
```json
{
  "alumni_id": "string (required)",
  "engagement_type": "string (required)",
  "description": "text",
  "engagement_date": "datetime (required)",
  "category": "string",
  "details": "array"
}
```

#### Get Engagements
```
GET /api/alumni/engagements
```

**Query Parameters:**
- `alumni_id` (string): Filter by alumni
- `engagement_type` (string): Filter by engagement type
- `category` (string): Filter by category
- `year` (integer): Filter by year
- `per_page` (integer): Items per page

### Reports

#### Get Engagement Report
```
GET /api/alumni/reports/engagement
```

**Query Parameters:**
- `start_date` (date): Start date filter
- `end_date` (date): End date filter
- `year` (integer): Year filter

**Response:**
```json
{
  "success": true,
  "data": {
    "total_engagements": "integer",
    "by_type": "object",
    "by_category": "object",
    "by_month": "object"
  }
}
```

#### Get Donation Report
```
GET /api/alumni/reports/donation
```

**Query Parameters:**
- `start_date` (date): Start date filter
- `end_date` (date): End date filter
- `campaign` (string): Campaign filter

**Response:**
```json
{
  "success": true,
  "data": {
    "total_donations": "integer",
    "total_amount": "decimal",
    "average_donation": "decimal",
    "by_campaign": "object",
    "by_type": "object",
    "recurring_count": "integer"
  }
}
```

### Directory

#### Get Alumni Directory
```
GET /api/alumni/directory
```

**Query Parameters:**
- `industry` (string): Filter by industry
- `graduation_year` (integer): Filter by graduation year
- `search` (string): Search by name, email, company
- `per_page` (integer): Items per page

**Note:** Returns only public alumni profiles with verification status.

## Database Schema

### alumni
- `id` (uuid, primary key)
- `student_id` (string, foreign key to students)
- `user_id` (string, foreign key to users)
- `graduation_year` (integer)
- `graduation_class` (string)
- `degree` (string)
- `field_of_study` (string)
- `current_company` (text)
- `current_position` (string)
- `industry` (string)
- `linkedin_url` (text)
- `bio` (text)
- `achievements` (text)
- `is_verified` (boolean)
- `is_public` (boolean)
- `allow_contact` (boolean)
- `newsletter_subscription` (boolean)
- `mentor_availability` (boolean)
- `privacy_settings` (json)
- `consent_data` (json)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_careers
- `id` (uuid, primary key)
- `alumni_id` (string, foreign key to alumni)
- `company_name` (string)
- `position` (string)
- `industry` (string)
- `start_date` (date)
- `end_date` (date, nullable)
- `is_current` (boolean)
- `description` (text)
- `achievements` (text)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_donations
- `id` (uuid, primary key)
- `alumni_id` (string, foreign key to alumni, nullable)
- `donor_name` (string)
- `email` (string)
- `phone` (string)
- `amount` (decimal 10,2)
- `currency` (string, default: USD)
- `donation_type` (string)
- `campaign` (string)
- `is_recurring` (boolean)
- `recurring_frequency` (string)
- `donation_date` (date)
- `payment_method` (text)
- `transaction_id` (string)
- `is_anonymous` (boolean)
- `message` (text)
- `status` (string)
- `receipt_details` (json)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_events
- `id` (uuid, primary key)
- `name` (string)
- `slug` (string, unique)
- `description` (text)
- `event_type` (string)
- `event_date` (datetime)
- `end_date` (datetime, nullable)
- `location` (string)
- `virtual_link` (string)
- `is_virtual` (boolean)
- `max_capacity` (integer, nullable)
- `current_attendees` (integer, default: 0)
- `status` (string)
- `image_url` (text)
- `organizer_name` (string)
- `contact_email` (text)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_event_registrations
- `id` (uuid, primary key)
- `event_id` (string, foreign key to alumni_events)
- `alumni_id` (string, foreign key to alumni, nullable)
- `name` (string)
- `email` (string)
- `phone` (string)
- `guests` (integer, default: 0)
- `is_attending` (boolean, default: true)
- `dietary_requirements` (text)
- `special_requests` (text)
- `registration_date` (datetime)
- `check_in_status` (boolean, default: false)
- `check_in_time` (datetime, nullable)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_engagements
- `id` (uuid, primary key)
- `alumni_id` (string, foreign key to alumni)
- `engagement_type` (string)
- `description` (text)
- `engagement_date` (datetime)
- `category` (string)
- `details` (json)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

### alumni_mentorships
- `id` (uuid, primary key)
- `mentor_id` (string, foreign key to alumni)
- `student_id` (string, foreign key to students, nullable)
- `mentee_name` (string)
- `mentee_email` (string)
- `status` (string)
- `focus_area` (string)
- `goals` (text)
- `start_date` (date)
- `end_date` (date)
- `sessions_count` (integer, default: 0)
- `notes` (json)
- `feedback` (json)
- `match_criteria` (json)
- `created_by` (uuid)
- `updated_by` (uuid)
- `created_at` (datetime)
- `updated_at` (datetime)
- `deleted_at` (datetime, nullable)

## Security and Privacy

### GDPR Compliance
- All alumni data access requires JWT authentication
- Privacy controls for personal information display
- Consent management for communications
- Opt-out mechanisms for newsletters and marketing
- Right to be forgotten (soft delete with anonymization)

### Data Protection
- Role-based access control for sensitive data
- Audit logging for all profile access and modifications
- Encrypted data storage for sensitive information
- Secure data transmission via HTTPS

### Access Control
- Public profiles accessible to authenticated users
- Private profiles only accessible to admins and the alumni themselves
- Donation data restricted to admins and finance team
- Mentorship information accessible to mentors, students, and admins

## Integration Guide

### Student Information System
Alumni profiles are linked to Student records via `student_id` field:
- Access academic history and graduation details
- Link alumni achievements back to student records
- Track alumni from graduation to current status

### Notification System
Integrate with notification system (Issue #257) for:
- Event reminders and updates
- Donation confirmations and receipts
- Mentorship session reminders
- Newsletter and announcement distribution
- Re-engagement campaigns

### Fee Management
Integrate with fee management system (Issue #200) for:
- Process alumni donations as payments
- Generate donation receipts
- Track donation campaigns and goals
- Recurring donation billing

### Authentication
All alumni management endpoints require JWT authentication:
- Use existing JWT middleware (`app/Http/Middleware/JWTMiddleware`)
- Token-based access control
- Role-based permissions can be added as needed

## Best Practices

### For Administrators
1. **Profile Verification**: Verify alumni profiles before granting public visibility
2. **Event Planning**: Set appropriate capacity limits for in-person events
3. **Donation Acknowledgement**: Send automated thank-you messages for donations
4. **Mentorship Matching**: Match mentors with students based on industry, experience, and goals
5. **Regular Engagement**: Schedule regular communication and events to maintain alumni engagement

### For Alumni
1. **Profile Completeness**: Encourage alumni to complete their profiles with current information
2. **Privacy Settings**: Review and update privacy preferences regularly
3. **Event Participation**: Encourage participation in alumni events and networking
4. **Mentorship**: Offer mentorship opportunities to experienced alumni
5. **Donation Engagement**: Provide multiple donation options and campaign information

### Data Management
1. **Regular Cleanup**: Archive old event registrations and completed mentorships
2. **Data Quality**: Regularly validate alumni information and update outdated records
3. **Backup**: Ensure regular backups of alumni data as part of disaster recovery
4. **Analytics Review**: Review engagement and donation reports quarterly
5. **Consent Management**: Regularly update consent records and respect opt-out requests

## Troubleshooting

### Common Issues

#### Event Registration Fails
**Problem**: Alumni unable to register for event
**Solution**: Check if event is fully booked (`current_attendees >= max_capacity`)

#### Mentorship Status Not Updating
**Problem**: Mentorship status not changing from pending to active
**Solution**: Ensure activate endpoint is called and mentor has necessary permissions

#### Donation Processing Fails
**Problem**: Donation records not being created
**Solution**: Verify required fields (amount, donation_type, donation_date) are provided

#### Directory Not Showing Alumni
**Problem**: Alumni directory returns empty results
**Solution**: Check that alumni profiles have `is_public` and `is_verified` set to true

### Performance Considerations
- Use pagination for large alumni lists
- Cache frequently accessed alumni profiles
- Index alumni data by industry and graduation year for faster searches
- Optimize database queries with proper eager loading (with relationships)

## Future Enhancements

### Planned Features
1. **Advanced Mentorship Matching**: AI-powered mentor-student matching based on interests, skills, and goals
2. **Social Network Integration**: Direct LinkedIn and social media integration for alumni networking
3. **Professional Development**: Webinar and workshop offerings for alumni
4. **Job Board**: Alumni job posting and career opportunities board
5. **Giving Levels**: Tiered recognition programs for high-value donors
6. **Mobile App**: Dedicated mobile app for alumni engagement
7. **Advanced Analytics**: Predictive analytics for engagement and donation patterns
8. **Alumni Chapters**: Regional or interest-based alumni chapter management

### Integration Opportunities
1. **CRM Systems**: Integration with external CRM for alumni relationship management
2. **Payment Gateways**: Multiple payment gateway integrations for donations
3. **Email Marketing**: Integration with email marketing platforms for newsletters
4. **Survey Tools**: Alumni satisfaction surveys and feedback collection
5. **Event Ticketing**: Advanced ticketing system for paid alumni events

## Support

For issues or questions related to the Alumni Management System:
1. Check this documentation for common solutions
2. Review API response messages for specific error details
3. Contact the development team for technical support
4. Submit feature requests through the project issue tracker
