# Student Attendance Tracking System

## Overview

The Student Attendance Tracking System provides comprehensive attendance management for students, including daily attendance marking, bulk attendance operations, attendance statistics, chronic absenteeism detection, and attendance reporting.

## Features

### 1. Attendance Marking
- Individual student attendance marking
- Bulk attendance marking by class
- Support for multiple attendance statuses (present, absent, late, excused)
- Check-in/check-out time tracking
- Notes and remarks for each attendance record

### 2. Attendance Tracking
- Daily attendance tracking by class
- Teacher authorization checks
- Automatic absence detection
- Attendance history for individual students
- Class-wide attendance tracking

### 3. Statistics and Analytics
- Attendance percentage calculations
- Present, absent, late, excused day tracking
- Chronic absenteeism detection
- Student attendance statistics
- Class attendance statistics

### 4. Reporting
- Individual student attendance reports
- Class attendance reports
- Daily, weekly, monthly reports
- Date range queries
- Export capabilities

## Database Schema

### student_attendances Table

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| student_id | UUID | Foreign key to students table |
| class_id | UUID | Foreign key to class_models table |
| teacher_id | UUID | Foreign key to users table (nullable) |
| attendance_date | DATE | Date of attendance |
| status | ENUM | present, absent, late, excused |
| notes | TEXT | Optional notes about attendance |
| check_in_time | TIME | Check-in time (nullable) |
| check_out_time | TIME | Check-out time (nullable) |
| marked_by | UUID | User who marked attendance |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Record update timestamp |

### Indexes

- student_id (for student attendance queries)
- class_id (for class attendance queries)
- attendance_date (for date range queries)
- status (for filtering by status)
- (student_id, attendance_date) composite index
- (class_id, attendance_date) composite index

## API Endpoints

### Mark Attendance

#### POST /api/attendance/student/mark

Mark individual student attendance.

**Request Body:**
```json
{
  "student_id": "uuid",
  "class_id": "uuid",
  "teacher_id": "uuid",
  "marked_by": "uuid",
  "status": "present",
  "attendance_date": "2024-01-15",
  "check_in_time": "08:00:00",
  "check_out_time": "14:00:00",
  "notes": "Optional notes"
}
```

**Status Options:**
- `present` - Student was present
- `absent` - Student was absent
- `late` - Student arrived late
- `excused` - Student absence was excused

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "student_id": "uuid",
    "status": "present",
    "attendance_date": "2024-01-15"
  },
  "message": "Attendance marked successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Bulk Mark Attendance

#### POST /api/attendance/student/bulk-mark

Mark attendance for multiple students in a class at once.

**Request Body:**
```json
{
  "class_id": "uuid",
  "teacher_id": "uuid",
  "marked_by": "uuid",
  "attendance_date": "2024-01-15",
  "attendances": [
    {
      "student_id": "uuid-1",
      "status": "present"
    },
    {
      "student_id": "uuid-2",
      "status": "absent",
      "notes": "Sick leave"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "count": 2,
    "attendances": [...]
  },
  "message": "Bulk attendance marked successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Get Student Attendance

#### GET /api/attendance/student/{id}

Get attendance history for a specific student.

**Query Parameters:**
- `start_date` (optional) - Filter attendance from this date
- `end_date` (optional) - Filter attendance until this date

**Response:**
```json
{
  "success": true,
  "data": {
    "attendances": [
      {
        "id": "uuid",
        "attendance_date": "2024-01-15",
        "status": "present",
        "check_in_time": "08:00:00",
        "check_out_time": "14:00:00",
        "notes": null
      }
    ],
    "statistics": {
      "total_days": 100,
      "present_days": 85,
      "absent_days": 10,
      "late_days": 5,
      "excused_days": 5,
      "attendance_percentage": 85.00,
      "is_chronic_absentee": false
    }
  },
  "message": "Student attendance retrieved successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Get Class Attendance

#### GET /api/attendance/class/{id}

Get attendance records for a specific class.

**Query Parameters:**
- `date` (optional) - Filter by specific date

**Response:**
```json
{
  "success": true,
  "data": {
    "attendances": [...],
    "statistics": {
      "total_students": 30,
      "total_records": 30,
      "present_count": 25,
      "absent_count": 5,
      "late_count": 3,
      "class_average_attendance": 83.33
    },
    "students": [...]
  },
  "message": "Class attendance retrieved successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Get Attendance Statistics

#### GET /api/attendance/student/{id}/statistics

Get detailed attendance statistics for a student.

**Query Parameters:**
- `start_date` (optional) - Statistics from this date
- `end_date` (optional) - Statistics until this date

**Response:**
```json
{
  "success": true,
  "data": {
    "total_days": 100,
    "present_days": 85,
    "absent_days": 10,
    "late_days": 5,
    "excused_days": 5,
    "attendance_percentage": 85.00,
    "is_chronic_absentee": false
  },
  "message": "Attendance statistics retrieved successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Get Attendance Report

#### GET /api/attendance/class/{id}/report

Generate attendance report for a class over a date range.

**Query Parameters:**
- `class_id` (required) - Class ID
- `start_date` (required) - Report start date
- `end_date` (required) - Report end date

**Response:**
```json
{
  "success": true,
  "data": {
    "class_id": "uuid",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "statistics": {
      "total_students": 30,
      "total_records": 900,
      "present_count": 765,
      "absent_count": 135,
      "late_count": 75,
      "class_average_attendance": 85.00
    },
    "daily_attendance": [
      {
        "date": "2024-01-01",
        "present": 28,
        "absent": 2,
        "late": 1,
        "excused": 0,
        "total": 30
      }
    ]
  },
  "message": "Attendance report generated successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Get Chronic Absentees

#### GET /api/attendance/chronic-absentees

Get list of students with chronic absenteeism.

**Query Parameters:**
- `days` (optional) - Lookback period in days (default: 30)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "student_id": "uuid",
      "student_name": "John Doe",
      "absent_days": 5,
      "attendance_percentage": 83.33
    }
  ],
  "message": "Chronic absentees retrieved successfully",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## Model Scopes

The `StudentAttendance` model provides several query scopes:

### `byStudent($studentId)`
Filter attendance by student ID.

```php
StudentAttendance::byStudent($studentId)->get();
```

### `byClass($classId)`
Filter attendance by class ID.

```php
StudentAttendance::byClass($classId)->get();
```

### `byDate($date)`
Filter attendance by specific date.

```php
StudentAttendance::byDate($date)->get();
```

### `byDateRange($startDate, $endDate)`
Filter attendance within date range.

```php
StudentAttendance::byDateRange($startDate, $endDate)->get();
```

### `byStatus($status)`
Filter attendance by status.

```php
StudentAttendance::byStatus('present')->get();
```

### `present()`
Filter present attendance records.

```php
StudentAttendance::present()->get();
```

### `absent()`
Filter absent attendance records.

```php
StudentAttendance::absent()->get();
```

### `late()`
Filter late attendance records.

```php
StudentAttendance::late()->get();
```

### `excused()`
Filter excused attendance records.

```php
StudentAttendance::excused()->get();
```

### `withRelationships()`
Eager load relationships (student, class, teacher, marked_by).

```php
StudentAttendance::withRelationships()->get();
```

## Service Methods

### `markAttendance(array $data): StudentAttendance`
Mark individual student attendance.

### `markBulkAttendance(string $classId, array $attendanceData, string $teacherId, string $markedBy): array`
Mark attendance for multiple students at once.

### `getStudentAttendance(string $studentId, ?string $startDate, ?string $endDate): object`
Get attendance history for a student with optional date filtering.

### `getClassAttendance(string $classId, ?string $date): object`
Get attendance records for a class with optional date filtering.

### `calculateAttendanceStatistics(string $studentId, ?string $startDate, ?string $endDate): array`
Calculate attendance statistics for a student.

### `calculateClassStatistics(string $classId, ?string $date): array`
Calculate attendance statistics for a class.

### `detectChronicAbsenteeism(): array`
Identify students with chronic absenteeism.

### `validateTeacherAccess(string $teacherId, string $classId): bool`
Validate that a teacher is authorized to mark attendance for a class.

## Configuration

Add to `.env` file:

```bash
# Attendance Configuration
ATTENDANCE_CHRONIC_ABSENCE_THRESHOLD=3
ATTENDANCE_CUTOFF_HOUR=14
```

### Configuration Options

- `ATTENDANCE_CHRONIC_ABSENCE_THRESHOLD`: Number of absences within 30 days to be considered chronic absentee (default: 3)
- `ATTENDANCE_CUTOFF_HOUR`: Hour after which attendance cannot be marked (default: 14, i.e., 2 PM)

## Security & Privacy

### Authorization
- Teachers can only mark attendance for their assigned classes
- All endpoints require JWT authentication
- Teacher authorization is validated before marking attendance

### Data Privacy
- Attendance records are sensitive student information
- Audit logging tracks who marked attendance and when
- Student privacy is maintained through role-based access control

## Integration with Related Systems

### Notification System (Issue #257)
- Automatic notifications can be sent to parents when students are absent
- Chronic absenteeism alerts can be configured
- Daily attendance summary notifications

### Student Information System (Issue #229)
- Student profiles are linked to attendance records
- Attendance history is available in student profiles
- Parent portal can access student attendance

### Parent Portal (Future Enhancement)
- Parents can view their children's attendance
- Monthly attendance summaries for parents
- Absence explanation submission

## Migration

After deploying this feature, run the migration:

```bash
php artisan migrate
```

This will create the `student_attendances` table with proper indexes and foreign key constraints.

## Testing

Run the attendance test suite:

```bash
vendor/bin/phpunit tests/Feature/AttendanceTest.php
```

Tests cover:
- Marking individual student attendance
- Bulk attendance marking
- Retrieving student attendance
- Retrieving class attendance
- Attendance statistics calculation
- Model scopes and query filters

## Best Practices

1. **Mark Attendance Regularly**: Teachers should mark attendance daily for accurate records
2. **Use Bulk Operations**: When marking attendance for entire class, use bulk API
3. **Track Absences**: Add notes to attendance records for absent students
4. **Monitor Chronic Absenteeism**: Regularly review chronic absentee list for intervention
5. **Use Date Ranges**: When querying attendance, use date range parameters for efficiency

## Troubleshooting

### Common Issues

**Issue**: Teacher unauthorized to mark attendance
- **Solution**: Verify teacher is assigned to the class in the Teacher model

**Issue**: Attendance not appearing in reports
- **Solution**: Verify attendance_date format is correct (YYYY-MM-DD)

**Issue**: Chronic absenteeism not detected
- **Solution**: Check ATTENDANCE_CHRONIC_ABSENCE_THRESHOLD configuration

**Issue**: Performance issues with large date ranges
- **Solution**: Use proper pagination when querying large attendance datasets

## Performance Considerations

- Database indexes on student_id, class_id, attendance_date, and status ensure fast queries
- Eager loading relationships with `withRelationships()` prevents N+1 queries
- Bulk operations are more efficient than individual insert operations
- Use date range queries instead of loading all records when possible

## Future Enhancements

- Biometric attendance integration
- GPS-based attendance tracking
- Automated attendance from online learning platform
- AI-powered attendance pattern analysis
- Parent notification for real-time absence alerts
- Mobile app for teachers to mark attendance

## Support

For questions or issues related to the Student Attendance Tracking System, please refer to:
- Issue #199 on GitHub
- API Documentation
- Developer Guide
