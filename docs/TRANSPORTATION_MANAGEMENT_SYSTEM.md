# Transportation Management System

## Overview

The Transportation Management System provides comprehensive management capabilities for school buses, routes, and student transportation logistics. This module includes:

- Bus route planning and optimization
- Student transportation registration and assignment
- Vehicle and driver management
- Transportation fee management and billing
- Safety incident reporting and monitoring
- Real-time GPS tracking structure (for future integration)

## Architecture

### Database Schema

The system uses the following tables:

- `transportation_routes` - Bus routes with stops and scheduling
- `transportation_registrations` - Student transportation registrations
- `transportation_vehicles` - Bus/vehicle information
- `transportation_drivers` - Driver profiles and assignments
- `transportation_assignments` - Student to route assignments
- `transportation_fees` - Fee billing and payment tracking
- `transportation_incidents` - Safety incident reports

### Models

All models are located in `App\Models\Transportation\`:

- `TransportationRoute` - Route management with stops, vehicle, and driver relationships
- `TransportationRegistration` - Student registration with route assignment
- `TransportationVehicle` - Vehicle information with maintenance tracking
- `TransportationDriver` - Driver profiles with license and background checks
- `TransportationAssignment` - Active student-route assignments
- `TransportationFee` - Fee billing and payment tracking
- `TransportationIncident` - Safety incident reporting with resolution tracking

### Service Layer

`TransportationManagementService` provides business logic for:

- Route creation, update, and deletion
- Student registration and assignment management
- Vehicle and driver management
- Fee calculation and billing
- Incident reporting and resolution
- Student and vehicle schedule retrieval

### API Endpoints

All endpoints are protected with JWT authentication and role-based access control:

#### Route Management
- `POST /api/transportation/routes` - Create a new route
- `PUT /api/transportation/routes/{id}` - Update an existing route
- `DELETE /api/transportation/routes/{id}` - Delete a route

#### Registration Management
- `POST /api/transportation/registrations` - Create student registration
- `PUT /api/transportation/registrations/{id}` - Update registration

#### Vehicle Management
- `POST /api/transportation/vehicles` - Create a new vehicle
- `PUT /api/transportation/vehicles/{id}` - Update vehicle information

#### Driver Management
- `POST /api/transportation/drivers` - Create driver profile
- `PUT /api/transportation/drivers/{id}` - Update driver information

#### Student Assignment
- `POST /api/transportation/assign-student` - Assign student to route

#### Fee Management
- `POST /api/transportation/fees` - Create transportation fee
- `PUT /api/transportation/fees/{id}` - Update fee information

#### Incident Reporting
- `POST /api/transportation/incidents` - Report safety incident
- `PUT /api/transportation/incidents/{id}` - Update incident

## Usage Examples

### Creating a Route

```bash
POST /api/transportation/routes
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "route_name": "Route 1 - North Area",
  "route_description": "Morning route covering northern residential area",
  "start_location": "Main Gate",
  "end_location": "North Terminal",
  "start_time": "07:00:00",
  "end_time": "17:00:00",
  "distance_km": 15.5,
  "stops": [
    {"name": "Stop 1", "time": "07:00", "location": "North Street Corner"},
    {"name": "Stop 2", "time": "07:15", "location": "School Gate"}
  ],
  "vehicle_id": "vehicle-uuid",
  "driver_id": "driver-uuid"
}
```

### Registering a Student

```bash
POST /api/transportation/registrations
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "student_id": "student-uuid",
  "route_id": "route-uuid",
  "stop_id": "stop-1",
  "pickup_location": "123 Main Street",
  "dropoff_location": "School Gate",
  "emergency_contact": {
    "name": "John Parent",
    "phone": "+62812345678"
  },
  "registration_date": "2025-01-15"
}
```

### Creating a Vehicle

```bash
POST /api/transportation/vehicles
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "vehicle_number": "BUS-001",
  "license_plate": "B-1234-XYZ",
  "vehicle_type": "bus",
  "capacity": 40,
  "make": "Mercedes",
  "model": "Sprinter",
  "year": 2023,
  "status": "active"
}
```

### Reporting an Incident

```bash
POST /api/transportation/incidents
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "route_id": "route-uuid",
  "vehicle_id": "vehicle-uuid",
  "driver_id": "driver-uuid",
  "incident_type": "delay",
  "incident_date": "2025-01-15 07:30:00",
  "description": "Bus arrived 15 minutes late due to traffic",
  "severity": "minor",
  "status": "open",
  "reported_by": {
    "name": "Parent",
    "phone": "+62812345678"
  }
}
```

## Status Values

### Route Status
- `active` - Route is currently active
- `inactive` - Route is temporarily inactive
- `maintenance` - Route is under maintenance

### Registration Status
- `pending` - Registration awaiting approval
- `approved` - Registration approved
- `rejected` - Registration rejected
- `active` - Student currently using route

### Vehicle Status
- `active` - Vehicle is operational
- `maintenance` - Vehicle under maintenance
- `inactive` - Vehicle temporarily unavailable

### Incident Severity
- `minor` - Low severity, no immediate danger
- `moderate` - Medium severity, requires attention
- `severe` - High severity, immediate action required
- `critical` - Critical incident, emergency response

### Incident Status
- `open` - Incident reported, not yet resolved
- `investigating` - Incident under investigation
- `resolved` - Incident resolved
- `closed` - Incident closed after resolution

### Fee Payment Status
- `unpaid` - Fee not paid
- `partial` - Partial payment made
- `paid` - Fee fully paid
- `overdue` - Fee past due date

## Integration Points

### Existing Systems
- **Student Information System** - Student data for registrations and assignments
- **User Management** - User authentication and driver profiles
- **Notification System** - Parent notifications for delays and incidents (future integration)
- **Financial System** - Fee billing integration (future)

### Future Enhancements
- Real-time GPS tracking integration
- Mobile app for parent notifications
- Route optimization algorithms
- Automated fee calculation based on distance
- Parent portal for transportation information

## Security Considerations

- All endpoints require JWT authentication
- Role-based access control (Super Admin, Kepala Sekolah, Staf TU)
- Audit logging for all operations
- Sensitive data encryption for driver licenses and personal information
- Vehicle and driver background check tracking

## Testing

Run the transportation management test suite:

```bash
vendor/bin/co-phpunit tests/Feature/TransportationManagementTest.php
```

Tests cover:
- Route creation, update, and deletion
- Vehicle creation and update
- Driver creation and update
- Fee creation and management
- Incident reporting and resolution
- Student registration and assignment
- Route and student data retrieval

## Migration

After deploying this feature, run the migration:

```bash
php artisan migrate
```

This creates 7 new tables for transportation management.

## Troubleshooting

### Common Issues

**Issue**: Routes not displaying
- Check that routes have `status = 'active'`
- Verify vehicle and driver are assigned and active

**Issue**: Student assignment not working
- Ensure student has valid student_id
- Verify route exists and is active
- Check stop_id exists in route stops

**Issue**: Fee calculation incorrect
- Verify amount is in correct format (decimal)
- Check academic_year format matches system
- Ensure currency is valid (IDR by default)

**Issue**: Incident not updating
- Verify user has permissions to update incidents
- Check incident status allows updates
- Ensure resolved_date is provided for status change to 'resolved'

## License

This module follows the same MIT license as the Malnu Backend project.
