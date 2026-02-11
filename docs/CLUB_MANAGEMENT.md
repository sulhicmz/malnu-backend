# Club and Extracurricular Activity Management System

## Overview

The Club and Extracurricular Activity Management System provides a comprehensive solution for schools to manage student clubs, activities, memberships, and advisor assignments. This system enables schools to track student participation, manage club operations, schedule activities, and monitor student engagement.

## Features

- **Club Management**: Create, update, and delete school clubs with categories (academic, sports, arts, community service, other)
- **Activity Management**: Schedule and manage club activities and events with capacity limits
- **Membership Management**: Track student participation with role-based access (member, officer, president, vice-president)
- **Advisor Assignment**: Assign teachers as club advisors with tracking
- **Attendance Tracking**: Record activity participation with status tracking
- **Capacity Checking**: Monitor club membership limits

## API Endpoints

### Clubs

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| GET | `/api/clubs` | List all clubs | Yes |
| POST | `/api/clubs` | Create a new club | Admin |
| GET | `/api/clubs/{id}` | Get club details | Yes |
| PUT | `/api/clubs/{id}` | Update club | Admin |
| DELETE | `/api/clubs/{id}` | Delete club | Admin |

### Activities

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| GET | `/api/activities` | List all activities | Yes |
| POST | `/api/activities` | Create a new activity | Admin |
| GET | `/api/activities/{id}` | Get activity details | Yes |
| PUT | `/api/activities/{id}` | Update activity | Admin |
| DELETE | `/api/activities/{id}` | Delete activity | Admin |

### Club Memberships

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| GET | `/api/club-memberships` | List all memberships | Yes |
| POST | `/api/club-memberships` | Add a member to a club | Admin |
| GET | `/api/club-memberships/{id}` | Get membership details | Yes |
| PUT | `/api/club-memberships/{id}` | Update member role | Admin |
| DELETE | `/api/club-memberships/{id}` | Remove a member | Admin |

### Club Advisors

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|----------------|
| GET | `/api/club-advisors` | List all advisors | Yes |
| POST | `/api/club-advisors` | Assign an advisor to a club | Admin |
| GET | `/api/club-advisors/{id}` | Get advisor details | Yes |
| DELETE | `/api/club-advisors/{id}` | Remove an advisor | Admin |

## Data Models

### Club

```php
{
  "id": "uuid",
  "name": "Math Club",
  "description": "A club for mathematics enthusiasts",
  "category": "academic",
  "max_members": 30,
  "advisor_id": "uuid",
  "status": "active",
  "created_at": "2026-01-18T10:00:00Z",
  "updated_at": "2026-01-18T10:00:00Z"
}
```

### Activity

```php
{
  "id": "uuid",
  "club_id": "uuid",
  "name": "Robotics Competition",
  "description": "Annual robotics competition",
  "start_date": "2026-02-15T14:00:00Z",
  "end_date": "2026-02-15T18:00:00Z",
  "location": "School Hall",
  "max_attendees": 50,
  "created_at": "2026-01-18T10:00:00Z",
  "updated_at": "2026-01-18T10:00:00Z"
}
```

### Club Membership

```php
{
  "id": "uuid",
  "club_id": "uuid",
  "student_id": "uuid",
  "role": "member",
  "joined_date": "2026-01-15",
  "created_at": "2026-01-15T10:00:00Z",
  "updated_at": "2026-01-15T10:00:00Z"
}
```

### Activity Attendance

```php
{
  "id": "uuid",
  "activity_id": "uuid",
  "student_id": "uuid",
  "status": "present",
  "notes": "Excellent participation",
  "created_at": "2026-01-15T14:30:00Z",
  "updated_at": "2026-01-15T14:30:00Z"
}
```

## Usage Examples

### Create a Club

```bash
curl -X POST http://localhost:9501/api/clubs \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {jwt_token}" \
  -d '{
    "name": "Robotics Club",
    "description": "For students interested in robotics",
    "category": "academic",
    "max_members": 20
  }'
```

### Add a Member

```bash
curl -X POST http://localhost:9501/api/club-memberships \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {jwt_token}" \
  -d '{
    "club_id": "club-uuid",
    "student_id": "student-uuid",
    "role": "officer"
  }'
```

### Schedule an Activity

```bash
curl -X POST http://localhost:9501/api/activities \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {jwt_token}" \
  -d '{
    "club_id": "club-uuid",
    "name": "Annual Robotics Competition",
    "description": "District-wide robotics competition",
    "start_date": "2026-03-15T10:00:00",
    "end_date": "2026-03-15T16:00:00",
    "location": "School Auditorium",
    "max_attendees": 100
  }'
```

### Mark Attendance

```bash
curl -X POST http://localhost:9501/api/activity-attendances \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {jwt_token}" \
  -d '{
    "activity_id": "activity-uuid",
    "student_id": "student-uuid",
    "status": "present",
    "notes": "Great performance!"
  }'
```

## Club Categories

- `academic` - Math club, science club, debate club, etc.
- `sports` - Basketball club, soccer club, swimming club, etc.
- `arts` - Drama club, art club, music club, etc.
- `community_service` - Volunteer club, community service club, etc.
- `other` - Any other type of club not covered above

## Member Roles

- `member` - Regular club member
- `officer` - Club officer with additional responsibilities
- `president` - Club president (highest role)
- `vice-president` - Vice-president of the club

## Attendance Status

- `present` - Student attended the activity
- `absent` - Student did not attend the activity
- `late` - Student arrived late
- `excused` - Student has a valid reason for not attending

## Configuration

The club management system can be configured with the following options:

### Club Categories

Categories can be customized by adding or modifying club categories in the Club model validation.

### Role Permissions

Different member roles can have different permissions:
- **president**: Can approve activities, manage budgets
- **vice-president**: Can schedule activities, manage membership
- **officer**: Can mark attendance, track participation
- **member**: Can view club information, RSVP to activities

### Capacity Limits

Clubs can have maximum member limits to ensure manageable group sizes:
- Small clubs: 10-20 members
- Medium clubs: 20-50 members
- Large clubs: 50-100 members

## Testing

Run the test suite:

```bash
vendor/bin/co-phpunit tests/Feature/ClubManagementTest.php
```

## Integration with Other Systems

The club management system integrates with:
- **Student Management**: Links club memberships to student profiles
- **Teacher Management**: Links advisors to teacher profiles
- **Calendar System**: Activities can be synced with calendar events
- **Notification System**: Activity reminders can be sent to participants

## Security Considerations

- **Role-Based Access**: Only administrators and club advisors can modify club settings
- **Student Privacy**: Students can only view clubs they are members of
- **Attendance Tracking**: Advisors can mark attendance for activities
- **Data Validation**: All inputs are validated before processing

## Migration

After deployment, run database migrations:

```bash
php artisan migrate
```

This will create the necessary tables for clubs, memberships, activities, attendances, and advisors.
