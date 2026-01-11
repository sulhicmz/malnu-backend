# Timetable and Scheduling System

## Overview

The Timetable and Scheduling System provides comprehensive functionality for managing school schedules with intelligent conflict detection and automatic generation capabilities. This system enables administrators to create, validate, and optimize class schedules while preventing conflicts in teacher assignments, room bookings, and class scheduling.

## Features

### 1. Automatic Schedule Generation
- Generate complete timetables for classes or teachers
- Intelligent conflict-free scheduling based on constraints
- Support for custom time slots and room preferences

### 2. Conflict Detection
- **Teacher Conflicts**: Detect when a teacher is double-booked
- **Room Conflicts**: Identify room booking conflicts
- **Class Conflicts**: Prevent overlapping class schedules

### 3. Schedule Validation
- Validate schedule data before creation/update
- Check time ranges and logical consistency
- Provide detailed validation feedback and warnings

### 4. Available Time Slots
- Find available time slots for scheduling
- Consider existing schedules for classes, teachers, and rooms
- Support for standard school time slots

### 5. Schedule Management
- Full CRUD operations for schedules
- Integration with existing ClassSubject relationships
- Real-time conflict checking

## API Endpoints

### Generate Timetable
**POST** `/api/school/timetable/generate`

Generate a complete timetable for a class or teacher.

**Request Body:**
```json
{
  "class_id": "optional-class-id",
  "teacher_id": "optional-teacher-id",
  "constraints": {
    "preferred_rooms": ["R-101", "R-102", "Lab-1"]
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "class_subject_id": "cs-id",
      "day_of_week": 1,
      "start_time": "08:00",
      "end_time": "08:45",
      "room": "R-101"
    }
  ],
  "message": "Timetable generated successfully"
}
```

### Validate Schedule
**POST** `/api/school/timetable/validate`

Validate schedule data before creation or update.

**Request Body:**
```json
{
  "class_subject_id": "cs-id",
  "day_of_week": 1,
  "start_time": "08:00",
  "end_time": "08:45",
  "room": "R-101"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "errors": [],
    "warnings": []
  },
  "message": "Schedule validation completed"
}
```

### Detect Conflicts
**POST** `/api/school/timetable/conflicts`

Detect conflicts for a proposed schedule.

**Request Body:**
```json
{
  "class_subject_id": "cs-id",
  "day_of_week": 1,
  "start_time": "08:00",
  "end_time": "08:45",
  "room": "R-101"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "teacher_conflicts": [],
    "room_conflicts": [
      {
        "schedule_id": "conflicting-id",
        "type": "room",
        "message": "Room is already booked during this time",
        "conflicting_schedule": { ... }
      }
    ],
    "class_conflicts": []
  },
  "message": "Conflict detection completed"
}
```

### Create Schedule
**POST** `/api/school/timetable/schedules`

Create a new schedule entry.

**Request Body:**
```json
{
  "class_subject_id": "cs-id",
  "day_of_week": 1,
  "start_time": "08:00",
  "end_time": "08:45",
  "room": "R-101"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "schedule-id",
    "class_subject_id": "cs-id",
    "day_of_week": 1,
    "start_time": "08:00",
    "end_time": "08:45",
    "room": "R-101",
    "created_at": "2024-01-01T00:00:00+00:00",
    "updated_at": "2024-01-01T00:00:00+00:00"
  },
  "message": "Schedule created successfully"
}
```

### Update Schedule
**PUT** `/api/school/timetable/schedules/{id}`

Update an existing schedule.

**Request Body:**
```json
{
  "room": "R-102",
  "start_time": "09:00",
  "end_time": "09:45"
}
```

**Response:**
```json
{
  "success": true,
  "data": { ...updated schedule... },
  "message": "Schedule updated successfully"
}
```

### Delete Schedule
**DELETE** `/api/school/timetable/schedules/{id}`

Delete a schedule.

**Response:**
```json
{
  "success": true,
  "message": "Schedule deleted successfully"
}
```

### Get Class Schedule
**GET** `/api/school/timetable/class/{classId}/schedule`

Get complete schedule for a class.

**Query Parameters:**
- `day_of_week` (optional): Filter by day (1-7, where 1=Monday, 7=Sunday)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "schedule-id",
      "class_subject_id": "cs-id",
      "day_of_week": 1,
      "start_time": "08:00",
      "end_time": "08:45",
      "room": "R-101",
      "class_subject": {
        "id": "cs-id",
        "class": { ... },
        "subject": { ... },
        "teacher": { ... }
      }
    }
  ],
  "message": "Class schedule retrieved successfully"
}
```

### Get Teacher Schedule
**GET** `/api/school/timetable/teacher/{teacherId}/schedule`

Get complete schedule for a teacher.

**Query Parameters:**
- `day_of_week` (optional): Filter by day (1-7, where 1=Monday, 7=Sunday)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "schedule-id",
      "class_subject_id": "cs-id",
      "day_of_week": 1,
      "start_time": "08:00",
      "end_time": "08:45",
      "room": "R-101",
      "class_subject": {
        "id": "cs-id",
        "class": { ... },
        "subject": { ... },
        "teacher": { ... }
      }
    }
  ],
  "message": "Teacher schedule retrieved successfully"
}
```

### Get Available Slots
**GET** `/api/school/timetable/available-slots`

Find available time slots for scheduling.

**Query Parameters:**
- `day_of_week` (required): Day to check (1-7, where 1=Monday, 7=Sunday)
- `class_id` (optional): Class to consider
- `teacher_id` (optional): Teacher to consider

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "start": "08:00",
      "end": "08:45"
    },
    {
      "start": "09:00",
      "end": "09:45"
    }
  ],
  "message": "Available slots retrieved successfully"
}
```

## Scheduling Algorithms

### Conflict Detection

The system uses time range overlap detection to identify conflicts:

```php
function timeRangesOverlap($start1, $end1, $start2, $end2): bool
{
    $s1 = strtotime($start1);
    $e1 = strtotime($end1);
    $s2 = strtotime($start2);
    $e2 = strtotime($end2);

    return !($e1 <= $s2 || $s1 >= $e2);
}
```

This function returns `true` if two time ranges overlap, considering:
- Adjacent time slots (e.g., 08:00-08:45 and 08:45-09:00) are NOT overlapping
- Enclosed time slots ARE overlapping
- Partial overlaps ARE overlapping

### Schedule Generation

The system uses a heuristic algorithm for schedule generation:

1. **Get Input**: Class ID or Teacher ID
2. **Collect Subjects**: Retrieve all ClassSubject associations
3. **Define Time Slots**: Use standard school time slots
4. **Assign Sequentially**: Iterate through days and time slots
5. **Check Availability**: For each slot, verify no conflicts exist
6. **Assign Room**: Use preferred rooms or randomly assign
7. **Return Schedule**: List of generated schedule entries

**Standard Time Slots:**
- 07:30 - 08:15
- 08:15 - 09:00
- 09:00 - 09:45
- 10:00 - 10:45
- 10:45 - 11:30
- 11:30 - 12:15
- 13:00 - 13:45
- 13:45 - 14:30

### Available Slots Calculation

The system calculates available slots by:

1. **Get Standard Time Slots**: All predefined time slots
2. **Find Occupied Slots**: Query existing schedules for the class/teacher
3. **Compare Each Slot**: Check if each standard slot overlaps with any occupied slot
4. **Return Available**: List of non-overlapping slots

## Data Model

### Schedule Model

```php
{
  "id": string,              // UUID
  "class_subject_id": string, // FK to class_subjects
  "day_of_week": integer,     // 1-7 (Monday-Sunday)
  "start_time": string,      // HH:MM format
  "end_time": string,        // HH:MM format
  "room": string,            // Room identifier
  "created_at": datetime,
  "updated_at": datetime
}
```

### Relationships

- **Schedule** belongs to **ClassSubject**
- **ClassSubject** belongs to **ClassModel**
- **ClassSubject** belongs to **Subject**
- **ClassSubject** belongs to **Teacher**

## Usage Examples

### Example 1: Create a Schedule with Conflict Checking

```php
// First, validate the proposed schedule
$validation = $timetableService->validateSchedule([
    'class_subject_id' => 'cs-123',
    'day_of_week' => 1,
    'start_time' => '08:00',
    'end_time' => '08:45',
    'room' => 'R-101'
]);

if (!$validation['valid']) {
    throw new Exception(implode(', ', $validation['errors']));
}

// Then create the schedule
$schedule = $timetableService->createSchedule([
    'class_subject_id' => 'cs-123',
    'day_of_week' => 1,
    'start_time' => '08:00',
    'end_time' => '08:45',
    'room' => 'R-101'
]);
```

### Example 2: Find Available Time Slots

```bash
GET /api/school/timetable/available-slots?day_of_week=1&class_id=class-123&teacher_id=teacher-456
```

This returns all available time slots on Monday for both the specified class and teacher.

### Example 3: Generate Complete Class Timetable

```bash
POST /api/school/timetable/generate
{
  "class_id": "class-123",
  "constraints": {
    "preferred_rooms": ["R-101", "R-102", "Lab-1"]
  }
}
```

This generates a complete timetable for the specified class, using the preferred rooms when possible.

## Integration with Existing Components

### Frontend Integration

The existing `TeachingSchedule.tsx` component can be enhanced with:

1. **API Integration**: Replace mock data with real API calls
2. **Conflict Visualization**: Highlight conflicts in the timetable UI
3. **Real-time Validation**: Validate schedules as users create/edit them
4. **Available Slots**: Show available slots when creating new schedules

### Integration with Other Systems

- **Calendar System (Issue #258)**: Sync schedules with calendar events
- **Attendance System**: Track attendance based on schedules
- **Leave Management (Issue #108)**: Handle substitute teacher assignments
- **E-Learning System**: Link online classes to scheduled sessions

## Performance Considerations

### Query Optimization

- Use database indexes on `day_of_week`, `start_time`, `end_time`
- Eager load relationships to avoid N+1 queries
- Cache frequently accessed schedules

### Conflict Detection Performance

- Conflict detection queries use indexed columns
- Time range comparisons are efficient string comparisons
- Batch conflict detection for multiple schedules

## Security Considerations

### Access Control

- All endpoints require JWT authentication
- Role-based access for schedule management
- Audit logging for all schedule modifications

### Data Validation

- Input validation on all schedule data
- Time range validation to prevent invalid schedules
- Conflict detection prevents double-booking

## Limitations and Future Enhancements

### Current Limitations

- Schedule generation uses simple heuristic algorithms
- No support for complex constraint satisfaction problems
- Limited substitution management (future enhancement)
- No multi-school campus scheduling support

### Future Enhancements

- **Advanced AI Optimization**: Machine learning for optimal schedules
- **Substitute Management**: Automatic substitute assignment for absent teachers
- **Complex Constraints**: Support for advanced scheduling rules
- **Preference-Based Scheduling**: Teacher and student preferences
- **Multi-Campus Support**: Scheduling across multiple locations

## Testing

Comprehensive test coverage is provided in `tests/Feature/TimetableTest.php`:

- Schedule creation and validation
- Conflict detection for teachers, rooms, and classes
- Available slots calculation
- Schedule retrieval by class and teacher
- CRUD operations
- Time range overlap detection

Run tests with:

```bash
vendor/bin/phpunit tests/Feature/TimetableTest.php
```

## Troubleshooting

### Common Issues

**Issue**: "Schedule has conflicts" error
**Solution**: Use the `/api/school/timetable/conflicts` endpoint to identify conflicts

**Issue**: No available slots returned
**Solution**: Check if all standard time slots are occupied or adjust day_of_week parameter

**Issue**: Schedule validation fails
**Solution**: Review validation errors for specific issues (invalid times, missing required fields)

## Support

For issues or questions about the Timetable System:
- Check API documentation
- Review test cases for usage examples
- Refer to this documentation for algorithm details
