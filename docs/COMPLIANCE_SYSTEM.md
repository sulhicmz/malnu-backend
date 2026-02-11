# Compliance and Regulatory Reporting System

## Overview

The Compliance and Regulatory Reporting System provides comprehensive compliance management for educational institutions operating in highly regulated environments. This system addresses FERPA, GDPR, CCPA, CIPA, IDEA, and general school compliance requirements.

## Architecture

### Core Components

1. **Compliance Policies** - Policy management and acknowledgment tracking
2. **Compliance Training** - Training assignment and completion tracking
3. **Compliance Audits** - Comprehensive audit trail for compliance-relevant activities
4. **Compliance Reports** - Regulatory report generation and submission tracking
5. **Data Breach Incidents** - Security incident tracking and response management
6. **Compliance Risks** - Risk assessment and mitigation tracking

### Database Schema

The system includes 8 database tables:

- `compliance_policies` - Policy definitions with version control
- `compliance_policy_acknowledgments` - User acknowledgment tracking
- `compliance_training` - Training modules and assignments
- `compliance_training_completions` - Training completion records
- `compliance_audits` - Audit trail for all compliance-relevant activities
- `compliance_reports` - Generated regulatory reports
- `data_breach_incidents` - Security incident tracking
- `compliance_risks` - Risk assessment and mitigation

### Models

All models extend the base `Model` class and use UUID primary keys:

- `App\Models\Compliance\CompliancePolicy`
- `App\Models\Compliance\CompliancePolicyAcknowledgment`
- `App\Models\Compliance\ComplianceTraining`
- `App\Models\Compliance\ComplianceTrainingCompletion`
- `App\Models\Compliance\ComplianceAudit`
- `App\Models\Compliance\ComplianceReport`
- `App\Models\Compliance\DataBreachIncident`
- `App\Models\Compliance\ComplianceRisk`

### Services

**ComplianceService** - Core business logic for compliance management:
- Policy management (create, update, acknowledge)
- Training management (create, complete, track)
- Audit logging
- Report generation
- Incident tracking
- Risk assessment

### Controllers

**ComplianceController** - RESTful API endpoints for all compliance functionality

## API Endpoints

### Authentication

All compliance endpoints require JWT authentication. Some endpoints require specific roles:
- **Super Admin** - Full access to all compliance management
- **Kepala Sekolah** - Read and write access to policies and training

### Dashboard and Statistics

```
GET /api/compliance/dashboard
GET /api/compliance/compliance-score
```

### Policy Management

```
GET /api/compliance/policies
GET /api/compliance/policies/{id}
POST /api/compliance/policies
PUT /api/compliance/policies/{id}
POST /api/compliance/policies/{id}/acknowledge
GET /api/compliance/my-pending-policies
```

**Policy Categories:**
- `FERPA` - US student data privacy
- `GDPR` - EU data protection
- `CCPA` - California privacy law
- `CIPA` - Internet safety for minors
- `IDEA` - Special education
- `General` - School policies

### Training Management

```
GET /api/compliance/training
GET /api/compliance/training/{id}
POST /api/compliance/training
PUT /api/compliance/training/{id}
POST /api/compliance/training/{id}/complete
GET /api/compliance/my-pending-training
```

**Training Types:**
- `FERPA` - FERPA compliance training
- `GDPR` - GDPR compliance training
- `Security` - Information security training
- `Privacy` - Data privacy training
- `General` - General compliance training

### Audit Trail

```
GET /api/compliance/audits
```

**Query Parameters:**
- `action_type` - Filter by action type (login, data_access, etc.)
- `entity_type` - Filter by entity type
- `severity` - Filter by severity (low, medium, high, critical)
- `days` - Recent audits (default: 30)

### Risk Management

```
GET /api/compliance/risks
GET /api/compliance/risks/{id}
POST /api/compliance/risks
PUT /api/compliance/risks/{id}
```

**Risk Parameters:**
- `likelihood` - rare, unlikely, possible, likely, almost_certain
- `impact` - negligible, minor, moderate, major, catastrophic
- `risk_score` - Automatically calculated (likelihood × impact, 1-25)
- `mitigation_priority` - low, medium, high, critical
- `mitigation_status` - not_started, in_progress, completed, deferred

### Incident Management

```
GET /api/compliance/incidents
GET /api/compliance/incidents/{id}
POST /api/compliance/incidents
PUT /api/compliance/incidents/{id}
```

**Incident Types:**
- `unauthorized_access` - Unauthorized data access
- `data_exposure` - Data exposure incident
- `lost_device` - Lost or stolen device
- `phishing` - Phishing attack
- `malware` - Malware infection
- `other` - Other incidents

**Incident Status:**
- `open` - New incident
- `investigating` - Under investigation
- `mitigating` - Mitigation in progress
- `resolved` - Incident resolved
- `closed` - Incident closed

### Report Management

```
GET /api/compliance/reports
GET /api/compliance/reports/{id}
POST /api/compliance/reports
POST /api/compliance/reports/{id}/submit
```

**Report Types:**
- `FERPA_access` - FERPA access reports
- `GDPR_subject_rights` - GDPR data subject requests
- `training_completion` - Training completion reports
- `audit_summary` - Audit trail summaries
- `incident_summary` - Security incident reports
- `risk_assessment` - Risk assessment reports

## Usage Examples

### Create a Compliance Policy

```bash
curl -X POST http://localhost:9501/api/compliance/policies \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Student Data Privacy Policy",
    "description": "Policy for handling student educational records",
    "content": "Full policy content here...",
    "category": "FERPA",
    "effective_date": "2026-01-01",
    "expiry_date": null
  }'
```

### Acknowledge Policy

```bash
curl -X POST http://localhost:9501/api/compliance/policies/{policy_id}/acknowledge \
  -H "Authorization: Bearer {jwt_token}"
```

### Create Training

```bash
curl -X POST http://localhost:9501/api/compliance/training \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "FERPA Compliance Training",
    "description": "Annual FERPA compliance training",
    "content": "Training content here...",
    "training_type": "FERPA",
    "duration_minutes": 45,
    "category": "Privacy",
    "required_for_all": true,
    "valid_from": "2026-01-01"
  }'
```

### Create Risk Assessment

```bash
curl -X POST http://localhost:9501/api/compliance/risks \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "risk_title": "Unauthorized access to student records",
    "description": "Risk of unauthorized access to student records via weak permissions",
    "risk_category": "access_control",
    "likelihood": "possible",
    "impact": "major",
    "affected_systems": ["student_management", "grade_management"],
    "applicable_regulations": ["FERPA", "GDPR"],
    "mitigation_plan": "Implement role-based access control and regular permission reviews",
    "mitigation_priority": "high",
    "target_mitigation_date": "2026-02-01"
  }'
```

### Report Data Breach Incident

```bash
curl -X POST http://localhost:9501/api/compliance/incidents \
  -H "Authorization: Bearer {jwt_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "incident_type": "unauthorized_access",
    "severity": "high",
    "title": "Unauthorized access to student database",
    "description": "Unknown user accessed student records on 2026-01-15",
    "affected_records": 150,
    "data_types_affected": ["student_records", "personal_info", "grades"]
  }'
```

## Compliance Scoring

The system automatically calculates compliance scores based on:

1. **Policy Compliance** - Percentage of policies acknowledged
2. **Training Compliance** - Percentage of required training completed
3. **Risk Assessment** - Deduction for open and high-priority risks

**Overall Score Formula:**
```
Overall Score = (Policy Compliance × 0.4) + (Training Compliance × 0.4) - Risk Deduction

Risk Deduction = min(25, (Critical Risks × 5) + (Open Risks × 2))
```

Score range: 0-100

## Regulatory Framework Mappings

### FERPA (Family Educational Rights and Privacy Act)

**Requirements Met:**
- ✅ Student data access logging (audit trail)
- ✅ Data subject rights tracking
- ✅ Policy acknowledgment system
- ✅ Training on FERPA compliance
- ✅ Breach notification tracking

### GDPR (General Data Protection Regulation)

**Requirements Met:**
- ✅ Data processing consent management (via policies)
- ✅ Data subject request tracking
- ✅ Data breach notification (72-hour requirement)
- ✅ Privacy impact assessment framework
- ✅ Training on GDPR compliance

### CCPA (California Consumer Privacy Act)

**Requirements Met:**
- ✅ Data access requests tracking
- ✅ Data deletion requests tracking
- ✅ Opt-out mechanisms (via policies)
- ✅ Data breach notifications

### CIPA (Children's Internet Protection Act)

**Requirements Met:**
- ✅ Internet safety policy tracking
- ✅ Minor protection policies
- ✅ Training on internet safety

### IDEA (Individuals with Disabilities Education Act)

**Requirements Met:**
- ✅ Special education data privacy (via FERPA)
- ✅ IEP confidentiality tracking (via policies)
- ✅ Training on IDEA compliance

## Compliance Workflows

### 1. Policy Management Workflow

```
1. Admin creates policy with category, effective date
2. System automatically assigns version 1
3. Users are notified of new policy
4. Users acknowledge policy via API
5. System tracks acknowledgment (who, when, IP, device)
6. When policy expires or is superseded, system marks it
7. Superseded policies remain for historical records
```

### 2. Training Management Workflow

```
1. Admin creates training with type, duration, requirements
2. System marks users who must complete based on roles/requirements
3. Users access training and complete
4. System records completion (score, pass/fail, IP, device)
5. Dashboard shows pending training for each user
6. Managers can track completion rates
```

### 3. Risk Assessment Workflow

```
1. Staff member identifies compliance risk
2. Risk assigned likelihood and impact
3. System calculates risk score (1-25)
4. Risk assigned mitigation priority and target date
5. Risk assigned to owner for mitigation
6. System tracks mitigation progress
7. Dashboard shows open and overdue risks
```

### 4. Incident Response Workflow

```
1. Data breach or incident discovered
2. Incident reported via API with severity and details
3. System automatically:
   - Assigns incident status: open
   - Records timestamp
   - Tracks affected records
   - Flags if regulatory report required
4. Incident assigned to investigator
5. Investigation and mitigation documented
6. If regulatory report required, submission tracked
7. Incident closed when resolved
```

### 5. Audit Logging Workflow

```
Compliance-relevant activities automatically logged:
- Login/logout events
- Student data access
- Grade modifications
- Personal data exports
- System configuration changes

Audit includes:
- User ID
- Action type
- Entity type and ID
- Old and new values (for modifications)
- IP address and user agent
- Compliance tags
- Severity level
```

## Integration Points

### Student Information System

The compliance system integrates with student data:
- Audit logging for student record access
- FERPA compliance for student data
- Training requirements for student-facing staff

### User Management System

Integration with user accounts:
- Policy acknowledgment linked to users
- Training completion tracked per user
- Audit trail includes user context

### Notification System

Integration points for future enhancement:
- Notify users of new policies requiring acknowledgment
- Remind users of pending training
- Alert administrators of new incidents or risks
- Notify of upcoming policy expiry

## Security Considerations

### Data Privacy

- All compliance data is sensitive
- Role-based access control required
- Audit trail for all compliance system access
- IP and device tracking for acknowledgments

### Audit Trail Protection

- Audit logs cannot be deleted
- Audit logs are immutable
- Tamper-evident storage (future enhancement)

### Incident Data Protection

- Incident reports contain sensitive information
- Restricted access to authorized personnel only
- Regulatory submission details protected

## Performance Considerations

### Database Optimization

- Indexes on frequently queried fields (status, dates, categories)
- Pagination on all list endpoints (default: 15-50 items)
- Query optimization for dashboard statistics

### Audit Log Growth

- Audit logs can grow large over time
- Implement log retention policy (e.g., 1-3 years)
- Consider archiving old audit logs

## Best Practices

### 1. Policy Management

- **Version Control**: Always increment policy version when making changes
- **Effective Dates**: Ensure effective dates give users time to review policies
- **Supersession**: Mark old policies as superseded, don't delete
- **Categories**: Use appropriate categories for regulatory compliance

### 2. Training Management

- **Targeted Training**: Assign training to relevant roles only when possible
- **Tracking**: Track both completion and pass/fail status
- **Reminders**: Implement notification system for training reminders (future)
- **Records**: Keep training records for compliance audits

### 3. Risk Management

- **Regular Assessments**: Conduct regular risk assessments
- **Prioritization**: Use risk scores to prioritize mitigation
- **Documentation**: Document mitigation plans clearly
- **Follow-up**: Track mitigation completion and target dates

### 4. Incident Response

- **Rapid Reporting**: Report incidents promptly for regulatory compliance
- **Severity Assessment**: Use accurate severity for proper prioritization
- **Documentation**: Document root cause and mitigation actions
- **Regulatory Reporting**: Track required regulatory submissions

### 5. Audit Trail

- **Comprehensive Logging**: Log all compliance-relevant activities
- **Clear Descriptions**: Use clear, descriptive action types
- **Proper Tagging**: Apply appropriate compliance tags
- **Regular Review**: Review audit logs for compliance gaps

## Troubleshooting

### Issues and Solutions

**Issue**: Dashboard shows incorrect compliance score
**Solution**: Check that policies are active and users have acknowledged training

**Issue**: Users can't see policies
**Solution**: Verify policies have status "active" and effective_date <= today

**Issue**: Training not marked as completed
**Solution**: Verify training is "active" and valid_from <= today

**Issue**: Audit logs not being created
**Solution**: Check that audit logging is properly implemented in middleware/controllers

**Issue**: Risk scores incorrect
**Solution**: Verify likelihood and impact are valid values; score auto-calculates on save

## Configuration

### Environment Variables

No special configuration required beyond standard JWT authentication.

### Required Permissions

**Compliance Dashboard Access**:
- All authenticated users (read-only view)

**Compliance Management Access**:
- Super Admin - Full access
- Kepala Sekolah - Policies, training, reports
- Staf TU - Policies, training
- Guru - View only

**Incident and Risk Management**:
- Super Admin - Full access
- Kepala Sekolah - Full access
- Staf TU - Create incidents and risks

## Migration Instructions

### Database Migration

After merging this PR, run:

```bash
php artisan migrate
```

This will create 8 compliance tables.

### Seeding (Optional)

Consider seeding initial compliance policies and training:

```bash
php artisan db:seed --class=ComplianceSeeder
```

## Future Enhancements

### Advanced Features (Deferred to Follow-up Issues)

1. **Automated Compliance Rule Engine**
   - Rule-based compliance checking
   - Automated violation detection
   - Real-time compliance monitoring

2. **Advanced GDPR Consent Management**
   - Detailed consent tracking
   - Consent versioning
   - Granular consent preferences

3. **Accreditation Management**
   - Standards tracking
   - Evidence collection
   - Site visit preparation
   - Self-study tools

4. **Advanced Privacy Impact Assessments (DPIA)**
   - Automated DPIA workflows
   - Risk assessment integration
   - Documentation templates

5. **External Regulatory System Integration**
   - Direct API integration with regulatory bodies
   - Automated report submission
   - Regulatory change tracking

6. **AI-Powered Compliance Monitoring**
   - Anomaly detection in audit logs
   - Predictive risk assessment
   - Automated compliance gap identification

7. **Advanced Report Templates**
   - Jurisdiction-specific templates
   - Customizable report formats
   - Batch report generation

8. **Compliance Notifications**
   - Email/SMS alerts for new policies
   - Training reminders
   - Incident alerts
   - Risk escalation notifications

## Compliance Resources

### Regulatory References

- [FERPA Guidelines](https://www2.ed.gov/policy/gen/guid/fpco/ferpa/index.html)
- [EU GDPR Official Website](https://gdpr.eu/)
- [California Consumer Privacy Act](https://oag.ca.gov/privacy/ccpa)
- [CIPA Information](https://www.fcc.gov/cipa)
- [IDEA Information](https://sites.ed.gov/idea/)

### Compliance Tools

- Use compliance dashboard regularly
- Review audit logs monthly
- Conduct quarterly risk assessments
- Track training completion rates
- Monitor incident response times

## Support

For questions or issues with the compliance system:
1. Review this documentation
2. Check API error messages
3. Review audit logs for issues
4. Create issue on GitHub repository

---

**Version**: 1.0.0  
**Last Updated**: 2026-01-15  
**Maintained By**: Development Team
