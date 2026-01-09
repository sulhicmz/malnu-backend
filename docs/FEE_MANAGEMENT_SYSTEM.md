# Fee Management and Billing System

## Overview

The Fee Management and Billing System provides comprehensive financial management capabilities for school operations, including fee structures, invoice generation, payment processing, and financial reporting.

## Features

- **Fee Type Management**: Define different types of school fees (tuition, lab fees, library fees, etc.)
- **Fee Structure Management**: Configure fee amounts by grade level and academic year
- **Invoice Generation**: Automated and manual invoice generation for students
- **Payment Processing**: Track and process payments with multiple payment methods
- **Fee Waivers**: Scholarship and discount management system
- **Financial Reporting**: Comprehensive analytics and reporting capabilities
- **Late Fee Calculation**: Automatic late fee calculation based on due dates
- **Student Outstanding Balance**: Track payment status and outstanding amounts

## Database Schema

### Fee Types

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| name | string(100) | Fee type name |
| code | string(50) | Unique code for fee type |
| description | text | Fee type description |
| category | string(50) | Fee category (tuition, other) |
| is_active | boolean | Active status |
| is_mandatory | boolean | Whether fee is mandatory |

### Fee Structures

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| fee_type_id | UUID | Reference to fee type |
| grade_level | string(50) | Grade level |
| academic_year | string(10) | Academic year |
| amount | decimal(12,2) | Fee amount |
| payment_schedule | string(20) | Payment schedule (monthly, quarterly, annually) |
| due_date | date | Payment due date |
| late_fee_percentage | decimal(5,2) | Late fee percentage |
| is_active | boolean | Active status |

### Fee Invoices

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| student_id | UUID | Reference to student |
| fee_structure_id | UUID | Reference to fee structure |
| invoice_number | string(50) | Unique invoice number |
| issue_date | date | Invoice issue date |
| due_date | date | Payment due date |
| subtotal | decimal(12,2) | Subtotal amount |
| tax | decimal(12,2) | Tax amount |
| discount | decimal(12,2) | Discount amount |
| late_fee | decimal(12,2) | Late fee amount |
| total_amount | decimal(12,2) | Total amount |
| paid_amount | decimal(12,2) | Amount paid |
| balance_amount | decimal(12,2) | Balance remaining |
| status | string(20) | Invoice status (pending, partially_paid, paid) |
| notes | text | Additional notes |

### Fee Payments

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| invoice_id | UUID | Reference to invoice |
| user_id | UUID | Reference to user who made payment |
| payment_method | string(50) | Payment method |
| transaction_reference | string(100) | Transaction reference |
| amount | decimal(12,2) | Payment amount |
| status | string(20) | Payment status (pending, completed, failed) |
| payment_gateway_response | text | Payment gateway response |
| paid_at | timestamp | Payment completion time |
| notes | text | Payment notes |

### Fee Waivers

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| invoice_id | UUID | Reference to invoice (optional) |
| student_id | UUID | Reference to student |
| waiver_type | string(50) | Type of waiver |
| waiver_code | string(50) | Unique waiver code |
| discount_percentage | decimal(5,2) | Discount percentage |
| discount_amount | decimal(12,2) | Discount amount |
| reason | text | Waiver reason |
| valid_from | date | Waiver valid from |
| valid_until | date | Waiver valid until |
| status | string(20) | Waiver status (active, inactive) |
| approved_by | UUID | Reference to approving user |

### Payment Methods

| Column | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| name | string(50) | Payment method name |
| code | string(20) | Unique payment method code |
| description | text | Payment method description |
| is_active | boolean | Active status |
| requires_online_payment | boolean | Whether requires online payment |
| configuration | text | Payment method configuration |

## API Endpoints

### Fee Types

#### List Fee Types
```
GET /api/fees/fee-types
```

Query Parameters:
- `category`: Filter by category (tuition, other)
- `is_active`: Filter by active status (true/false)
- `page`: Page number
- `limit`: Items per page

Response:
```json
{
  "success": true,
  "data": {
    "data": [...],
    "current_page": 1,
    "total": 10
  },
  "message": "Fee types retrieved successfully",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

#### Create Fee Type
```
POST /api/fees/fee-types
```

Request Body:
```json
{
  "name": "Tuition Fee",
  "code": "TUITION",
  "category": "tuition",
  "description": "Annual tuition fee",
  "is_mandatory": true
}
```

#### Get Fee Type
```
GET /api/fees/fee-types/{id}
```

#### Update Fee Type
```
PUT /api/fees/fee-types/{id}
```

#### Delete Fee Type
```
DELETE /api/fees/fee-types/{id}
```

### Fee Structures

#### List Fee Structures
```
GET /api/fees/fee-structures
```

Query Parameters:
- `grade_level`: Filter by grade level
- `academic_year`: Filter by academic year
- `is_active`: Filter by active status

#### Create Fee Structure
```
POST /api/fees/fee-structures
```

Request Body:
```json
{
  "fee_type_id": "uuid",
  "grade_level": "10",
  "academic_year": "2024-2025",
  "amount": 5000.00,
  "payment_schedule": "annually",
  "due_date": "2024-09-01",
  "late_fee_percentage": 5.00
}
```

#### Get Fee Structure
```
GET /api/fees/fee-structures/{id}
```

#### Update Fee Structure
```
PUT /api/fees/fee-structures/{id}
```

#### Delete Fee Structure
```
DELETE /api/fees/fee-structures/{id}
```

### Invoices

#### List Invoices
```
GET /api/fees/invoices
```

Query Parameters:
- `student_id`: Filter by student ID
- `status`: Filter by status (pending, partially_paid, paid)
- `overdue`: Filter overdue invoices (true/false)
- `page`: Page number
- `limit`: Items per page

#### Create Invoice
```
POST /api/fees/invoices
```

Request Body:
```json
{
  "student_id": "uuid",
  "fee_structure_id": "uuid",
  "issue_date": "2024-01-01",
  "apply_waivers": true,
  "notes": "Semester 1 tuition"
}
```

#### Generate Bulk Invoices
```
POST /api/fees/invoices/generate-bulk
```

Request Body:
```json
{
  "grade": "10",
  "academic_year": "2024-2025"
}
```

#### Get Invoice
```
GET /api/fees/invoices/{id}
```

Includes payments and waivers applied to the invoice.

### Payments

#### List Payments
```
GET /api/fees/payments
```

Query Parameters:
- `invoice_id`: Filter by invoice ID
- `status`: Filter by status (pending, completed, failed)
- `page`: Page number
- `limit`: Items per page

#### Create Payment
```
POST /api/fees/payments
```

Request Body:
```json
{
  "invoice_id": "uuid",
  "user_id": "uuid",
  "payment_method": "credit_card",
  "transaction_reference": "TXN-123456",
  "amount": 1000.00,
  "status": "completed"
}
```

#### Get Payment
```
GET /api/fees/payments/{id}
```

### Waivers

#### List Waivers
```
GET /api/fees/waivers
```

Query Parameters:
- `student_id`: Filter by student ID
- `status`: Filter by status (active, inactive)
- `page`: Page number
- `limit`: Items per page

#### Create Waiver
```
POST /api/fees/waivers
```

Request Body:
```json
{
  "student_id": "uuid",
  "waiver_type": "scholarship",
  "waiver_code": "SCHOLARSHIP-001",
  "discount_percentage": 50.00,
  "reason": "Academic excellence scholarship",
  "valid_from": "2024-01-01",
  "valid_until": "2024-12-31",
  "status": "active"
}
```

### Reports

#### Financial Report
```
GET /api/fees/reports/financial
```

Query Parameters:
- `from_date`: Filter from date
- `to_date`: Filter to date
- `status`: Filter by invoice status

Response:
```json
{
  "success": true,
  "data": {
    "total_billed": 150000.00,
    "total_paid": 120000.00,
    "total_pending": 30000.00,
    "payment_rate": 80.00,
    "payment_statistics": [...],
    "invoice_count": 100,
    "paid_count": 75,
    "pending_count": 25
  },
  "message": "Financial report generated successfully"
}
```

#### Student Outstanding Balance
```
GET /api/fees/students/{studentId}/outstanding
```

Response:
```json
{
  "success": true,
  "data": {
    "total_outstanding": 5000.00,
    "overdue_count": 2,
    "invoices": [...]
  },
  "message": "Student outstanding balance retrieved successfully"
}
```

## Invoice Status Flow

```
pending -> partially_paid -> paid
         |
         v
      overdue
```

- **pending**: Invoice created, no payments made
- **partially_paid**: Partial payments received
- **paid**: Full payment received
- **overdue**: Invoice past due date (not yet paid)

## Payment Status

- **pending**: Payment initiated, not yet processed
- **completed**: Payment successfully processed
- **failed**: Payment failed

## Waiver Types

- **scholarship**: Academic or merit-based scholarship
- **discount**: General discount
- **financial_aid**: Need-based financial assistance
- **invoice_discount**: Applied to specific invoice

## Payment Methods

Common payment methods include:
- **cash**: Cash payments
- **bank_transfer**: Bank/wire transfers
- **credit_card**: Credit card payments
- **debit_card**: Debit card payments
- **online_payment**: Online payment gateways

## Security and Compliance

### Authentication
All endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer <your_jwt_token>
```

### Role-Based Access
Financial data access is controlled by user roles:
- **Admin**: Full access to all fee management features
- **Staff**: Limited access to assigned functions
- **Parent**: Read-only access to their children's fee information
- **Student**: Read-only access to their own fee information

### PCI DSS Compliance
For production deployments:
1. Never store full credit card numbers
2. Use tokenization for payment card data
3. Encrypt sensitive payment information
4. Implement proper logging and monitoring
5. Regular security audits

### Audit Logging
All financial transactions are logged with:
- User who initiated the action
- Timestamp of the action
- IP address
- Transaction details
- Changes made

## Best Practices

### Invoice Generation
- Generate invoices at the beginning of each term
- Apply relevant waivers automatically
- Send notifications to parents
- Set appropriate due dates

### Payment Processing
- Verify payment amounts before recording
- Update invoice balances immediately
- Generate receipts for completed payments
- Handle failed payments appropriately

### Late Fees
- Configure late fee percentages appropriately
- Calculate late fees daily
- Notify parents of overdue invoices
- Consider grace periods for special circumstances

### Waiver Management
- Approve waivers through proper channels
- Set appropriate validity periods
- Track waiver usage
- Regular audit of waivers applied

## Troubleshooting

### Invoice Not Generated
- Verify fee structure exists for the grade and academic year
- Check student enrollment status
- Ensure fee type is active
- Review service logs for errors

### Payment Not Reflected
- Verify payment status is "completed"
- Check invoice update logic
- Refresh invoice to recalculate balance
- Review database for transaction consistency

### Late Fee Not Applied
- Verify due date is passed
- Check late_fee_percentage in fee structure
- Run late fee calculation manually
- Review configuration settings

### Waiver Not Applied
- Verify waiver is active and valid
- Check student eligibility
- Ensure apply_waivers flag is set
- Review waiver validity period

## Integration Points

### Student Information System
- Student profiles linked via student_id
- Class/grade information for fee structure assignment
- Parent contacts for notifications

### Parent Portal
- Parents can view their children's fee statements
- Online payment processing
- Payment history and receipts
- Outstanding balance notifications

### Notification System
- Invoice generation notifications
- Payment confirmation notifications
- Overdue invoice reminders
- Waiver approval notifications

## Testing

Run the fee management test suite:
```bash
vendor/bin/phpunit tests/Feature/FeeManagement/FeeManagementTest.php
```

Test coverage includes:
- Fee type CRUD operations
- Fee structure management
- Invoice generation and management
- Payment processing and tracking
- Waiver creation and application
- Financial reporting
- Filtering and pagination

## Future Enhancements

- Advanced payment gateway integration (Stripe, PayPal, etc.)
- Automated payment reminders via email/SMS
- Refund processing workflow
- Multi-currency support
- Bulk payment import/export
- Advanced reporting with charts and graphs
- Integration with accounting software
- Mobile app support for parents
