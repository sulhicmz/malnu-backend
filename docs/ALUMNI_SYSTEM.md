# Alumni Network and Tracking System

## Overview

The Alumni Network and Tracking System provides comprehensive capabilities for managing relationships with graduates, career tracking, mentorship programs, fundraising, and alumni events. This system helps schools maintain long-term connections with alumni and leverage their success for current students.

## Features

### 1. Alumni Profiles

Manage comprehensive graduate profiles with education and career information.

#### Endpoints

- `POST /api/alumni/profiles` - Create alumni profile
- `GET /api/alumni/profiles/{id}` - Get alumni profile by ID
- `PUT /api/alumni/profiles/{id}` - Update alumni profile
- `DELETE /api/alumni/profiles/{id}` - Delete alumni profile

#### Profile Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `student_id` | uuid | Yes | Link to graduated student record |
| `user_id` | uuid | Yes | Link to user account for alumni portal access |
| `graduation_year` | string | Yes | Year of graduation (format: YYYY) |
| `degree` | string | No | Degree obtained (e.g., Bachelor of Science) |
| `field_of_study` | string | No | Major/field of study |
| `bio` | text | No | Professional biography |
| `public_profile` | boolean | No | Whether profile is publicly visible in directory |
| `allow_contact` | boolean | No | Whether alumni allow others to contact them |
| `privacy_consent` | boolean | No | Privacy consent for data sharing |

#### Example Request

```bash
curl -X POST http://localhost:8000/api/alumni/profiles \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "<student-uuid>",
    "user_id": "<user-uuid>",
    "graduation_year": "2020",
    "degree": "Bachelor of Science in Computer Science",
    "field_of_study": "Computer Science",
    "bio": "Software engineer at Tech Corp with 5 years experience",
    "public_profile": true,
    "allow_contact": true,
    "privacy_consent": true
  }'
```

### 2. Career Tracking

Track alumni employment history and career progression.

#### Endpoints

- `POST /api/alumni/profiles/{id}/careers` - Add career to alumni profile
- `PUT /api/alumni/careers/{id}` - Update career
- `DELETE /api/alumni/careers/{id}` - Delete career

#### Career Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `company` | string | No | Company name |
| `position` | string | No | Job title/position |
| `industry` | string | No | Industry sector |
| `start_date` | date | No | Employment start date |
| `end_date` | date | No | Employment end date (null if current) |
| `current_job` | boolean | No | Whether this is current employment |
| `location` | string | No | Job location |
| `description` | text | No | Job description/responsibilities |

### 3. Achievements

Showcase alumni achievements and success stories.

#### Endpoints

- `POST /api/alumni/profiles/{id}/achievements` - Add achievement
- `PUT /api/alumni/achievements/{id}` - Update achievement
- `DELETE /api/alumni/achievements/{id}` - Delete achievement

#### Achievement Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `achievement_type` | string | Yes | Type of achievement (award, publication, milestone, etc.) |
| `title` | string | Yes | Achievement title |
| `description` | text | No | Achievement description |
| `achievement_date` | date | No | Date of achievement |
| `link` | string | No | URL to more information (news article, publication, etc.) |

### 4. Mentorship Program

Connect alumni with current students for career guidance and mentorship.

#### Endpoints

- `POST /api/alumni/mentorships` - Create mentorship match
- `PUT /api/alumni/mentorships/{id}` - Update mentorship status
- `GET /api/alumni/profiles/{id}/mentorships` - Get alumni's mentorships
- `GET /api/alumni/mentorships/student/{id}` - Get student's mentorships

#### Mentorship Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `alumni_id` | uuid | Yes | Alumni acting as mentor |
| `student_id` | uuid | Yes | Student being mentored |
| `status` | string | Yes | Status: pending, active, completed |
| `focus_area` | string | No | Area of mentorship (career guidance, academic support) |
| `start_date` | date | No | Mentorship start date |
| `end_date` | date | No | Mentorship end date |
| `notes` | text | No | Notes about mentorship progress |

#### Mentorship Status

- **pending** - Mentorship request pending student/parent acceptance
- **active** - Active mentorship in progress
- **completed** - Mentorship completed successfully
- **cancelled** - Mentorship cancelled

### 5. Donation Tracking

Track alumni contributions, campaigns, and recognition.

#### Endpoints

- `POST /api/alumni/profiles/{id}/donations` - Record donation
- `GET /api/alumni/profiles/{id}/donations` - Get alumni donations

#### Donation Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `amount` | decimal | Yes | Donation amount |
| `currency` | string | No | Currency code (default: USD) |
| `donation_type` | string | Yes | Donation type (annual fund, capital campaign, etc.) |
| `campaign` | string | No | Associated fundraising campaign |
| `anonymous` | boolean | No | Whether donation is anonymous |
| `acknowledged` | boolean | No | Whether donation has been acknowledged |
| `message` | text | No | Donor message or dedication |

### 6. Events and Reunions

Organize alumni events, reunions, and networking opportunities.

#### Endpoints

- `POST /api/alumni/events` - Create alumni event
- `GET /api/alumni/events/{id}` - Get event by ID
- `PUT /api/alumni/events/{id}` - Update event
- `DELETE /api/alumni/events/{id}` - Delete event
- `GET /api/alumni/events/upcoming` - Get upcoming events

#### Event Fields

| Field | Type | Required | Description |
|--------|------|-----------|-------------|
| `created_by` | uuid | Yes | User who created the event |
| `title` | string | Yes | Event title |
| `description` | text | No | Event description |
| `event_type` | string | Yes | Event type (reunion, networking, workshop) |
| `location` | string | No | Event location |
| `event_date` | datetime | Yes | Event date and time |
| `max_attendees` | integer | No | Maximum attendees |
| `current_attendees` | integer | No | Current registered attendees |
| `status` | string | No | Status: upcoming, ongoing, completed, cancelled |

#### Event Registration

- `POST /api/alumni/events/{id}/register` - Register for event
- `PUT /api/alumni/registrations/{id}` - Update registration
- `POST /api/alumni/registrations/{id}/cancel` - Cancel registration

#### Registration Fields

| Field | Type | Description |
|--------|------|-------------|
| `attendance_status` | enum | Status: registered, attended, cancelled, no_show |
| `registration_time` | timestamp | When registration was made |
| `notes` | text | Notes about registration |

### 7. Statistics and Analytics

Get comprehensive analytics on alumni engagement.

#### Endpoint

- `GET /api/alumni/statistics` - Get alumni statistics

#### Statistics Response

```json
{
  "total_alumni": 1250,
  "public_profiles": 850,
  "active_mentorships": 45,
  "total_donations": 125000.00,
  "upcoming_events": 3
}
```

## Directory and Filtering

The alumni directory provides advanced search and filtering capabilities:

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `public_only` | boolean | Filter to only show public profiles |
| `allow_contact` | boolean | Filter to only show alumni who allow contact |
| `graduation_year` | string | Filter by graduation year |
| `field_of_study` | string | Filter by field of study (partial match) |

#### Example Directory Query

```bash
curl "http://localhost:8000/api/alumni/directory?public_only=true&graduation_year=2020&field_of_study=computer" \
  -H "Authorization: Bearer <token>"
```

## Privacy and Consent Management

The system includes comprehensive privacy controls:

### Profile Visibility

- **public_profile**: Controls whether profile appears in public alumni directory
- **allow_contact**: Controls whether other alumni can contact this alumnus

### Consent Tracking

- **privacy_consent**: Records user's consent for data sharing and communications
- Required for certain features (mentorship, contact information sharing)

### Privacy Best Practices

1. Always obtain explicit consent before sharing contact information
2. Allow alumni to control visibility of their profiles
3. Provide options to opt-out of communications
4. Comply with data protection regulations (GDPR, FERPA)

## Integration Points

The alumni system integrates with existing systems:

### Student Information System (Issue #229)

- Alumni profiles link to graduated student records
- Graduation information and academic history
- Enables tracking alumni from student records

### Notification System (Issue #257)

- Event reminders and registration confirmations
- Donation acknowledgment notifications
- Mentorship program updates

### Fee Management (Issue #200)

- Donation processing and receipt generation
- Fundraising campaign tracking
- Financial reporting

### User Authentication

- Alumni portal access through user accounts
- JWT-based authentication for all endpoints
- Role-based access control

## API Response Format

All API endpoints follow standard response format:

### Success Response

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "data": null
}
```

### Common Error Codes

| Error Code | Description |
|------------|-------------|
| `ALUMNI_NOT_FOUND` | Alumni profile not found |
| `INVALID_GRADUATION_YEAR` | Graduation year must be valid |
| `STUDENT_NOT_FOUND` | Student record not found |
| `USER_NOT_FOUND` | User account not found |
| `PERMISSION_DENIED` | Insufficient permissions |
| `VALIDATION_ERROR` | Request validation failed |

## Security Considerations

1. **Authentication**: All endpoints require JWT authentication except public routes
2. **Authorization**: Role-based access control for write operations
3. **Privacy**: Respects privacy consent settings
4. **Data Encryption**: Sensitive data encrypted in transit and at rest
5. **Audit Logging**: All operations logged for compliance and security

## Rate Limiting

All endpoints are protected with rate limiting to prevent abuse:

- Default: 60 requests per minute per IP
- Public endpoints: Higher limits for legitimate access
- Write operations: Stricter limits to prevent spam

## Testing

Run the alumni management tests:

```bash
vendor/bin/co-phpunit tests/Feature/AlumniManagementTest.php
```

## Best Practices

### For Administrators

1. Verify alumni consent before publishing profiles
2. Keep achievement information current and verified
3. Respond to donation acknowledgments promptly
4. Monitor mentorship program for student safety
5. Regular data quality checks and updates

### For Alumni

1. Keep profile information current
2. Report career updates and achievements
3. Engage with mentorship program positively
4. Manage privacy preferences
5. Report contact information changes

### For Students

1. Respect mentor boundaries and time commitments
2. Set clear goals for mentorship relationships
3. Communicate regularly with mentors
4. Be professional in all interactions

## Future Enhancements

The current implementation provides a solid foundation for alumni management. Future enhancements could include:

1. **LinkedIn Integration**: Automatic profile syncing with LinkedIn
2. **Advanced Mentorship Matching**: AI-powered compatibility matching
3. **Alumni Portal**: Self-service web portal for alumni
4. **Mobile App**: Native mobile app for alumni engagement
5. **Video Networking**: Virtual event and reunion capabilities
6. **Job Board**: Alumni job posting and referral system
7. **Chapter Management**: Regional alumni chapter organization

## Troubleshooting

### Common Issues

**Issue**: Alumni profile not visible in directory
- **Solution**: Check `public_profile` and `privacy_consent` settings

**Issue**: Cannot register for event
- **Solution**: Ensure you have a valid alumni profile with privacy consent

**Issue**: Donation not recorded
- **Solution**: Verify payment processing and check donation amount format

**Issue**: Mentorship not visible to students
- **Solution**: Check mentorship status and ensure status is `active`

### Support

For issues or questions about the alumni management system:
1. Check API documentation
2. Review system logs in `storage/logs/`
3. Contact system administrators
4. Review this documentation for common solutions
