# School Administration and Governance Module

This document provides comprehensive information about the School Administration and Governance module for the Malnu Kananga School Management System.

## Table of Contents

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Models](#models)
- [API Endpoints](#api-endpoints)
- [Security and Privacy](#security-and-privacy-considerations)
- [Integration Guide](#integration-guide)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

The School Administration and Governance Module provides comprehensive tools for managing institutional operations, compliance, regulatory reporting, and administrative workflows that are essential for school management.

### Key Features

1. **Compliance & Accreditation Management**
   - Regulatory requirement tracking and monitoring
   - Accreditation preparation and documentation
   - Audit trail and reporting capabilities
   - Policy and procedure management system

2. **Staff Administration**
   - Teacher and staff performance evaluation systems
   - Professional development tracking and certification
   - Recruitment and onboarding workflows
   - Staff scheduling and workload management

3. **Resource Management**
   - Budget allocation and expense tracking
   - Facility and equipment management
   - Inventory tracking for school supplies
   - Vendor and contract management

4. **Institutional Analytics**
   - School performance metrics and KPI tracking
   - Enrollment trends and demographic analysis
   - Financial reporting and budget analysis
   - Stakeholder satisfaction surveys

## System Architecture

### Component Structure

```
app/
├── Models/
│   ├── ComplianceRequirement.php
│   ├── AccreditationStandard.php
│   ├── PolicyAndProcedure.php
│   ├── StaffEvaluation.php
│   ├── ProfessionalDevelopment.php
│   ├── BudgetAllocation.php
│   ├── Expense.php
│   ├── InventoryItem.php
│   ├── VendorContract.php
│   └── InstitutionalMetric.php
├── Services/
│   └── SchoolAdministrationService.php
└── Http/Controllers/Api/
    └── SchoolAdministrationController.php

database/
└── migrations/
    └── 2026_01_10_000000_create_school_administration_governance_tables.php

tests/
└── Feature/
    └── SchoolAdministrationTest.php
```

## Database Schema

### Compliance Requirements (compliance_requirements)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| name | string(200) | Compliance requirement name |
| description | text | Detailed description |
| category | string(100) | Compliance category |
| regulatory_body | string(100) | Regulatory authority |
| status | string(50) | pending, in_progress, completed |
| due_date | date | Deadline for compliance |
| completion_date | date | When compliance was achieved |
| responsible_staff_id | UUID | Staff responsible |
| priority | string(20) | low, medium, high |
| notes | text | Additional notes |
| document_path | string(500) | Path to supporting documents |

### Accreditation Standards (accreditation_standards)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| name | string(200) | Standard name |
| accreditation_body | string(100) | Accreditation authority |
| standard_code | string(50) | Standard identifier |
| description | text | Standard description |
| status | string(50) | in_progress, completed |
| assessment_date | date | Assessment date |
| expiry_date | date | Standard expiration |
| coordinator_id | UUID | Staff coordinator |
| evidence_notes | text | Assessment evidence |
| report_path | string(500) | Assessment report path |

### Policies and Procedures (policies_and_procedures)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| title | string(200) | Policy title |
| category | string(100) | Policy category |
| policy_number | string(50) | Policy identifier |
| content | text | Policy content |
| version | integer | Policy version |
| effective_date | date | Effective date |
| review_date | date | Review date |
| status | string(50) | active, pending_review |
| author_id | UUID | Policy author (User) |
| approver_id | UUID | Policy approver (User) |
| change_summary | text | Version changes summary |
| document_path | string(500) | Document path |

### Staff Evaluations (staff_evaluations)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| staff_id | UUID | Staff being evaluated |
| evaluator_id | UUID | Evaluator (Staff) |
| evaluation_date | date | Evaluation date |
| evaluation_type | string(50) | Annual, probation, etc. |
| academic_year | string(9) | Academic year |
| overall_score | decimal(5,2) | Overall performance score |
| rating | string(20) | Performance rating |
| strengths | text | Staff strengths |
| areas_for_improvement | text | Improvement areas |
| goals | text | Development goals |
| status | string(50) | draft, submitted, reviewed |
| reviewer_id | UUID | Reviewer (Staff) |
| review_date | date | Review date |
| feedback | text | Review feedback |

### Professional Development (professional_development)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| staff_id | UUID | Staff member |
| title | string(200) | Training title |
| training_type | string(50) | Course, workshop, conference |
| provider | string(100) | Training provider |
| start_date | date | Start date |
| end_date | date | End date |
| duration_hours | integer | Training duration in hours |
| location | string(200) | Training location |
| description | text | Training description |
| status | string(50) | planned, in_progress, completed, cancelled |
| certificate_path | string(500) | Certificate document path |
| cost | decimal(10,2) | Training cost |
| internal | boolean | Internal vs external training |

### Budget Allocations (budget_allocations)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| budget_code | string(50) | Budget code (unique) |
| name | string(200) | Budget name |
| category | string(100) | Budget category |
| department | string(100) | Department responsible |
| academic_year | string(9) | Academic year (2023-2024) |
| allocated_amount | decimal(15,2) | Amount allocated |
| spent_amount | decimal(15,2) | Amount spent |
| remaining_amount | decimal(15,2) | Remaining balance |
| start_date | date | Budget period start |
| end_date | date | Budget period end |
| status | string(50) | active, closed |
| manager_id | UUID | Budget manager (Staff) |
| notes | text | Additional notes |

### Expenses (expenses)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| budget_allocation_id | UUID | Associated budget |
| description | string(200) | Expense description |
| amount | decimal(15,2) | Expense amount |
| expense_date | date | Date incurred |
| category | string(100) | Expense category |
| payment_method | string(50) | Credit card, check, etc. |
| vendor | string(100) | Vendor name |
| requester_id | UUID | Requesting staff |
| approver_id | UUID | Approving staff |
| status | string(50) | pending, approved, rejected |
| receipt_path | string(500) | Receipt document path |
| justification | text | Justification for expense |

### Inventory Items (inventory_items)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| name | string(200) | Item name |
| code | string(50) | Item code (unique) |
| category | string(100) | Inventory category |
| type | string(50) | Equipment, supply, material |
| quantity | integer | Available quantity |
| minimum_quantity | integer | Minimum stock level |
| unit | string(20) | Unit of measure |
| unit_cost | decimal(10,2) | Cost per unit |
| location | string(200) | Storage location |
| condition | string(50) | Item condition |
| purchase_date | date | Purchase date |
| last_maintenance | date | Last maintenance date |
| responsible_staff_id | UUID | Responsible staff |
| status | string(50) | available, in_use, maintenance, retired |
| specifications | text | Item specifications |

### Vendor Contracts (vendor_contracts)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| vendor_name | string(200) | Vendor company name |
| contact_person | string(100) | Contact name |
| email | string(150) | Vendor email |
| phone | string(20) | Contact phone |
| address | text | Vendor address |
| service_type | string(100) | Service type |
| contract_number | string(50) | Contract number (unique) |
| start_date | date | Contract start |
| end_date | date | Contract end |
| contract_value | decimal(15,2) | Contract value |
| status | string(50) | active, expired, terminated |
| manager_id | UUID | Contract manager |
| terms_and_conditions | text | Contract terms |
| document_path | string(500) | Contract document path |

### Institutional Metrics (institutional_metrics)

| Field | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| metric_name | string(200) | Metric name |
| metric_type | string(100) | Performance, financial, enrollment |
| category | string(100) | Metric category |
| value | decimal(15,2) | Metric value |
| unit | string(50) | Unit of measure |
| metric_date | date | Metric date |
| academic_year | string(9) | Academic year |
| comparison_period | string(50) | Previous period |
| previous_value | decimal(15,2) | Previous period value |
| target_value | decimal(15,2) | Target value |
| trend | string(20) | up, down, stable |
| notes | text | Notes |
| data_source_staff_id | UUID | Data source (Staff) |

## Models

### ComplianceRequirement

**Relationships:**
- `responsibleStaff()` - BelongsTo Staff

**Scopes:**
- `pending()` - Filter pending requirements
- `inProgress()` - Filter in progress
- `completed()` - Filter completed
- `overdue()` - Filter overdue requirements
- `highPriority()` - Filter high priority

**Methods:**
- `isOverdue()` - Check if requirement is overdue

### AccreditationStandard

**Relationships:**
- `coordinator()` - BelongsTo Staff

**Scopes:**
- `inProgress()` - Filter in progress
- `completed()` - Filter completed
- `expiringSoon()` - Filter expiring within 90 days
- `expired()` - Filter expired standards

### PolicyAndProcedure

**Relationships:**
- `author()` - BelongsTo User
- `approver()` - BelongsTo User

**Scopes:**
- `active()` - Filter active policies
- `pendingReview()` - Filter pending review

### StaffEvaluation

**Relationships:**
- `staff()` - BelongsTo Staff
- `evaluator()` - BelongsTo Staff
- `reviewer()` - BelongsTo Staff

### ProfessionalDevelopment

**Relationships:**
- `staff()` - BelongsTo Staff

**Scopes:**
- `upcoming()` - Filter upcoming training
- `completed()` - Filter completed training
- `internal()` - Filter internal training
- `external()` - Filter external training

### BudgetAllocation

**Relationships:**
- `manager()` - BelongsTo Staff
- `expenses()` - HasMany Expense

**Scopes:**
- `active()` - Filter active budgets
- `expiringSoon()` - Filter budgets expiring in 30 days

### Expense

**Relationships:**
- `budgetAllocation()` - BelongsTo BudgetAllocation
- `requester()` - BelongsTo Staff
- `approver()` - BelongsTo Staff

**Scopes:**
- `pending()` - Filter pending expenses
- `approved()` - Filter approved expenses
- `rejected()` - Filter rejected expenses

### InventoryItem

**Relationships:**
- `responsibleStaff()` - BelongsTo Staff

**Scopes:**
- `available()` - Filter available items
- `lowStock()` - Filter items below minimum
- `needsMaintenance()` - Filter items needing maintenance

### VendorContract

**Relationships:**
- `manager()` - BelongsTo Staff

**Scopes:**
- `active()` - Filter active contracts
- `expiringSoon()` - Filter contracts expiring in 30 days
- `expired()` - Filter expired contracts

### InstitutionalMetric

**Relationships:**
- `dataSourceStaff()` - BelongsTo Staff

**Scopes:**
- `byCategory()` - Filter by category
- `byType()` - Filter by metric type
- `byAcademicYear()` - Filter by academic year

**Methods:**
- `getTrend()` - Calculate trend (up/down/stable)

## API Endpoints

All endpoints require JWT authentication and are prefixed with `/api/administration`.

### Index Route

**GET** `/api/administration`

Query Parameters:
- `type` - Resource type (compliance, accreditation, policies, evaluations, professional_development, budget, expenses, inventory, vendors, metrics, all)
- `status` - Filter by status
- `category` - Filter by category
- `academic_year` - Filter by academic year

Returns summary data based on type.

### Compliance Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/compliance` | List compliance requirements |
| POST | `/api/administration/compliance` | Create compliance requirement |
| PUT | `/api/administration/compliance/{id}` | Update compliance requirement |
| DELETE | `/api/administration/compliance/{id}` | Delete compliance requirement |

### Accreditation Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/accreditation` | List accreditation standards |
| POST | `/api/administration/accreditation` | Create accreditation standard |

### Policy Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/policies` | List policies |
| POST | `/api/administration/policies` | Create policy |
| PUT | `/api/administration/policies/{id}` | Update policy |

### Staff Evaluation Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/evaluations` | List staff evaluations |
| POST | `/api/administration/evaluations` | Create evaluation |
| PUT | `/api/administration/evaluations/{id}` | Update evaluation |

### Professional Development Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/professional-development` | List professional development |
| POST | `/api/administration/professional-development` | Create professional development |
| PUT | `/api/administration/professional-development/{id}` | Update professional development |

### Budget Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/budget` | List budget allocations |
| POST | `/api/administration/budget` | Create budget allocation |
| PUT | `/api/administration/budget/{id}` | Update budget allocation |

### Expense Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/expenses` | List expenses |
| POST | `/api/administration/expenses` | Create expense |
| POST | `/api/administration/expenses/{id}/approve` | Approve expense |
| POST | `/api/administration/expenses/{id}/reject` | Reject expense |

### Inventory Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/inventory` | List inventory items |
| POST | `/api/administration/inventory` | Create inventory item |
| PUT | `/api/administration/inventory/{id}` | Update inventory item |

### Vendor Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/vendors` | List vendor contracts |
| POST | `/api/administration/vendors` | Create vendor contract |
| PUT | `/api/administration/vendors/{id}` | Update vendor contract |

### Metrics Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/metrics` | List institutional metrics |
| POST | `/api/administration/metrics` | Create metric |
| PUT | `/api/administration/metrics/{id}` | Update metric |

### Reports Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/administration/reports` | Generate reports |

Query Parameters:
- `type` - Report type (all, compliance, budget, performance)
- `academic_year` - Academic year for report

Returns compliance, budget, and performance reports.

## Security and Privacy Considerations

### Access Control

All administration endpoints require:
1. **JWT Authentication** - Valid JWT token required
2. **Role-Based Access** - Recommended roles:
   - Super Admin - Full access
   - Kepala Sekolah (Principal) - View and create
   - Staf TU - Limited access

### Data Privacy

1. **Staff Performance Data**
   - Restricted to authorized evaluators and administrators
   - Individual evaluations accessible only to evaluator, reviewer, and evaluated staff

2. **Budget and Financial Data**
   - Restricted to finance team and administrators
   - Audit logging for all financial transactions

3. **Compliance Documents**
   - Secure document storage with controlled access
   - Version control for policy changes

### Audit Logging

All administrative actions are logged:
- Who made the change (user, IP, timestamp)
- What was changed (old value, new value)
- Why change was made (justification)

### GDPR/FERPA Compliance

1. **Data Minimization** - Collect only necessary data
2. **Right to Access** - Staff can view their own evaluations
3. **Right to Rectification** - Request corrections to personal data
4. **Right to Erasure** - Data retention policies
5. **Data Portability** - Export personal data on request

## Integration Guide

### Student Information System (Issue #229)

**Staff Linking:**
- Staff evaluations reference staff via `staff_id`
- Professional development linked to staff
- Compliance responsible staff references `staff_id`

### Notification System (Issue #257)

**Alert Types:**
- Compliance due date reminders
- Budget approval requests
- Expense approval notifications
- Vendor contract expiration alerts
- Professional development reminders

### Fee Management (Issue #200)

**Budget Integration:**
- Professional development costs can link to fee allocations
- Expense tracking provides spending data
- Budget variance analysis for fee adjustments

### Inventory Management (Issue #203)

**Reconciliation:**
- Budget allocations for equipment purchases
- Expense tracking for inventory acquisitions
- Maintenance costs linked to inventory items

## Best Practices

### Compliance Management

1. **Regular Reviews** - Review compliance requirements monthly
2. **Document Trail** - Maintain evidence of compliance activities
3. **Escalation Process** - Define escalation for overdue items
4. **Continuous Monitoring** - Set up automated monitoring

### Budget Management

1. **Zero-Based Budgeting** - Start from zero each period
2. **Regular Reconciliation** - Monthly budget vs. actual analysis
3. **Approval Workflows** - Define approval thresholds for expenses
4. **Forecasting** - Use historical data for future budgeting

### Staff Evaluation

1. **Clear Criteria** - Define evaluation rubrics
2. **Multiple Perspectives** - 360-degree feedback where appropriate
3. **Development Focus** - Link evaluations to professional development
4. **Regular Schedule** - Annual reviews with quarterly check-ins

### Vendor Management

1. **Competitive Bidding** - Document bid evaluation process
2. **Performance Tracking** - Track vendor performance metrics
3. **Contract Renewals** - Start renewal process 60 days before expiration
4. **Centralized Repository** - Maintain approved vendor list

## Troubleshooting

### Common Issues

#### Compliance Requirements Not Tracking

**Issue:** Compliance requirements not updating status

**Solution:**
1. Check `responsible_staff_id` is assigned
2. Verify staff has appropriate permissions
3. Review status workflow configuration

#### Budget Calculations Incorrect

**Issue:** Remaining amount not calculating correctly

**Solution:**
1. Verify expense approval workflow is updating budget
2. Check for duplicate expense entries
3. Review `spent_amount` calculation in service

#### Evaluation Data Not Saving

**Issue:** Staff evaluation form not submitting

**Solution:**
1. Check JWT token is valid
2. Verify evaluator has permissions to evaluate target staff
3. Review required fields validation

#### Vendor Contracts Not Expiring

**Issue:** Not receiving contract expiration alerts

**Solution:**
1. Verify notification integration is configured
2. Check `end_date` is set correctly
3. Review notification templates

### Error Messages

| Error Code | Description | Resolution |
|-------------|---------------|------------|
| NOT_FOUND | Resource not found | Verify ID is correct |
| UNAUTHORIZED | Invalid JWT token | Re-authenticate |
| FORBIDDEN | Insufficient permissions | Check user role |
| VALIDATION_ERROR | Invalid input | Correct request data |
| SERVER_ERROR | Internal error | Contact administrator |

### Debug Mode

Enable detailed error logging in development:

```bash
# In .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Performance Optimization

1. **Database Indexing** - Add indexes for frequently queried fields
2. **Caching** - Cache reports and metrics data
3. **Pagination** - Use pagination for large lists
4. **Lazy Loading** - Load relationships only when needed

### Testing

Run the administration module tests:

```bash
vendor/bin/phpunit tests/Feature/SchoolAdministrationTest.php
```

### Support

For issues or questions:
1. Check this documentation
2. Review API logs
3. Contact system administrator
4. Review GitHub issues and pull requests
