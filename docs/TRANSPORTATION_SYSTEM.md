# Transportation Management System

## Overview

The Transportation Management System provides comprehensive capabilities for managing school buses, routes, drivers, and student transportation assignments. This system enables real-time tracking, attendance management, and incident reporting for school transportation operations.

## Features

### Vehicle Management
- Complete bus fleet management with make, model, and capacity details
- Registration, insurance, and inspection expiry tracking
- GPS device integration for real-time tracking
- Maintenance history tracking
- Vehicle status management (active, maintenance, retired)

### Driver Management
- Driver profiles with license and certification tracking
- License expiry monitoring
- Hire date and employment status tracking
- Emergency contact information

### Route Management
- Route creation with multiple stops
- Route timing (start/end times)
- Stop ordering and coordinates
- Distance and duration calculation
- Route types: regular, express, special

### Student Assignment
- Assign students to routes and stops
- Session type selection (morning, afternoon, both)
- Fee management for transportation services
- Assignment history with effective dates

### Attendance Tracking
- Daily student boarding and alighting records
- Session-based attendance (morning/afternoon)
- Parent notifications for missed students
- Stop-level tracking

### Real-time Tracking
- GPS-based vehicle location tracking
- Speed and heading monitoring
- Ignition status
- Odometer tracking
- Recent location queries

### Incident Reporting
- Comprehensive incident types (accident, breakdown, delay, medical, safety issue)
- Severity levels (minor, moderate, major, critical)
- Location-based reporting
- Evidence photo support
- Parent notifications for major incidents
- Status tracking (open, under investigation, resolved, closed)

## Database Schema

### Tables

1. **transport_vehicles** - Vehicle fleet management
2. **transport_drivers** - Driver information
3. **transport_routes** - Bus route definitions
4. **transport_stops** - Route stops with coordinates
5. **transport_assignments** - Student to route/stop assignments
6. **transport_schedules** - Weekly route schedules
7. **transport_attendance** - Daily attendance records
8. **transport_incidents** - Transportation incidents
9. **transport_tracking** - GPS location history

## API Endpoints

### Vehicle Management

- `POST /api/transport/vehicles` - Create new vehicle
- `GET /api/transport/vehicles` - List all active vehicles
- `GET /api/transport/vehicles/{id}` - Get vehicle details
- `PUT /api/transport/vehicles/{id}` - Update vehicle
- `DELETE /api/transport/vehicles/{id}` - Delete vehicle

### Driver Management

- `POST /api/transport/drivers` - Create new driver
- `GET /api/transport/drivers` - List all active drivers
- `GET /api/transport/drivers/{id}` - Get driver details
- `PUT /api/transport/drivers/{id}` - Update driver

### Route Management

- `POST /api/transport/routes` - Create new route with stops
- `GET /api/transport/routes` - List all active routes
- `GET /api/transport/routes/{id}` - Get route details with stops

### Student Assignment

- `POST /api/transport/assignments` - Assign student to route/stop
- `GET /api/transport/assignments/{id}` - Get assignment details
- `GET /api/transport/students/{studentId}/assignments` - Get student's assignments

### Attendance

- `POST /api/transport/attendance` - Record attendance
- `GET /api/transport/attendance/today` - Get today's attendance

### Incident Reporting

- `POST /api/transport/incidents` - Report incident
- `GET /api/transport/incidents/{id}` - Get incident details
- `GET /api/transport/incidents/open` - Get open incidents

### Real-time Tracking

- `POST /api/transport/tracking/location` - Record vehicle location
- `GET /api/transport/tracking/vehicles/{vehicleId}/location` - Get latest vehicle location
- `GET /api/transport/tracking/vehicles/active` - Get vehicles with recent locations

### Statistics

- `GET /api/transport/stats` - Get transportation statistics

## API Examples

### Create Vehicle

```bash
curl -X POST https://api.example.com/api/transport/vehicles \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plate_number": "B 1234 CD",
    "vehicle_type": "bus",
    "make": "Mercedes",
    "model": "Sprinter",
    "year": 2020,
    "capacity": 20,
    "fuel_type": "diesel"
  }'
```

### Create Route with Stops

```bash
curl -X POST https://api.example.com/api/transport/routes \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "route_name": "Main School Route",
    "route_number": "R001",
    "start_time": "06:30:00",
    "end_time": "07:30:00",
    "stops": [
      {
        "stop_name": "Pickup Point A",
        "latitude": -6.2088,
        "longitude": 106.8456,
        "arrival_time": "06:35:00"
      },
      {
        "stop_name": "School",
        "latitude": -6.2090,
        "longitude": 106.8470,
        "arrival_time": "06:45:00"
      }
    ]
  }'
```

### Assign Student to Route

```bash
curl -X POST https://api.example.com/api/transport/assignments \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "uuid-of-student",
    "route_id": "uuid-of-route",
    "stop_id": "uuid-of-stop",
    "session_type": "both",
    "monthly_fee": 500000
  }'
```

### Record Vehicle Location (GPS)

```bash
curl -X POST https://api.example.com/api/transport/tracking/location \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": "uuid-of-vehicle",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "speed": 45.5,
    "heading": 90.0,
    "ignition_on": true,
    "odometer": 12345.67
  }'
```

### Report Incident

```bash
curl -X POST https://api.example.com/api/transport/incidents \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": "uuid-of-vehicle",
    "incident_type": "breakdown",
    "severity": "moderate",
    "incident_time": "2024-01-15T08:30:00Z",
    "description": "Engine breakdown on main road",
    "latitude": -6.2088,
    "longitude": 106.8456
  }'
```

### Get Vehicle Location

```bash
curl -X GET https://api.example.com/api/transport/tracking/vehicles/{vehicleId}/location \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Get Transportation Statistics

```bash
curl -X GET https://api.example.com/api/transport/stats \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

Response example:
```json
{
  "success": true,
  "data": {
    "total_vehicles": 15,
    "total_drivers": 18,
    "total_routes": 12,
    "total_assignments": 245,
    "vehicles_on_route": 8,
    "students_today": 198,
    "missed_today": 5,
    "open_incidents": 2
  },
  "message": "Statistics retrieved successfully",
  "timestamp": "2024-01-15T10:30:00+00:00"
}
```

## Constants

### Vehicle Status
- `active` - Vehicle in service
- `maintenance` - Vehicle under maintenance
- `retired` - Vehicle no longer in use

### Driver Status
- `active` - Driver currently employed
- `inactive` - Driver not currently employed
- `on_leave` - Driver on leave

### Vehicle Type
- `bus` - Standard school bus
- `van` - Smaller van vehicle
- `minibus` - Mid-size vehicle

### Route Type
- `regular` - Regular route with multiple stops
- `express` - Express route with fewer stops
- `special` - Special purpose route

### Session Type
- `morning` - Morning session only
- `afternoon` - Afternoon session only
- `both` - Both sessions

### Assignment Status
- `active` - Currently active assignment
- `inactive` - Not currently active
- `cancelled` - Cancelled assignment

### Fee Status
- `pending` - Fee payment pending
- `paid` - Fee has been paid
- `exempt` - Student is exempt from fees

### Attendance Status
- `pending` - Not yet recorded
- `boarded` - Student boarded the bus
- `missed` - Student missed the bus
- `excused` - Excused absence

### Incident Type
- `accident` - Accident occurred
- `breakdown` - Vehicle breakdown
- `delay` - Route delay
- `medical` - Medical emergency
- `safety_issue` - Safety concern

### Incident Severity
- `minor` - Minor incident
- `moderate` - Moderate incident
- `major` - Major incident
- `critical` - Critical incident

### Tracking Status
- `moving` - Vehicle is moving
- `stopped` - Vehicle is stopped
- `idle` - Engine running but not moving

## Security Considerations

### Access Control
- All endpoints require JWT authentication
- Write operations require appropriate role (Super Admin, Kepala Sekolah, Staf TU, Guru)
- Location recording can be done by GPS devices with service credentials

### Data Privacy
- Student transportation data should be protected
- Only authorized personnel can access location history
- Parent access limited to their children's information

### Location Data
- Real-time GPS data should use HTTPS
- Vehicle location data should be secured
- Historical location data should be cleaned up periodically

## Performance Considerations

### Tracking Data Volume
- Transport tracking table can grow rapidly
- Implement periodic cleanup of old location data
- Consider archiving historical data

### Real-time Updates
- Poll for latest location every 10-30 seconds for real-time tracking
- Use WebSocket implementation for production to reduce polling

### Database Indexes
- Indexes on frequently queried fields (vehicle_id, recorded_at)
- Composite indexes for common queries

## Integration Points

### Notification System
- Integrated with notification system for:
  - Parent alerts for missed students
  - Parent alerts for incidents
  - Driver notifications for expiring licenses

### Student Information
- Links to student records for assignments
- Uses student.user_id for parent notifications

### GPS Integration
- Placeholder implementation for GPS device APIs
- Can be integrated with providers like:
  - Teltonika
  - Ruptela
  - Trimble
  - GPSWOX

## Testing

Run transportation system tests:

```bash
php artisan test --filter=TransportationTest
```

Test coverage includes:
- Vehicle creation and validation
- Driver creation and validation
- Route creation with stops
- Location recording
- Incident reporting
- Statistics retrieval

## Troubleshooting

### Vehicle Not Appearing in Active List
- Check vehicle status is 'active'
- Verify vehicle is not in maintenance
- Check database indexes

### GPS Location Not Updating
- Verify GPS device is configured correctly
- Check vehicle_id is correct
- Verify database writes are working

### Parent Not Notified
- Check notification service is configured
- Verify student has valid user_id
- Check notification templates exist
