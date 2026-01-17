# Behavior and Discipline Management System

## Overview

The Behavior and Discipline Management System provides comprehensive tools for tracking student behavior, managing disciplinary actions, creating intervention plans, and monitoring behavior patterns. This system is critical for:

- Tracking student behavior and incidents
- Managing disciplinary actions and interventions
- Positive behavior recognition programs
- Behavior analytics and reporting
- Parent communication for behavior incidents

## Architecture

### Database Schema

The system uses 5 core tables:

1. **behavior_categories** - Classification system for different behavior types
   - Type: positive, negative, or neutral
   - Severity level: 1-5 scale
   - Active status for enable/disable

2. **behavior_incidents** - Individual behavior incident records
   - Student reference
   - Category reference
   - Severity levels: minor, moderate, severe, critical
   - Resolution tracking with assigned staff
   - Parent notification tracking

3. **discipline_actions** - Disciplinary measures taken
   - Incident reference
   - Action type: warning, detention, suspension, expulsion, counseling, community service, parent conference, other
   - Duration tracking for detention/suspension
   - Completion status tracking

4. **intervention_plans** - Student behavior improvement plans
   - Goals and strategies
   - Status workflow: draft, active, completed, cancelled
   - Start/end date tracking
   - Review scheduling
   - Success evaluation

5. **behavior_notes** - Daily/weekly behavior observations
   - Type: positive, negative, or neutral
   - Private/Public visibility control
   - Date tracking for chronological view

### Models

#### BehaviorCategory
- **Relationships**: hasMany(BehaviorIncident)
- **Scopes**:
  - `active()` - Filter active categories
  - `byType($type)` - Filter by type (positive/negative/neutral)
  - `bySeverity($min, $max)` - Filter by severity range

#### BehaviorIncident
- **Relationships**: belongsTo(Student, User), belongsTo(BehaviorCategory), hasMany(DisciplineAction)
- **Scopes**:
  - `resolved()` - Filter resolved incidents
  - `unresolved()` - Filter unresolved incidents
  - `bySeverity($severity)` - Filter by severity level
  - `byDateRange($start, $end)` - Filter by date range

#### DisciplineAction
- **Relationships**: belongsTo(BehaviorIncident), belongsTo(User)
- **Scopes**:
  - `completed()` - Filter completed actions
  - `pending()` - Filter pending actions
  - `byActionType($type)` - Filter by action type
  - `active()` - Filter currently active actions

#### InterventionPlan
- **Relationships**: belongsTo(Student, User)
- **Scopes**:
  - `active()` - Filter active intervention plans
  - `byStatus($status)` - Filter by status
  - `completed()` - Filter completed plans
  - `forStudent($studentId)` - Filter by student

#### BehaviorNote
- **Relationships**: belongsTo(Student, User)
- **Scopes**:
  - `public()` - Filter public notes
  - `private()` - Filter private notes (staff only)
  - `byType($type)` - Filter by note type
  - `byDateRange($start, $end)` - Filter by date range
  - `forStudent($studentId)` - Filter by student

## API Endpoints

### Authentication
All endpoints require JWT authentication.

### Behavior Incident Management

#### Create Incident
```http
POST /api/behavior/incidents
Content-Type: application/json
Authorization: Bearer {jwt_token}
```

**Request Body:**
```json
{
  "student_id": "uuid-here",
  "behavior_category_id": "uuid-here",
  "severity": "moderate",
  "description": "Student was disruptive during class",
  "incident_date": "2026-01-17",
  "incident_time": "14:30:00",
  "location": "Classroom 3B",
  "witnesses": "John Doe, Jane Smith"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "incident-uuid",
    "student_id": "student-uuid",
    "severity": "moderate",
    "description": "Student was disruptive during class",
    "is_resolved": false,
    "parent_notified": false,
    ...
  },
  "message": "Incident reported successfully",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

#### Get Incidents
```http
GET /api/behavior/incidents?student_id={uuid}&severity={minor|moderate|severe|critical}&start_date={date}&end_date={date}&page={page}&per_page={count}
Authorization: Bearer {jwt_token}
```

**Query Parameters:**
- `student_id` (optional) - Filter by student
- `severity` (optional) - Filter by severity level
- `start_date` (optional) - Start date filter
- `end_date` (optional) - End date filter
- `page` (optional, default: 1) - Page number
- `per_page` (optional, default: 15) - Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "incident-uuid",
      "student_id": "student-uuid",
      "severity": "moderate",
      "description": "Student was disruptive during class",
      "is_resolved": false,
      "incident_date": "2026-01-17",
      ...
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  },
  "message": "Operation successful",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

### Discipline Actions

#### Create Discipline Action
```http
POST /api/behavior/discipline-actions
Content-Type: application/json
Authorization: Bearer {jwt_token}
```

**Request Body:**
```json
{
  "incident_id": "incident-uuid",
  "assigned_to": "staff-uuid",
  "action_type": "suspension",
  "duration_days": 5,
  "start_date": "2026-01-20",
  "end_date": "2026-01-25",
  "description": "Suspended for fighting",
  "conditions": "Complete counseling before returning"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "action-uuid",
    "incident_id": "incident-uuid",
    "action_type": "suspension",
    "duration_days": 5,
    "is_completed": false,
    ...
  },
  "message": "Disciplinary action created successfully",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

### Intervention Plans

#### Create Intervention Plan
```http
POST /api/behavior/intervention-plans
Content-Type: application/json
Authorization: Bearer {jwt_token}
```

**Request Body:**
```json
{
  "student_id": "student-uuid",
  "assigned_to": "counselor-uuid",
  "title": "Behavioral Support Plan",
  "description": "Weekly counseling sessions for 3 months",
  "goals": "Improve classroom behavior",
  "strategies": "Positive reinforcement, clear expectations",
  "start_date": "2026-01-20",
  "end_date": "2026-04-20",
  "review_date": "2026-04-15"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "plan-uuid",
    "student_id": "student-uuid",
    "title": "Behavioral Support Plan",
    "status": "draft",
    ...
  },
  "message": "Intervention plan created successfully",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

### Student History

#### Get Student Behavior History
```http
GET /api/behavior/student/{id}/history
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "incidents": [
      {
        "id": "incident-uuid",
        "severity": "moderate",
        "description": "Student was disruptive",
        "incident_date": "2026-01-17",
        ...
      }
    ],
    "discipline_actions": [
      {
        "id": "action-uuid",
        "action_type": "suspension",
        "duration_days": 5,
        "is_completed": true,
        ...
      }
    ],
    "intervention_plans": [
      {
        "id": "plan-uuid",
        "title": "Behavioral Support Plan",
        "status": "active",
        ...
      }
    ]
  },
  "message": "Operation successful",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

### Reports

#### Get Behavior Reports
```http
GET /api/behavior/reports?start_date={date}&end_date={date}&severity={level}&category_id={uuid}
Authorization: Bearer {jwt_token}
```

**Query Parameters:**
- `start_date` (optional, default: current month start) - Report start date
- `end_date` (optional, default: current month end) - Report end date
- `severity` (optional) - Filter by severity level
- `category_id` (optional) - Filter by behavior category

**Response:**
```json
{
  "success": true,
  "data": {
    "total_incidents": 150,
    "resolved_incidents": 120,
    "resolution_rate": 80.00,
    "severe_incidents": 15,
    "minor_incidents": 85,
    "total_actions": 45,
    "completed_actions": 40,
    "action_completion_rate": 88.89
  },
  "message": "Operation successful",
  "timestamp": "2026-01-17T14:30:00Z"
}
```

## Behavior Management Service

The `BehaviorManagementService` provides core business logic:

### Methods

#### Incident Management
- `createIncident(array $data): BehaviorIncident` - Create a new incident
- `updateIncident(string $id, array $data): BehaviorIncident` - Update incident
- `resolveIncident(string $id, string $resolvedBy, ?string $resolutionNotes = null): BehaviorIncident` - Resolve incident

#### Discipline Action Management
- `createDisciplineAction(array $data): DisciplineAction` - Create disciplinary action
- `completeDisciplineAction(string $id, string $completedBy): DisciplineAction` - Mark action as completed

#### Intervention Plan Management
- `createInterventionPlan(array $data): InterventionPlan` - Create intervention plan
- `updateInterventionPlan(string $id, array $data): InterventionPlan` - Update plan
- `activateInterventionPlan(string $id): InterventionPlan` - Activate plan
- `completeInterventionPlan(string $id, bool $isSuccessful, ?string $notes = null): InterventionPlan` - Complete plan

#### Behavior Note Management
- `createBehaviorNote(array $data): BehaviorNote` - Create behavior note

#### Behavior Category Management
- `createBehaviorCategory(array $data): BehaviorCategory` - Create behavior category

#### Analytics and Reporting
- `getStudentBehaviorHistory(string $studentId, ?int $limit = 50): array` - Get complete history
- `getBehaviorStatistics(array $filters = []): array` - Get behavior statistics

## Security and Privacy Considerations

### Role-Based Access Control

- **Super Admin|Kepala Sekolah|Staf TU|Guru**: Full access to all behavior management features
- **Parents**: Read-only access to their children's behavior records
- **Students**: Read-only access to their own behavior records

### Data Privacy

- Sensitive behavior data is protected with appropriate access controls
- Private notes are only visible to authorized staff
- Parent notification tracking for compliance
- Audit logging of all incident modifications
- Data retention policies can be configured

### GDPR Compliance

- Student consent for behavior tracking
- Right to access personal behavior data
- Right to request data deletion
- Clear data retention policies
- Secure storage of sensitive information

## Integration Points

### Notification System (Issue #257)
- Automatic parent notifications for severe incidents
- Notification delivery status tracking
- Email, SMS, and in-app notification channels

### Student Information System (Issue #229)
- Integration with student profiles
- Automatic student information retrieval
- Attendance and academic record integration

### Academic Records System
- Behavior impact on grades
- Correlation with attendance patterns
- Performance tracking integration

## Best Practices

### Incident Reporting
1. Document incidents immediately with as much detail as possible
2. Use appropriate severity levels to ensure proper follow-up
3. Include witness information when available
4. Consider student age and context when categorizing behavior

### Discipline Actions
1. Ensure actions are proportional to the incident severity
2. Document clear conditions for reinstatement
3. Set appropriate review dates
4. Track completion of all disciplinary actions

### Intervention Planning
1. Involve parents and students in goal setting
2. Set specific, measurable goals
3. Use evidence-based strategies
4. Schedule regular reviews and adjustments
5. Monitor progress and adjust strategies as needed

### Documentation Standards
1. Use objective, factual language
2. Avoid subjective judgments
3. Include dates, times, and locations
4. Document conversations with parents and students
5. Maintain consistent formatting across all records

## Troubleshooting

### Common Issues

#### Parent Not Not Receiving Notifications
- Check notification service configuration
- Verify parent contact information is correct
- Check notification delivery logs

#### Incident Not Showing in Student History
- Verify student_id matches
- Check if incident date is within filter range
- Ensure incident has not been deleted (soft delete)

#### Discipline Action Not Completing
- Verify end_date has not passed
- Check if action is already marked complete
- Review system logs for errors

## Testing

Run behavior management tests:
```bash
vendor/bin/co-phpunit tests/Feature/BehaviorManagementTest.php
```

Test coverage includes:
- Incident creation with valid data
- Incident creation with missing required fields
- Incident filtering by student and severity
- Discipline action creation and validation
- Intervention plan creation and validation
- Student behavior history retrieval
- Behavior report generation
- Role-based access control

## Future Enhancements

### Potential Improvements
- AI-powered behavior pattern analysis
- Predictive behavior analytics
- Mobile app for parents and teachers
- Advanced reporting with visualizations
- Integration with external counseling services
- Behavior trend analysis over time
