# Transportation Management System

## Overview

The Transportation Management System provides comprehensive tools for managing school buses, routes, drivers, and student transportation. This module ensures safe, efficient, and well-coordinated transportation services for students.

## Features

### 1. Vehicle Management
- Bus fleet registration and tracking
- Vehicle capacity monitoring
- Document expiry tracking (registration, insurance, inspection)
- Vehicle status management (available, maintenance, out of service)
- Integration with GPS tracking

### 2. Driver Management
- Driver profiles with license and certification tracking
- License expiry monitoring
- Driver assignment to routes
- Availability status management

### 3. Route Management
- Route creation with multiple stops
- Stop sequencing and timing
- Route capacity management
- Active/inactive route status

### 4. Stop Management
- Pickup/dropoff location registration
- GPS coordinates for precise locations
- Nearby stop search functionality
- Distance calculation using Haversine formula

### 5. Schedule Management
- Weekly schedule assignments
- Driver-vehicle-route combinations
- Day-specific routing

### 6. Student Assignment
- Assign students to routes
- Pickup/dropoff stop configuration
- Session type management (morning, afternoon, both)
- Fee tracking per assignment
- Assignment activation/deactivation

### 7. Attendance Tracking
- Student boarding recording
- Alighting tracking
- Attendance status management
- Daily attendance reports

### 8. GPS Tracking
- Real-time vehicle location tracking
- Speed and heading monitoring
- Odometer tracking
- Active vehicle location retrieval
- Location history per vehicle

### 9. Incident Management
- Incident reporting with severity levels
- Incident resolution tracking
- Multiple incident types (breakdown, accident, delay, etc.)
- Open/closed status management

### 10. Statistics and Reporting
- Fleet utilization metrics
- Driver availability statistics
- Route capacity utilization
- Attendance summaries
- Incident tracking dashboard

## API Endpoints

### Vehicles
- `POST /api/transport/vehicles` - Create vehicle
- `GET /api/transport/vehicles` - List vehicles (with filters: status, is_active)
- `GET /api/transport/vehicles/{id}` - Get vehicle by ID
- `PUT /api/transport/vehicles/{id}` - Update vehicle
- `DELETE /api/transport/vehicles/{id}` - Delete vehicle

### Drivers
- `POST /api/transport/drivers` - Create driver
- `GET /api/transport/drivers` - List drivers (with filters: status, is_active)
- `GET /api/transport/drivers/{id}` - Get driver by ID
- `PUT /api/transport/drivers/{id}` - Update driver

### Stops
- `POST /api/transport/stops` - Create stop
- `GET /api/transport/stops` - List stops (with filters: type, is_active)
- `GET /api/transport/stops/nearby` - Get nearby stops (params: latitude, longitude, radius)
- `GET /api/transport/stops/{id}` - Get stop by ID
- `PUT /api/transport/stops/{id}` - Update stop
- `DELETE /api/transport/stops/{id}` - Delete stop

### Routes
- `POST /api/transport/routes` - Create route
- `GET /api/transport/routes` - List routes (with filters: status, is_active)
- `GET /api/transport/routes/{id}` - Get route by ID
- `PUT /api/transport/routes/{id}` - Update route
- `DELETE /api/transport/routes/{id}` - Delete route

### Schedules
- `POST /api/transport/schedules` - Create schedule
- `GET /api/transport/schedules` - List schedules (with filters: day_of_week, route_id, vehicle_id, driver_id)
- `GET /api/transport/schedules/{id}` - Get schedule by ID
- `PUT /api/transport/schedules/{id}` - Update schedule

### Assignments
- `POST /api/transport/assignments` - Assign student to route
- `GET /api/transport/assignments/student/{studentId}` - Get student's assignments
- `GET /api/transport/assignments/route/{routeId}` - Get route's assignments

### Attendance
- `POST /api/transport/attendance` - Record attendance
- `GET /api/transport/attendance/today/{routeId}` - Get today's attendance for route (optional: date param)

### GPS Tracking
- `POST /api/transport/tracking` - Record GPS location
- `GET /api/transport/tracking/active` - Get active vehicle locations

### Incidents
- `POST /api/transport/incidents` - Report incident
- `GET /api/transport/incidents` - List incidents (with filters: status, severity, vehicle_id, route_id)
- `POST /api/transport/incidents/{id}/resolve` - Resolve incident

### Statistics
- `GET /api/transport/statistics` - Get comprehensive statistics

## Database Schema

### Tables

1. **transport_vehicles**
   - id (UUID, primary key)
   - plate_number (unique)
   - vehicle_type
   - capacity
   - make, model, manufacture_year
   - registration_number, registration_expiry
   - insurance_number, insurance_expiry
   - inspection_number, inspection_expiry
   - is_active, status

2. **transport_drivers**
   - id (UUID, primary key)
   - user_id (foreign key to users)
   - name, license_number (unique), license_expiry
   - phone, address
   - certification_type, certification_expiry
   - is_active, status

3. **transport_stops**
   - id (UUID, primary key)
   - name, address
   - latitude, longitude
   - type (pickup/dropoff)
   - is_active

4. **transport_routes**
   - id (UUID, primary key)
   - name, code (unique)
   - description
   - start_time, end_time
   - capacity
   - is_active, status

5. **transport_route_stops**
   - id (UUID, primary key)
   - route_id (foreign key)
   - stop_id (foreign key)
   - stop_order
   - arrival_time, departure_time
   - fare

6. **transport_schedules**
   - id (UUID, primary key)
   - route_id (foreign key)
   - vehicle_id (foreign key)
   - driver_id (foreign key)
   - day_of_week
   - departure_time, arrival_time
   - is_active

7. **transport_assignments**
   - id (UUID, primary key)
   - student_id (foreign key to users)
   - route_id (foreign key)
   - pickup_stop_id, dropoff_stop_id (foreign keys to stops)
   - session_type (morning/afternoon/both)
   - start_date, end_date
   - fee
   - is_active

8. **transport_attendance**
   - id (UUID, primary key)
   - assignment_id (foreign key)
   - student_id (foreign key to users)
   - route_id (foreign key)
   - attendance_date
   - boarding_time, alighting_time
   - status
   - remarks

9. **transport_tracking**
   - id (UUID, primary key)
   - vehicle_id (foreign key)
   - route_id (foreign key)
   - latitude, longitude
   - speed, heading
   - odometer
   - recorded_at

10. **transport_incidents**
   - id (UUID, primary key)
   - vehicle_id, route_id, driver_id (foreign keys)
   - incident_type
   - severity (low/medium/high/critical)
   - description
   - incident_time
   - location
   - status (open/resolved)
   - resolution
   - resolved_at

## Integration Points

### Student Information System
- Uses `User` model for students
- Links assignments to student IDs

### Notification System (Issue #257)
- Integrate for parent notifications on:
  - Attendance confirmation
  - Delay alerts
  - Incident notifications
  - Route changes

### Fee Management System (Issue #200)
- Links transportation fees to billing system
- Assignment fee tracking

## Security Considerations

- All endpoints protected with JWT authentication
- Rate limiting enabled for tracking updates
- Sensitive data (license numbers) requires proper authorization
- Document expiry alerts prevent unauthorized operations
- Driver verification before assignment

## Performance Considerations

- Database indexes on frequently queried fields
- Efficient distance calculation for nearby stops
- Tracking records can be archived/purged periodically
- Pagination for large result sets (vehicles, routes, stops lists)

## Future Enhancements

1. Real-time WebSocket for GPS tracking
2. Route optimization algorithms
3. Advanced reporting and analytics
4. Mobile app integration
5. Parent notification preferences
6. Geofencing for route compliance
7. Maintenance scheduling and reminders
8. Fuel consumption tracking
9. Route conflict detection
10. Multi-school routing support

## Testing

Run the test suite:
```bash
vendor/bin/co-phpunit tests/Feature/TransportationSystemTest.php
```

## Migration

Run database migrations:
```bash
php artisan migrate
```

This will create all 10 transportation tables with proper indexes and foreign key constraints.