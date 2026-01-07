# Health Management System Documentation

## Overview

The Health Management System provides comprehensive student health record management capabilities including medical records, medications, immunizations, allergies, health screenings, emergency contacts, medical incidents, nurse visits, and health alerts. This system is designed to help schools maintain accurate health information, ensure student safety, and comply with health regulations.

## Features

### 1. Health Records
- Comprehensive student health profiles
- Blood type tracking
- Medical history documentation
- Chronic conditions management
- Previous surgeries records
- Family medical history
- Dietary restrictions
- Physical disabilities documentation

### 2. Medication Management
- Complete medication tracking with dosage and schedules
- Prescription information management
- Administration method specification
- Parent consent tracking
- School nurse assignment
- Refrigeration requirements
- Medication status tracking (active, completed, discontinued, on_hold)
- Discontinuation reason documentation

### 3. Immunization Tracking
- Immunization record management
- Vaccine type and manufacturer tracking
- Administration date and facility information
- Next due date tracking
- Compliance monitoring
- Exemption documentation
- Automated overdue detection

### 4. Allergy Management
- Allergy severity levels (mild, moderate, severe, life_threatening)
- Allergy types (food, medication, environmental, insect, other)
- Reaction documentation
- Emergency protocol specification
- EpiPen requirement tracking
- Treatment plan management
- Severe allergy alerts

### 5. Health Screenings
- Vision screening records
- Hearing screening records
- Other health screenings (scoliosis, etc.)
- Screening status tracking (normal, abnormal, needs_follow_up, incomplete)
- Findings and recommendations
- Follow-up date tracking
- Performed by information

### 6. Emergency Contacts
- Primary emergency contact designation
- Multiple contact management
- Relationship tracking
- Phone and email information
- Address documentation
- Authorized pickup designation
- Medical consent tracking

### 7. Medical Incidents
- Incident type and severity tracking
- Date and time documentation
- Injury details
- Treatment provided
- Reported by and treated by information
- Follow-up actions and dates
- Parent notification tracking
- Location information
- Incident status management (open, investigating, resolved, closed)
- Critical alert generation for severe incidents

### 8. Nurse Visits
- Visit reason and complaint tracking
- Symptoms documentation
- Examination and treatment records
- Medication administration
- Disposition tracking
- Return time documentation
- Parent notification
- Referral tracking
- Nurse assignment

### 9. Health Alerts
- Alert type categorization
- Priority levels (low, medium, high, critical)
- Automated alert generation
- Due date tracking
- Recipient management
- Alert status (pending, sent, acknowledged, resolved)
- Overdue alert detection

## API Endpoints

### Health Records

#### Get Health Record
```
GET /api/health/students/{studentId}/health-record
```

Returns comprehensive health record for a student including medications, immunizations, and allergies.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "student_id": "uuid",
    "blood_type": "A+",
    "medical_history": "...",
    "medications": [...],
    "immunizations": [...],
    "allergies": [...]
  },
  "message": "Health record retrieved successfully",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

#### Create Health Record
```
POST /api/health/health-records
```

Creates a new health record for a student.

**Request Body:**
```json
{
  "student_id": "uuid",
  "blood_type": "A+",
  "medical_history": "...",
  "chronic_conditions": "...",
  "dietary_restrictions": "..."
}
```

### Medications

#### Get Student Medications
```
GET /api/health/students/{studentId}/medications?status=active
```

Returns all medications for a student, optionally filtered by status.

#### Create Medication
```
POST /api/health/medications
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "medication_name": "Advil",
  "dosage": "10mg",
  "frequency": "Every 6 hours",
  "administration_method": "oral",
  "start_date": "2024-01-01",
  "end_date": "2024-01-07",
  "requires_refrigeration": false,
  "parent_consent": true
}
```

#### Update Medication
```
PUT /api/health/medications/{id}
```

#### Delete Medication
```
DELETE /api/health/medications/{id}
```

### Immunizations

#### Get Student Immunizations
```
GET /api/health/students/{studentId}/immunizations?status=completed
```

Returns all immunizations for a student, optionally filtered by status.

#### Get Immunization Compliance
```
GET /api/health/students/{studentId}/immunization-compliance
```

Returns immunization compliance statistics including:
- Total immunizations
- Completed count
- Overdue count
- Due count
- Compliance rate percentage
- List of overdue immunizations

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 10,
    "completed": 8,
    "overdue": 1,
    "due": 1,
    "compliance_rate": 80.0,
    "overdue_immunizations": [...]
  },
  "message": "Immunization compliance retrieved successfully",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

#### Create Immunization
```
POST /api/health/immunizations
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "vaccine_name": "MMR",
  "vaccine_type": "Live Attenuated",
  "manufacturer": "Merck",
  "lot_number": "123456",
  "administration_date": "2024-01-01",
  "administering_facility": "School Clinic",
  "status": "completed"
}
```

### Allergies

#### Get Student Allergies
```
GET /api/health/students/{studentId}/allergies?severe_only=true
```

Returns all allergies for a student. Use `severe_only=true` to get only severe/life-threatening allergies.

#### Get Severe Allergies Alert
```
GET /api/health/students/{studentId}/severe-allergies-alert
```

Returns formatted severe allergy information for emergency purposes including allergen, severity, reactions, and emergency protocols.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "allergen": "Peanuts",
      "severity": "life_threatening",
      "reactions": "Anaphylaxis",
      "emergency_protocol": "Administer EpiPen immediately, call 911",
      "requires_epipen": true
    }
  ],
  "message": "Severe allergies alert retrieved successfully",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

#### Create Allergy
```
POST /api/health/allergies
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "allergen": "Peanuts",
  "allergy_type": "food",
  "severity": "life_threatening",
  "reactions": "Anaphylaxis, hives",
  "emergency_protocol": "Administer EpiPen immediately, call 911",
  "requires_epipen": true
}
```

### Emergency Contacts

#### Get Emergency Contacts
```
GET /api/health/students/{studentId}/emergency-contacts
```

Returns all emergency contacts for a student, ordered by primary contact first.

#### Create Emergency Contact
```
POST /api/health/emergency-contacts
```

**Request Body:**
```json
{
  "student_id": "uuid",
  "full_name": "John Doe",
  "relationship": "Father",
  "phone": "555-1234",
  "secondary_phone": "555-5678",
  "email": "john@example.com",
  "address": "123 Main St",
  "primary_contact": true,
  "authorized_pickup": true,
  "medical_consent": true
}
```

### Medical Incidents

#### Get Medical Incidents
```
GET /api/health/medical-incidents?student_id=uuid&severity=severe&status=open
```

Returns medical incidents, filtered by optional parameters.

#### Create Medical Incident
```
POST /api/health/medical-incidents
```

Creates a medical incident record. Automatically generates a critical health alert for severe incidents.

**Request Body:**
```json
{
  "student_id": "uuid",
  "incident_date": "2024-01-01T10:30:00",
  "incident_type": "Fall",
  "description": "Student fell on playground",
  "injury_details": "Scraped knee",
  "severity": "moderate",
  "treatment_provided": "Cleaned and bandaged wound",
  "location": "Playground",
  "status": "open",
  "parent_notified": true,
  "created_by": "uuid"
}
```

### Reports and Analytics

#### Get Health Report
```
GET /api/health/students/{studentId}/health-report
```

Generates a comprehensive health report for a student including:
- Student information
- Health record
- Medications
- Immunizations and compliance
- Allergies (all and severe)
- Emergency contacts
- Health screenings
- Medical incidents
- Nurse visits

#### Get Health Summary
```
GET /api/health/health-summary
```

Returns system-wide health statistics:
- Total students
- Students with health records
- Active medications
- Immunization statistics
- Allergy statistics
- Medical incidents this month
- Open incidents
- Nurse visits this month
- Pending and critical health alerts

**Response:**
```json
{
  "success": true,
  "data": {
    "total_students": 500,
    "students_with_health_records": 450,
    "active_medications": 75,
    "completed_immunizations": 1200,
    "overdue_immunizations": 15,
    "students_with_allergies": 80,
    "severe_allergies": 25,
    "medical_incidents_this_month": 5,
    "open_incidents": 3,
    "nurse_visits_this_month": 120,
    "pending_health_alerts": 10,
    "critical_health_alerts": 2
  },
  "message": "Health summary retrieved successfully",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

## Security and Privacy

### HIPAA and FERPA Compliance

The Health Management System is designed with privacy and security as top priorities:

1. **Access Control**: All health endpoints require JWT authentication
2. **Role-Based Access**: Different access levels for nurses, administrators, and parents
3. **Audit Logging**: All access and modifications to health records are logged
4. **Data Encryption**: Sensitive health information should be encrypted at rest and in transit
5. **Parental Consent**: Required for medication administration and medical procedures
6. **Emergency Access**: Protocols for emergency access with proper documentation and audit trail

### Data Protection

- All health data is stored in secure database tables
- Soft delete implementation for record retention
- No sensitive health data in error messages or logs
- Proper sanitization of all inputs

## Integration

### Student Information System
Health records are linked to students via `student_id` foreign key, enabling integration with the Student Information System for:
- Student identification
- Grade and class information
- Academic history context

### Notification System (Future)
The Health Alert system is designed to integrate with the notification system (Issue #257) for:
- Automated medication reminders
- Immunization due notifications
- Health alert delivery to parents
- Emergency incident notifications

### Parent Portal (Future)
Health information can be made available through the Parent Portal (Issue #232) with:
- Read-only access to their child's health records
- Medication schedule viewing
- Immunization status
- Communication channels with school health staff

## Best Practices

### For School Nurses

1. Always verify student identity before accessing health records
2. Double-check severe allergy information before any treatment
3. Document all nurse visits thoroughly
4. Follow emergency protocols for severe reactions
5. Update immunization records promptly after administration
6. Report all medical incidents with complete information

### For Administrators

1. Regularly review immunization compliance reports
2. Monitor critical health alerts
3. Ensure all students have up-to-date emergency contacts
4. Review medical incident trends
5. Maintain proper documentation for all health record changes

### For Parents

1. Keep emergency contact information current
2. Provide consent for medications promptly
3. Inform school of new allergies or health conditions
4. Respond to health alerts in a timely manner
5. Review student health records periodically

## Error Handling

All API endpoints follow a consistent error response format:

```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": {...}
  },
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

Common error codes:
- `HEALTH_RECORD_NOT_FOUND`: Health record does not exist
- `MEDICATION_CREATION_ERROR`: Failed to create medication
- `IMMUNIZATION_CREATION_ERROR`: Failed to create immunization
- `ALLERGY_CREATION_ERROR`: Failed to create allergy
- `EMERGENCY_CONTACT_CREATION_ERROR`: Failed to create emergency contact
- `MEDICAL_INCIDENT_CREATION_ERROR`: Failed to create medical incident
- `VALIDATION_ERROR`: Request validation failed

## Support and Troubleshooting

For issues or questions regarding the Health Management System:
1. Check error messages for specific guidance
2. Verify student ID references are correct
3. Ensure all required fields are provided
4. Review audit logs for access issues
5. Contact system administrator for technical problems
