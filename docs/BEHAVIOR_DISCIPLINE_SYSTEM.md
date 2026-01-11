# Behavior and Discipline Management System

## Overview

The Behavior and Discipline Management System provides comprehensive tools for tracking student behavior, managing disciplinary actions, and supporting student development through intervention planning and positive behavior recognition.

## Features

### Incident Reporting
- Report behavioral incidents with detailed information
- Categorize incidents by type and severity
- Track incident status (reported, under investigation, resolved, closed)
- Attach evidence documentation
- Automatic parent notifications

### Discipline Management
- Record disciplinary actions taken in response to incidents
- Track action outcomes and completion status
- Assign responsibility for follow-up actions
- Maintain complete audit trail

### Intervention Planning
- Create individualized intervention plans for students
- Set improvement goals and strategies
- Track timeline and milestones
- Monitor progress and evaluate outcomes

### Positive Behavior Recognition
- Track positive behaviors and achievements
- Implement reward and recognition programs
- Celebrate improvements and successes
- Build student confidence and engagement

### Analytics and Reporting
- Generate individual student behavior reports
- Class-wide behavior trend analysis
- School-wide discipline statistics
- Chronic absenteeism and behavior pattern detection
- Intervention effectiveness tracking

## API Endpoints

### Incidents

| Method | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/behavior/incidents` | Report a new incident |
| GET | `/api/behavior/incidents` | List all incidents (with filters) |
| GET | `/api/behavior/incidents/{id}` | Get specific incident details |
| PUT | `/api/behavior/incidents/{id}` | Update incident information |

### Discipline Actions

| Method | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/behavior/discipline-actions` | Create disciplinary action |
| GET | `/api/behavior/discipline-actions` | List all disciplinary actions for incident |
| GET | `/api/behavior/discipline-actions/{id}` | Get specific action details |
| PUT | `/api/behavior/discipline-actions/{id}` | Update action status and outcome |

### Intervention Plans

| Method | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/behavior/intervention-plans` | Create intervention plan |
| GET | `/api/behavior/intervention-plans` | List all intervention plans |
| GET | `/api/behavior/intervention-plans/{id}` | Get specific plan details |
| PUT | `/api/behavior/intervention-plans/{id}` | Update plan progress and evaluation |

### Behavior Notes

| Method | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/behavior/notes` | Create behavior note |
| GET | `/api/behavior/notes` | List behavior notes (with filters) |

### Reports

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/behavior/reports` | Generate behavior reports |

### Categories

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/behavior/categories` | List behavior categories |

### Student History

| Method | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/behavior/student/{studentId}/history` | Get complete behavior history for student |

## Database Schema

### behavior_categories
- `id` (UUID, primary key)
- `name` (string, 100) - Category name
- `type` (enum: positive, negative) - Behavior type
- `description` (text, nullable) - Category description
- `severity` (enum: low, medium, high) - Default severity level
- `created_at`, `updated_at` (datetimes)

### behavior_incidents
- `id` (UUID, primary key)
- `student_id` (UUID, foreign key to students)
- `category_id` (UUID, foreign key to behavior_categories)
- `reported_by` (UUID, foreign key to users)
- `title` (string, 200) - Incident title
- `description` (text) - Detailed description
- `incident_date` (date) - Date of incident
- `incident_time` (time, nullable) - Time of incident
- `location` (string, 100, nullable) - Location
- `severity` (enum: low, medium, high) - Incident severity
- `status` (enum: reported, under_investigation, resolved, closed) - Current status
- `evidence` (text, nullable) - Evidence documentation
- `created_at`, `updated_at` (datetimes)

### discipline_actions
- `id` (UUID, primary key)
- `incident_id` (UUID, foreign key to behavior_incidents)
- `assigned_by` (UUID, foreign key to users)
- `action_type` (string, 100) - Type of action taken
- `description` (text) - Description of action
- `action_date` (date) - Date action was taken
- `status` (enum: pending, in_progress, completed, cancelled) - Action status
- `outcome` (text, nullable) - Result of action
- `created_at`, `updated_at` (datetimes)

### intervention_plans
- `id` (UUID, primary key)
- `student_id` (UUID, foreign key to students)
- `incident_id` (UUID, nullable, foreign key to behavior_incidents)
- `created_by` (UUID, foreign key to users)
- `goals` (text) - Improvement goals
- `strategies` (text) - Action strategies
- `timeline` (text) - Timeline and milestones
- `start_date` (date) - Plan start date
- `end_date` (date, nullable) - Plan end date
- `status` (enum: active, completed, cancelled) - Plan status
- `evaluation` (text, nullable) - Outcome evaluation
- `created_at`, `updated_at` (datetimes)

### behavior_notes
- `id` (UUID, primary key)
- `student_id` (UUID, foreign key to students)
- `noted_by` (UUID, foreign key to users)
- `note_type` (enum: observation, positive_incident, improvement) - Note type
- `content` (text) - Note content
- `note_date` (date) - Date of note
- `is_positive` (boolean) - Whether note records positive behavior
- `created_at`, `updated_at` (datetimes)

## Request/Response Examples

### Create Incident

```http
POST /api/behavior/incidents
Content-Type: application/json
Authorization: Bearer {jwt_token}

{
  "student_id": "uuid-here",
  "category_id": "uuid-here",
  "reported_by": "uuid-here",
  "title": "Disruption in class",
  "description": "Student disrupted classroom activities...",
  "incident_date": "2026-01-10",
  "incident_time": "09:30:00",
  "location": "Classroom 3A",
  "severity": "medium",
  "evidence": "Photos attached"
}
```

### Get Incidents with Filters

```http
GET /api/behavior/incidents?student_id=uuid&date_from=2026-01-01&date_to=2026-01-31&severity=high
Authorization: Bearer {jwt_token}
```

### Create Discipline Action

```http
POST /api/behavior/discipline-actions
Authorization: Bearer {jwt_token}

{
  "incident_id": "uuid-here",
  "assigned_to": "uuid-here",
  "action_type": "Detention",
  "description": "Student assigned detention",
  "action_date": "2026-01-11"
}
```

### Create Intervention Plan

```http
POST /api/behavior/intervention-plans
Authorization: Bearer {jwt_token}

{
  "student_id": "uuid-here",
  "incident_id": "uuid-here",
  "goals": "Improve classroom behavior",
  "strategies": "Daily check-ins, behavior chart",
  "timeline": "Week 1: Initial meeting\nWeek 2: Implement strategies",
  "start_date": "2026-01-15",
  "end_date": "2026-02-15"
}
```

## Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `student_id` | UUID | Filter by student ID |
| `category_id` | UUID | Filter by behavior category |
| `date_from` | Date | Filter incidents from this date |
| `date_to` | Date | Filter incidents to this date |
| `severity` | Enum | Filter by severity level (low, medium, high) |
| `status` | Enum | Filter by status |

## Status Codes

| Code | Description |
|-------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Validation error |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not found |
| 422 | Unprocessable entity |
| 500 | Internal server error |

## Best Practices

### For Teachers
- Document incidents with specific details and evidence
- Use objective language in incident descriptions
- Respond to incidents promptly and appropriately
- Document disciplinary actions with clear outcomes
- Update intervention plans regularly based on progress
- Recognize and celebrate positive behaviors

### For Administrators
- Monitor disciplinary action distribution and effectiveness
- Track chronic behavior patterns and school-wide trends
- Ensure consistent application of discipline policies
- Review intervention plans regularly
- Maintain confidentiality of sensitive behavior records

### For Parents
- Access student behavior history through parent portal
- Review intervention plans and provide feedback
- Communicate concerns to teachers promptly
- Celebrate positive behaviors and improvements at home

## Security Considerations

- All behavior data is considered sensitive student information
- Role-based access control ensures appropriate privacy
- Audit logging tracks all incident modifications
- Strict validation on status transitions and severity changes
- Parent access limited to their own children's records

## Integration

The behavior management system integrates with:
- **Student Information System** (Issue #229) - Student records and profiles
- **User Authentication** (Issue #196) - JWT-based access control
- **Notification System** (Issue #257) - Parent notifications for incidents
- **Calendar System** - Scheduling meetings and follow-ups
- **Parent Portal** (Issue #15) - Parent access to behavior history
