# Health and Medical Records Management System

## Overview

The Health and Medical Records Management System provides comprehensive tracking of student health information, immunizations, medications, emergencies, screenings, incidents, and health alerts. This system ensures proper medical documentation, emergency response preparedness, and compliance with health regulations.

## System Architecture

### Core Components

1. **Health Records** - Central health profile for each student
2. **Immunizations** - Vaccination records and compliance tracking
3. **Medications** - Current medications and administration logs
4. **Emergencies** - Emergency contact information
5. **Health Screenings** - Vision, hearing, and health assessments
6. **Medical Incidents** - Medical incident reports and follow-up
7. **Health Alerts** - Active health alerts and allergy notifications
8. **Nurse Visits** - School nurse visit documentation

### Database Schema

- `health_records` - Student medical profiles
- `health_immunizations` - Vaccination records
- `health_medications` - Medication administration
- `health_emergencies` - Emergency contacts
- `health_screenings` - Health assessment results
- `health_incidents` - Medical incident reports
- `health_alerts` - Active health alerts and allergies
- `health_nurse_visits` - Nurse visit documentation

## API Endpoints

### Health Records

- `GET /api/health/records/{studentId}` - Get student health records
- `POST /api/health/records` - Create health record
- `PUT /api/health/records/{id}` - Update health record
- `DELETE /api/health/records/{id}` - Delete health record

**Health Record Fields:**
- `student_id` (required) - Related student
- `blood_type` (optional) - Blood type (A+, B-, O+, etc.)
- `chronic_conditions` (optional) - Chronic medical conditions
- `dietary_restrictions` (optional) - Food allergies and dietary needs
- `family_medical_history` (optional) - Family medical history
- `physical_disabilities` (optional) - Physical disabilities or limitations
- `notes` (optional) - Additional notes

### Immunizations

- `GET /api/health/immunizations/{studentId}` - Get student immunizations
- `POST /api/health/immunizations` - Create immunization record
- `PUT /api/health/immunizations/{id}` - Update immunization
- `DELETE /api/health/immunizations/{id}` - Delete immunization

**Immunization Fields:**
- `health_record_id` (required) - Related health record
- `vaccine_name` (required) - Vaccine name
- `date_administered` (required) - Date vaccine was given
- `next_due_date` (optional) - Next scheduled vaccination date
- `administered_by` (optional) - Who administered the vaccine
- `notes` (optional) - Additional notes

### Emergency Contacts

- `GET /api/health/emergencies/{studentId}` - Get emergency contacts
- `POST /api/health/emergencies` - Create emergency contact
- `PUT /api/health/emergencies/{id}` - Update emergency contact
- `DELETE /api/health/emergencies/{id}` - Delete emergency contact

**Emergency Contact Fields:**
- `health_record_id` (required) - Related health record
- `contact_name` (required) - Contact person name
- `relationship` (required) - Relationship to student (Father, Mother, Guardian, etc.)
- `phone` (required) - Contact phone number
- `is_primary` (optional) - Is this the primary emergency contact (default: false)

### Medications

- `GET /api/health/medications/{studentId}` - Get student medications
- `POST /api/health/medications` - Create medication record
- `PUT /api/health/medications/{id}` - Update medication
- `DELETE /api/health/medications/{id}` - Delete medication

**Medication Fields:**
- `health_record_id` (required) - Related health record
- `medication_name` (required) - Name of medication
- `dosage` (optional) - Dosage amount (e.g., "10mg")
- `frequency` (optional) - How often to take (e.g., "Twice daily")
- `start_date` (required) - When medication starts
- `end_date` (optional) - When medication ends
- `administered_by` (optional) - Who administers medication
- `notes` (optional) - Additional notes

### Health Screenings

- `GET /api/health/screenings/{studentId}` - Get health screenings
- `POST /api/health/screenings` - Create screening record
- `PUT /api/health/screenings/{id}` - Update screening
- `DELETE /api/health/screenings/{id}` - Delete screening

**Screening Fields:**
- `health_record_id` (required) - Related health record
- `screening_type` (required) - Type of screening (Vision, Hearing, Dental, Physical, etc.)
- `screening_date` (required) - Date screening was performed
- `results` (optional) - Screening results
- `notes` (optional) - Additional notes
- `conducted_by` (optional) - Who conducted screening

### Medical Incidents

- `GET /api/health/incidents/{studentId}` - Get medical incidents
- `POST /api/health/incidents` - Create incident report
- `PUT /api/health/incidents/{id}` - Update incident
- `DELETE /api/health/incidents/{id}` - Delete incident

**Incident Fields:**
- `health_record_id` (optional) - Related health record
- `incident_date` (required) - Date of incident
- `incident_type` (required) - Type of incident (Fall, Injury, Illness, Allergic Reaction, etc.)
- `description` (required) - Detailed description
- `severity` (optional) - Severity level (minor, moderate, severe, critical; default: moderate)
- `reported_by` (optional) - Who reported incident
- `action_taken` (optional) - Actions taken in response
- `status` (optional) - Incident status (open, in_progress, closed; default: open)

### Health Alerts

- `GET /api/health/alerts/{studentId}` - Get active health alerts
- `POST /api/health/alerts` - Create health alert
- `PUT /api/health/alerts/{id}` - Update health alert
- `POST /api/health/alerts/{id}/deactivate` - Deactivate alert
- `DELETE /api/health/alerts/{id}` - Delete alert

**Alert Fields:**
- `health_record_id` (required) - Related health record
- `alert_type` (required) - Type of alert (Allergy, Medical Condition, etc.)
- `description` (required) - Alert description
- `severity` (optional) - Severity level (minor, moderate, severe, critical; default: moderate)
- `is_active` (optional) - Is alert currently active (default: true)
- `notified_parent` (optional) - Has parent been notified (default: false)

## Role-Based Access Control

### Access Levels

- **Super Admin** - Full access to all health management features
- **Kepala Sekolah** (School Administration) - Full access to health records and management
- **Staf TU** (Administrative Staff) - Full access to health records and management
- **Guru** (Teachers) - Read-only access to health records
- **Perawat** (Nurses/Medical Staff) - Full access to health management features
- **Parent** - Read-only access to their child's health records

### Permission Matrix

| Feature | Super Admin | Kepala Sekolah | Staf TU | Guru | Perawat | Parent |
|---------|------------|---------------|---------|------|----------|--------|
| View Records | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create Records | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Update Records | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Delete Records | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Immunizations | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Emergencies | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Medications | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Screenings | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Incidents | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Manage Alerts | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |

## Security and Privacy

### Data Protection

- **HIPAA-Level Security** - All health data is encrypted and access-controlled
- **Role-Based Access** - Users can only access health data appropriate to their role
- **Audit Logging** - All health record access and modifications are logged
- **Parent Privacy** - Parents can only view their own child's records
- **Data Minimization** - Only necessary health information is stored and displayed

### Privacy Controls

- Health records are linked to student records
- Medical staff (Perawat) have full access for treatment purposes
- Teachers have read-only access for safety awareness
- Parents have read-only access to their child's records
- All access attempts are logged for audit trails

## Usage Examples

### Create Health Record

```bash
curl -X POST http://localhost:9501/api/health/records \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "student-uuid",
    "blood_type": "A+",
    "chronic_conditions": "Asthma",
    "dietary_restrictions": "Peanut allergy",
    "family_medical_history": "Father has hypertension",
    "notes": "Initial health assessment"
  }'
```

### Add Immunization

```bash
curl -X POST http://localhost:9501/api/health/immunizations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "health_record_id": "health-record-uuid",
    "vaccine_name": "MMR (Measles, Mumps, Rubella)",
    "date_administered": "2026-01-15",
    "next_due_date": "2027-01-15",
    "administered_by": "School Nurse",
    "notes": "No adverse reactions"
  }'
```

### Create Medical Incident

```bash
curl -X POST http://localhost:9501/api/health/incidents \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "health_record_id": "health-record-uuid",
    "incident_date": "2026-01-18",
    "incident_type": "Fall",
    "description": "Student fell during recess on playground equipment",
    "severity": "moderate",
    "action_taken": "Nurse examined student, no injuries found",
    "status": "closed"
  }'
```

### Create Health Alert

```bash
curl -X POST http://localhost:9501/api/health/alerts \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "health_record_id": "health-record-uuid",
    "alert_type": "Allergy",
    "description": "Severe peanut allergy - anaphylaxis risk",
    "severity": "critical",
    "is_active": true,
    "notified_parent": true
  }'
```

### Get Student Health Records (Complete with all related data)

```bash
curl -X GET http://localhost:9501/api/health/records/{studentId} \
  -H "Authorization: Bearer {token}"
```

This endpoint returns the complete health record with all related immunizations, medications, emergencies, screenings, incidents, and alerts.

## Testing

Run health management tests:

```bash
vendor/bin/co-phpunit tests/Feature/HealthManagementTest.php
```

Test coverage includes:
- Health record CRUD operations
- Immunization management
- Emergency contact management
- Medication management
- Health screening management
- Medical incident reporting
- Health alert management

## Best Practices

### For School Administrators

1. **Complete Initial Assessments** - Create comprehensive health records for all new students
2. **Regular Updates** - Update records after screenings, vaccinations, or incidents
3. **Emergency Preparedness** - Ensure all emergency contacts are current and verified
4. **Alert Management** - Keep active alerts up-to-date and notify parents promptly
5. **Privacy Compliance** - Follow school privacy policies and legal requirements

### For Medical Staff (Nurses)

1. **Documentation** - Document all nurse visits and interventions
2. **Incident Reporting** - Report all medical incidents promptly and accurately
3. **Alert Creation** - Create health alerts for allergies and conditions requiring attention
4. **Parent Communication** - Communicate health concerns to parents through proper channels
5. **Follow-up** - Follow up on incidents and ensure appropriate care is provided

### For Teachers

1. **Safety Awareness** - Review student health records for classroom safety
2. **Accommodations** - Make appropriate accommodations for medical conditions
3. **Incident Reporting** - Report any incidents observed during school activities
4. **Emergency Preparedness** - Know which students have allergies or medical conditions

### For Parents

1. **Review Records** - Regularly review child's health records
2. **Update Information** - Notify school of any health changes or new conditions
3. **Emergency Contacts** - Keep emergency contact information current
4. **Stay Informed** - Monitor for health alerts and notifications from school

## Troubleshooting

### Common Issues

**Issue**: Health record not found  
**Solution**: Verify the correct student ID is being used and that a health record exists for the student

**Issue**: Unable to create immunization  
**Solution**: Ensure health_record_id is valid and refers to an existing health record

**Issue**: Permission denied  
**Solution**: Verify user has appropriate role (Super Admin, Kepala Sekolah, Staf TU, or Perawat) for the operation

**Issue**: Data not updating  
**Solution**: Verify that user has write permissions and that required fields are provided

## Integration with Other Systems

### Notification System

Health alerts can trigger notifications to parents when:
- Critical health alerts are created
- Medical incidents are reported
- Emergency contacts are updated

### Attendance System

Health incidents can impact attendance records when students are:
- Sent home due to illness
- Receiving medical treatment during school hours

### Parent Portal

Parents can view:
- Their child's health records
- Active health alerts
- Immunization history
- Medical incident reports

## Compliance and Regulations

### Data Protection

- **FERPA Compliance** - Student health records are protected educational records
- **HIPAA Guidelines** - Follow best practices for medical data security
- **Access Controls** - Role-based access ensures proper data protection
- **Audit Trails** - All access and modifications are logged

### Health Regulations

- **Immunization Tracking** - Maintains compliance with school vaccination requirements
- **Emergency Preparedness** - Ensures emergency contacts are available when needed
- **Incident Reporting** - Documents medical incidents for follow-up and analysis
- **Screening Records** - Maintains records of required health screenings

## Future Enhancements

Potential future improvements:
- Integration with electronic health records systems
- Automated vaccination compliance alerts
- Health trend analysis and reporting
- Telemedicine consultation integration
- Advanced health analytics dashboard
