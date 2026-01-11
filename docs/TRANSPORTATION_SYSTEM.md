# Transportation Management System

## Overview

The Transportation Management System provides comprehensive functionality for managing school transportation operations including vehicle management, route planning, driver management, student assignments, attendance tracking, GPS vehicle tracking, fee management, and parent notifications.

## System Architecture

### Database Schema

The system uses 10 main database tables:

1. **transport_vehicles** - Bus fleet and vehicle information
2. **transport_stops** - Pickup and dropoff locations with GPS coordinates
3. **transport_routes** - Bus routes with timing and capacity information
4. **transport_route_stops** - Link table for route-stop relationships with sequence order
5. **transport_drivers** - Driver profiles with license and certification tracking
6. **transport_assignments** - Student-to-route assignments
7. **transport_schedules** - Driver-vehicle-route schedule assignments
8. **transport_attendance** - Student boarding/alighting tracking
9. **transport_tracking** - Real-time GPS vehicle location data
10. **transport_fees** - Transportation fee billing and payments
11. **transport_notifications** - Parent alerts and notifications

### Service Layer

**TransportationService** (`app/Services/TransportationService.php`)

Core business logic for all transportation operations:

- Vehicle CRUD operations
- Stop management with geolocation
- Route creation with stop sequencing
- Driver management with certification tracking
- Student assignment to routes
- Attendance tracking for safety
- GPS location recording and retrieval
- Fee management and billing
- Notification creation and sending
- Route analytics and reporting

### API Controller

**TransportationController** (`app/Http/Controllers/Transportation/TransportationController.php`)

RESTful API endpoints protected by JWT middleware, organized by resource type.

## API Endpoints

### Vehicle Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/vehicles` | Create new vehicle |
| GET | `/api/transportation/vehicles` | Get all vehicles (filter by status, type) |
| GET | `/api/transportation/vehicles/{id}` | Get vehicle by ID |
| PUT | `/api/transportation/vehicles/{id}` | Update vehicle |
| DELETE | `/api/transportation/vehicles/{id}` | Delete vehicle |

### Stop Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/stops` | Create new stop |
| GET | `/api/transportation/stops` | Get all stops (filter by active status) |
| GET | `/api/transportation/stops/{id}` | Get stop by ID |
| PUT | `/api/transportation/stops/{id}` | Update stop |
| DELETE | `/api/transportation/stops/{id}` | Delete stop |

### Route Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/routes` | Create new route |
| GET | `/api/transportation/routes` | Get all routes (filter by status) |
| GET | `/api/transportation/routes/{id}` | Get route by ID with stops |
| PUT | `/api/transportation/routes/{id}` | Update route |
| DELETE | `/api/transportation/routes/{id}` | Delete route |
| POST | `/api/transportation/routes/{routeId}/stops` | Add stop to route |
| DELETE | `/api/transportation/routes/{routeId}/stops/{stopId}` | Remove stop from route |

### Driver Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/drivers` | Create new driver |
| GET | `/api/transportation/drivers` | Get all drivers (filter by status) |
| GET | `/api/transportation/drivers/{id}` | Get driver by ID |
| PUT | `/api/transportation/drivers/{id}` | Update driver |
| DELETE | `/api/transportation/drivers/{id}` | Delete driver |

### Schedule Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/schedules` | Create new schedule |
| GET | `/api/transportation/schedules` | Get all schedules (filter by status, route, driver) |
| GET | `/api/transportation/schedules/{id}` | Get schedule by ID |
| PUT | `/api/transportation/schedules/{id}` | Update schedule |
| DELETE | `/api/transportation/schedules/{id}` | Delete schedule |

### Assignment Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/assignments` | Assign student to route |
| GET | `/api/transportation/assignments/{id}` | Get assignment by ID |
| PUT | `/api/transportation/assignments/{id}` | Update assignment |
| DELETE | `/api/transportation/assignments/{id}` | Delete assignment |
| GET | `/api/transportation/students/{studentId}/assignments` | Get student assignments |
| GET | `/api/transportation/routes/{routeId}/assignments` | Get route assignments |

### Attendance Tracking

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/attendance` | Record student attendance |
| GET | `/api/transportation/attendance/{id}` | Get attendance record |
| GET | `/api/transportation/students/{studentId}/attendance` | Get student attendance (date range) |
| GET | `/api/transportation/routes/{routeId}/attendance` | Get route attendance for date |

### Vehicle Tracking

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/tracking` | Record vehicle GPS location |
| GET | `/api/transportation/vehicles/{vehicleId}/location` | Get latest vehicle location |

### Fee Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/fees` | Create transportation fee |
| GET | `/api/transportation/fees/{id}` | Get fee by ID |
| POST | `/api/transportation/fees/{id}/pay` | Mark fee as paid |
| GET | `/api/transportation/students/{studentId}/fees` | Get student fees |
| GET | `/api/transportation/fees/pending` | Get all pending fees |

### Notification Management

| Method | Endpoint | Description |
|---------|-----------|-------------|
| POST | `/api/transportation/notifications` | Create notification |
| POST | `/api/transportation/notifications/delay` | Create bus delay notification |
| POST | `/api/transportation/notifications/emergency` | Create emergency notification |
| POST | `/api/transportation/notifications/{id}/send` | Send notification |

### Reports & Analytics

| Method | Endpoint | Description |
|---------|-----------|-------------|
| GET | `/api/transportation/reports/occupancy/{routeId}` | Get vehicle occupancy data |
| GET | `/api/transportation/reports/analytics/{routeId}` | Get route analytics (date range) |
| GET | `/api/transportation/reports/summary` | Get transportation system summary |

## Key Features

### 1. Vehicle Management

- Track school bus fleet
- Monitor vehicle status (active, maintenance, retired)
- Track insurance and registration expiry dates
- Manage vehicle capacity and specifications

### 2. Route Planning

- Create bus routes with multiple stops
- Define stop sequence and timing
- Set route capacity and status
- Link stops to routes with pickup/dropoff times

### 3. Stop Management

- Create pickup/dropoff locations
- Store GPS coordinates
- Mark stops as active/inactive
- Estimate time from route start

### 4. Driver Management

- Manage driver profiles
- Track license information and expiry
- Monitor driver status
- Store certifications and contact info

### 5. Student Assignment

- Assign students to routes
- Define pickup/dropoff stops per student
- Track assignment status
- Prevent duplicate active assignments

### 6. Attendance Tracking

- Record student boarding (morning)
- Record student alighting (afternoon)
- Track status: present, absent, late, missed
- Maintain historical attendance records

### 7. GPS Tracking

- Record real-time vehicle location
- Store latitude, longitude, speed, direction
- Track vehicle status (moving, stopped, idle)
- Retrieve latest location for parents

### 8. Fee Management

- Create transportation fees
- Track payment status
- Mark fees as paid with payment method
- Query pending and overdue fees

### 9. Parent Notifications

- Send bus delay alerts
- Send emergency notifications
- Send route updates
- Track notification status (sent/pending)

### 10. Analytics & Reporting

- Vehicle occupancy rates
- Attendance statistics
- Route performance metrics
- System-wide summary reports

## Integration Points

### Student Information System (Issue #229)
- Student data retrieved from User model
- Student assignments linked to user IDs

### Notification System (Issue #257)
- Parent notifications sent via notification system
- Transport notifications created for alerts

### Fee Management System (Issue #200)
- Transportation fees integrated with billing
- Payment processing through billing system

## Security Considerations

### Student Safety
- Attendance tracking for all students
- Real-time GPS monitoring
- Emergency notification capabilities

### Data Privacy
- Student location data access restricted
- Driver information protected
- Parent notification opt-out supported

### Access Control
- All endpoints protected by JWT middleware
- Role-based access control
- Audit logging for all operations

## Best Practices

### Route Optimization
- Use proximity-based stop ordering
- Consider traffic patterns
- Balance passenger load across routes
- Plan for weather and road conditions

### Driver Management
- Track license expiry dates
- Maintain certification records
- Schedule regular training
- Monitor driver performance

### Parent Communication
- Send proactive delay notifications
- Provide real-time tracking access
- Establish clear emergency procedures
- Regular status updates

## GPS Integration

### Recommended Services
- Google Maps API
- Mapbox API
- TomTom API
- HERE Technologies

### Implementation
- Use REST API for location updates
- Update interval: 30 seconds (configurable)
- Store historical tracking data
- Implement geofencing for alerts

## Testing

Run tests with:
```bash
php bin/hyperf.php test tests/Feature/TransportationManagementTest.php
```

## Database Migration

Apply migrations to create transportation tables:
```bash
php bin/hyperf.php migrate
```

## Troubleshooting

### Common Issues

**Duplicate Student Assignment**
- Students can only have one active assignment
- Previous assignments must be terminated before creating new ones

**Route Stop Sequence**
- Stops must have unique sequence order within a route
- Invalid sequences cause data inconsistencies

**Vehicle Capacity Exceeded**
- Cannot assign more students than vehicle capacity
- Check occupancy before assigning

**GPS Tracking Not Working**
- Verify vehicle GPS hardware is operational
- Check API credentials for GPS service
- Ensure network connectivity on vehicle

## Future Enhancements

Out of scope for current implementation but recommended:

1. **Advanced Route Optimization** - Use algorithms for efficient routing
2. **Mobile Apps** - Driver and parent mobile applications
3. **Automated Notifications** - Rule-based automatic alerts
4. **Predictive Analytics** - Predict delays based on patterns
5. **Maintenance Scheduling** - Automated preventive maintenance reminders
6. **Fuel Management** - Track fuel consumption and costs
7. **Multi-school Support** - Coordinate transportation across multiple schools

## Support

For issues or questions:
- Check API documentation
- Review database schema
- Examine service layer logic
- Run test suite for verification
