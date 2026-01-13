# Hostel and Dormitory Management System

## Overview

The Hostel and Dormitory Management System provides comprehensive boarding facility management for educational institutions. This system handles room allocation, student supervision, facility maintenance, visitor management, meal planning, attendance tracking, health monitoring, and incident reporting.

## Architecture

### Database Schema

The system uses 9 core database tables:

- **hostels** - Main hostel/facility records
- **rooms** - Room allocation and occupancy tracking
- **room_assignments** - Student to room mapping
- **maintenance_requests** - Facility maintenance requests
- **visitors** - Visitor management and approval workflow
- **meal_plans** - Meal planning and dietary requirements
- **boarding_attendance** - Check-in/check-out tracking
- **health_records** - Health and wellness monitoring
- **incidents** - Incident reports and disciplinary actions

### Models

#### Hostel Model
- Manages hostel/facility information
- Tracks capacity and occupancy
- Manages warden information
- Relationships to rooms, assignments, maintenance, visitors, etc.

#### Room Model
- Manages room allocation
- Tracks capacity and occupancy
- Handles room types (single, shared, etc.)
- Amenity management

#### RoomAssignment Model
- Maps students to rooms
- Manages active/inactive assignments
- Tracks assignment dates and checkout
- Bed number management

#### MaintenanceRequest Model
- Facility maintenance tracking
- Priority levels (critical, high, medium, low)
- Status management (pending, in_progress, resolved)
- Resolution tracking

#### Visitor Model
- Visitor registration and approval workflow
- Check-in/check-out tracking
- ID proof validation
- Relationship management

#### MealPlan Model
- Meal planning management
- Dietary requirements tracking
- Allergy management
- Active/Inactive status

#### BoardingAttendance Model
- Daily check-in/check-out tracking
- Leave management
- Status tracking (present, absent, on_leave, late)
- Date range queries

#### HealthRecord Model
- Health and wellness monitoring
- Severity tracking
- Medical record management
- Checkup date tracking

#### Incident Model
- Incident reporting and management
- Severity levels
- Status tracking (open, in_progress, resolved)
- Disciplinary action tracking

## API Endpoints

### Hostel Management

#### Create Hostel
```http
POST /api/hostel/hostels
```

**Request Body:**
```json
{
  "name": "Main Boys Hostel",
  "code": "HOSTEL-001",
  "type": "boarding",
  "gender": "male",
  "capacity": 100,
  "current_occupancy": 0,
  "warden_name": "John Smith",
  "warden_contact": "+1234567890",
  "address": "123 School Road",
  "facilities": ["wifi", "laundry", "study_room"],
  "is_active": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid-here",
    "name": "Main Boys Hostel",
    "code": "HOSTEL-001",
    ...
  },
  "message": "Hostel created successfully"
}
```

#### Get Hostel
```http
GET /api/hostel/hostels/{id}
```

#### Update Hostel
```http
PUT /api/hostel/hostels/{id}
```

#### Delete Hostel
```http
DELETE /api/hostel/hostels/{id}
```

### Room Management

#### Create Room
```http
POST /api/hostel/rooms
```

**Request Body:**
```json
{
  "hostel_id": "hostel-uuid",
  "room_number": "101",
  "floor": "1",
  "room_type": "shared",
  "capacity": 4,
  "current_occupancy": 0,
  "is_available": true,
  "amenities": ["ac", "desk", "wardrobe"],
  "description": "Room with balcony"
}
```

### Room Assignment

#### Assign Student to Room
```http
POST /api/hostel/assignments
```

**Request Body:**
```json
{
  "student_id": "student-uuid",
  "hostel_id": "hostel-uuid",
  "room_id": "room-uuid",
  "assignment_date": "2026-01-08",
  "bed_number": "A1",
  "status": "active",
  "notes": "Assignment notes"
}
```

**Validation:**
- Checks room capacity before assignment
- Prevents overbooking
- Updates hostel and room occupancy counters

#### Checkout Student
```http
POST /api/hostel/assignments/{assignmentId}/checkout
```

- Sets status to 'inactive'
- Records checkout date
- Decrements room and hostel occupancy

### Maintenance Requests

#### Create Maintenance Request
```http
POST /api/hostel/maintenance-requests
```

**Request Body:**
```json
{
  "hostel_id": "hostel-uuid",
  "room_id": "room-uuid",
  "reported_by": "user-uuid",
  "type": "plumbing",
  "priority": "high",
  "description": "Leaking faucet in bathroom",
  "status": "pending"
}
```

**Priority Levels:** critical, high, medium, low
**Status Flow:** pending → in_progress → resolved

#### Resolve Maintenance Request
```http
PUT /api/hostel/maintenance-requests/{id}
```

### Visitor Management

#### Create Visitor Registration
```http
POST /api/hostel/visitors
```

**Request Body:**
```json
{
  "hostel_id": "hostel-uuid",
  "visitor_student_id": "student-uuid",
  "visitor_name": "Jane Doe",
  "visitor_phone": "+9876543210",
  "relationship": "mother",
  "purpose": "Family visit",
  "id_proof_type": "ID Card",
  "id_proof_number": "ID123456",
  "notes": "Will stay for weekend"
}
```

**Visitor Status Flow:** pending → approved → checked_in → checked_out

#### Approve Visitor
```http
POST /api/hostel/visitors/{id}/approve
```

#### Check-in Visitor
```http
POST /api/hostel/visitors/{id}/checkin
```

#### Check-out Visitor
```http
POST /api/hostel/visitors/{id}/checkout
```

### Attendance Tracking

#### Check-in Student
```http
POST /api/hostel/attendance/checkin
```

**Request Body:**
```json
{
  "student_id": "student-uuid",
  "hostel_id": "hostel-uuid",
  "room_id": "room-uuid",
  "attendance_date": "2026-01-08"
}
```

**Attendance Status:** present, absent, on_leave, late

#### Mark Student on Leave
```http
POST /api/hostel/attendance/leave
```

**Request Body:**
```json
{
  "student_id": "student-uuid",
  "hostel_id": "hostel-uuid",
  "room_id": "room-uuid",
  "attendance_date": "2026-01-08",
  "leave_type": "weekend",
  "leave_start_date": "2026-01-08",
  "leave_end_date": "2026-01-10",
  "notes": "Visiting family"
}
```

### Reports and Analytics

#### Get Hostel Occupancy
```http
GET /api/hostel/hostels/{hostelId}/occupancy
```

**Response:**
```json
{
  "success": true,
  "data": {
    "hostel_id": "uuid",
    "hostel_name": "Main Boys Hostel",
    "capacity": 100,
    "current_occupancy": 85,
    "available_capacity": 15,
    "occupancy_percentage": 85,
    "rooms": [
      {
        "room_id": "uuid",
        "room_number": "101",
        "capacity": 4,
        "current_occupancy": 4,
        "available_beds": 0,
        "is_full": true,
        "students": [...]
      }
    ]
  }
}
```

#### Get Student Boarding Info
```http
GET /api/hostel/students/{studentId}/boarding-info
```

**Response Includes:**
- Hostel details (name, warden, contact)
- Room assignment (room number, floor, bed, assignment date)
- Meal plan (type, dietary requirements, allergies)
- Attendance summary (last 7 days breakdown)
- Health summary (recent records, critical records, last checkup)

#### Get Available Rooms
```http
GET /api/hostel/hostels/{hostelId}/available-rooms
```

**Query Parameters:**
- `room_type` - Filter by room type (single, shared)
- `floor` - Filter by floor number
- `min_beds` - Minimum available beds required

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "room_id": "uuid",
      "room_number": "102",
      "floor": "1",
      "room_type": "shared",
      "capacity": 4,
      "available_beds": 2,
      "amenities": ["ac", "desk"],
      "hostel": {
        "id": "uuid",
        "name": "Main Boys Hostel"
      }
    }
  ]
}
```

## Service Methods

### HostelManagementService

The `HostelManagementService` class provides all business logic for hostel management:

#### Hostel Operations
- `createHostel(array $data): Hostel`
- `getHostel(string $id): ?Hostel`
- `updateHostel(string $id, array $data): bool`
- `deleteHostel(string $id): bool`

#### Room Operations
- `createRoom(array $data): Room` - Auto-updates hostel occupancy
- `getRoom(string $id): ?Room`
- `updateRoom(string $id, array $data): bool`
- `deleteRoom(string $id): bool` - Auto-decrements hostel occupancy

#### Assignment Operations
- `assignStudentToRoom(array $data): RoomAssignment` - Validates capacity
- `updateRoomAssignment(string $id, array $data): bool`
- `checkoutStudentFromRoom(string $assignmentId): bool` - Updates occupancy counters

#### Maintenance Operations
- `createMaintenanceRequest(array $data): MaintenanceRequest`
- `updateMaintenanceRequest(string $id, array $data): bool`
- `resolveMaintenanceRequest(string $id, array $resolutionData): bool`

#### Visitor Operations
- `createVisitor(array $data): Visitor` - Auto-sets visit date and pending status
- `approveVisitor(string $id, string $approvedBy): bool`
- `checkInVisitor(string $id): bool`
- `checkOutVisitor(string $id): bool`

#### Attendance Operations
- `createAttendanceRecord(array $data): BoardingAttendance`
- `checkInStudent(array $data): BoardingAttendance` - Auto-sets check-in time and status
- `checkOutStudent(string $attendanceId): bool`
- `markStudentOnLeave(array $data): bool`

#### Health Records
- `createHealthRecord(array $data): HealthRecord`
- `updateHealthRecord(string $id, array $data): bool`

#### Incident Management
- `createIncident(array $data): Incident`
- `updateIncident(string $id, array $data): bool`
- `resolveIncident(string $id, array $resolutionData): bool` - Records action and disciplinary action

#### Reporting and Analytics
- `getHostelOccupancy(string $hostelId): array` - Full occupancy with rooms breakdown
- `getMaintenanceSummary(string $hostelId): array` - Status and priority breakdown
- `getAttendanceReport(string $hostelId, string $startDate, string $endDate): array` - Daily breakdown
- `getWellnessReport(string $hostelId, int $days = 30): array` - Severity and type breakdown
- `getIncidentReport(string $hostelId, int $days = 30): array` - Status and type breakdown
- `getStudentBoardingInfo(string $studentId): array` - Complete student boarding profile
- `getAvailableRooms(string $hostelId, array $filters = []): array` - Filterable available rooms

## Model Scopes

### Hostel Scopes
- `active()` - Filter active hostels

### Room Scopes
- `available()` - Filter available rooms
- `hasCapacity()` - Filter rooms with available beds
- `byType($type)` - Filter by room type
- `byType($type)` - Filter by room type

### RoomAssignment Scopes
- `active()` - Filter active assignments
- `inactive()` - Filter inactive assignments
- `byHostel($hostelId)` - Filter by hostel
- `byStudent($studentId)` - Filter by student

### MaintenanceRequest Scopes
- `pending()` - Filter pending requests
- `inProgress()` - Filter in-progress requests
- `resolved()` - Filter resolved requests
- `byPriority($priority)` - Filter by priority
- `byHostel($hostelId)` - Filter by hostel
- `highPriority()` - Filter high/critical priority requests

### Visitor Scopes
- `pending()` - Filter pending visitors
- `approved()` - Filter approved visitors
- `rejected()` - Filter rejected visitors
- `checkedIn()` - Filter checked-in visitors
- `checkedOut()` - Filter checked-out visitors
- `byHostel($hostelId)` - Filter by hostel
- `byDate($date)` - Filter by date

### BoardingAttendance Scopes
- `present()` - Filter present records
- `absent()` - Filter absent records
- `onLeave()` - Filter on-leave records
- `late()` - Filter late records
- `byDate($date)` - Filter by date
- `byHostel($hostelId)` - Filter by hostel
- `byStudent($studentId)` - Filter by student
- `dateRange($startDate, $endDate)` - Filter by date range

### HealthRecord Scopes
- `byStudent($studentId)` - Filter by student
- `byHostel($hostelId)` - Filter by hostel
- `byType($type)` - Filter by record type
- `bySeverity($severity)` - Filter by severity
- `critical()` - Filter critical records
- `recent($days = 30)` - Filter recent records
- `dateRange($startDate, $endDate)` - Filter by date range

### Incident Scopes
- `open()` - Filter open incidents
- `inProgress()` - Filter in-progress incidents
- `resolved()` - Filter resolved incidents
- `byHostel($hostelId)` - Filter by hostel
- `byStudent($studentId)` - Filter by student
- `byType($type)` - Filter by incident type
- `bySeverity($severity)` - Filter by severity
- `critical()` - Filter critical incidents
- `recent($days = 30)` - Filter recent incidents
- `dateRange($startDate, $endDate)` - Filter by date range

## Security and Access Control

### Authentication
- All API endpoints require JWT authentication
- Routes are protected with `jwt` and `rate.limit` middleware

### Role-Based Access
- **Hostel Management**: Staff, Administrators
- **Room Assignment**: Hostel staff, Administrators
- **Maintenance Request**: Boarders, Staff
- **Visitor Management**: Security staff, Hostel wardens
- **Attendance Tracking**: Hostel staff
- **Health Records**: Medical staff, Hostel staff
- **Incident Management**: Hostel staff, Administrators

### Student Privacy
- Health records restricted to medical staff and administrators
- Incident records accessible based on severity and role
- Visitor information logged for security audit

## Integration Points

### Student Information System (SIS)
- Room assignments link to student profiles
- Attendance data integrates with academic attendance
- Health records integrate with medical system

### Notification System
- Visitor approval notifications (Issue #257)
- Maintenance request status updates
- Emergency alerts
- Attendance notifications to parents

### Fee Management
- Boarding fee integration (Issue #200)
- Room charges
- Meal plan billing

### Health Management
- Health records integration (Issue #261)
- Wellness check coordination
- Medical emergency handling

## Data Validation

### Room Assignment
- Validates room capacity before assignment
- Prevents overbooking
- Checks hostel capacity limits

### Visitor Registration
- ID proof validation
- Contact information required
- Purpose and relationship required

### Maintenance Requests
- Type validation (plumbing, electrical, structural, etc.)
- Priority validation
- Description required for non-emergency requests

## Error Handling

### Common Error Codes
- `400` - Bad Request (validation failed)
- `404` - Not Found (resource doesn't exist)
- `500` - Internal Server Error (unexpected error)

### Error Response Format
```json
{
  "success": false,
  "message": "Error description"
}
```

## Best Practices

### Room Allocation
- Always check room capacity before assignment
- Update occupancy counters automatically
- Consider student preferences when available
- Maintain consistent bed numbering

### Visitor Management
- Require ID proof for all visitors
- Approve all visitors before check-in
- Track exact check-in and check-out times
- Log all visitor information for security

### Maintenance Requests
- Set appropriate priority based on urgency
- Provide detailed descriptions
- Include room number when applicable
- Track resolution for quality control

### Attendance Tracking
- Record check-in times daily
- Monitor late arrivals and unexcused absences
- Track leave periods accurately
- Report attendance issues promptly

### Health Monitoring
- Record all health incidents promptly
- Document severity accurately
- Track medications and treatments
- Alert for critical health issues

## Testing

### Running Tests
```bash
php bin/hyperf.php test tests/Feature/Hostel/HostelManagementTest.php
```

### Test Coverage
- Hostel CRUD operations
- Room creation and allocation
- Student assignment and checkout
- Maintenance request workflow
- Visitor registration and approval
- Attendance tracking
- Reporting and analytics

## Performance Considerations

### Database Indexing
- Composite indexes on student_id + status
- Indexes on hostel_id + status
- Date range indexes for queries
- Foreign key indexes for joins

### Caching
- Consider caching occupancy data
- Cache hostel lists for UI
- Cache room availability queries

### Query Optimization
- Use eager loading to prevent N+1 queries
- Use scopes for common filters
- Optimize date range queries

## Troubleshooting

### Common Issues

**Issue: Room overbooking**
- Ensure capacity checks before assignment
- Verify occupancy counters are updated correctly

**Issue: Visitor check-in without approval**
- Implement approval workflow validation
- Add middleware to check visitor status

**Issue: Attendance records not syncing**
- Check date formats in requests
- Verify unique constraint on student_id + date

**Issue: Maintenance requests stuck in pending**
- Implement status update notifications
- Add overdue request alerts

## Security Best Practices

### Student Safety
- Always verify visitor identity
- Track all student movements
- Implement curfew monitoring
- Report missing students immediately

### Facility Security
- Limit room access to assigned students
- Track key/badge assignments
- Monitor visitor activities
- Secure maintenance access

### Data Protection
- Encrypt sensitive health information
- Implement access logging
- Regular security audits
- Comply with student privacy regulations

## Future Enhancements

### Planned Features
- Mobile app for staff (Issue #178)
- Automated curfew alerts
- RFID-based room access
- AI-powered maintenance prediction
- Parent portal integration (Issue #232)
