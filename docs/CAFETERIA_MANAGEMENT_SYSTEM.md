# Cafeteria and Meal Management System

## Overview

The Cafeteria and Meal Management System provides comprehensive tools for managing school cafeteria operations, including meal planning, student dietary preferences, inventory management, payment processing, and vendor management.

## Architecture

### Database Schema

The system uses 5 main database tables:

1. **meal_plans** - Weekly/monthly meal schedules and menus
2. **student_meal_preferences** - Student preferences, dietary restrictions, and allergy information
3. **cafeteria_inventories** - Food supply tracking and inventory management
4. **meal_payments** - Payment processing and subsidy tracking
5. **vendors** - Supplier and vendor management

### Models

- **MealPlan** - Meal schedule and menu management
- **StudentMealPreference** - Student dietary tracking
- **CafeteriaInventory** - Inventory and stock management
- **MealPayment** - Payment and subsidy processing
- **Vendor** - Vendor relationship management

### Service Layer

**CafeteriaManagementService** provides core business logic:
- Meal plan CRUD operations
- Student preference management
- Inventory tracking and low stock alerts
- Payment processing and subsidy management
- Vendor management

### API Endpoints

All cafeteria endpoints are under `/api/cafeteria` prefix and require JWT authentication.

#### Meal Plan Endpoints
- `GET /api/cafeteria/meal-plans` - List all meal plans
- `POST /api/cafeteria/meal-plans` - Create new meal plan
- `GET /api/cafeteria/meal-plans/{id}` - Get specific meal plan
- `PUT /api/cafeteria/meal-plans/{id}` - Update meal plan
- `DELETE /api/cafeteria/meal-plans/{id}` - Delete meal plan

#### Student Preference Endpoints
- `GET /api/cafeteria/student-preferences` - List all student preferences
- `POST /api/cafeteria/student-preferences` - Create student preference
- `GET /api/cafeteria/student-preferences/{id}` - Get specific preference
- `PUT /api/cafeteria/student-preferences/{id}` - Update preference
- `DELETE /api/cafeteria/student-preferences/{id}` - Delete preference

Query Parameters:
- `student_id` (optional) - Filter preferences by student

#### Inventory Endpoints
- `GET /api/cafeteria/inventory` - List all inventory items
- `POST /api/cafeteria/inventory` - Create inventory item
- `GET /api/cafeteria/inventory/{id}` - Get specific item
- `PUT /api/cafeteria/inventory/{id}` - Update inventory item
- `DELETE /api/cafeteria/inventory/{id}` - Delete inventory item
- `GET /api/cafeteria/inventory/low-stock` - Get low stock items

Query Parameters:
- `threshold` (optional, default: 10) - Minimum stock level threshold

#### Meal Payment Endpoints
- `GET /api/cafeteria/payments` - List all payments
- `POST /api/cafeteria/payments` - Create payment
- `GET /api/cafeteria/payments/{id}` - Get specific payment
- `PUT /api/cafeteria/payments/{id}` - Update payment
- `DELETE /api/cafeteria/payments/{id}` - Delete payment

Query Parameters:
- `student_id` (optional) - Filter payments by student

#### Vendor Endpoints
- `GET /api/cafeteria/vendors` - List all vendors
- `POST /api/cafeteria/vendors` - Create new vendor
- `GET /api/cafeteria/vendors/{id}` - Get specific vendor
- `PUT /api/cafeteria/vendors/{id}` - Update vendor
- `DELETE /api/cafeteria/vendors/{id}` - Delete vendor

## API Request/Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "Weekly Menu - Week 1",
        ...
    },
    "message": "Operation successful",
    "timestamp": "2026-01-10T12:00:00+00:00"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "message": "Error message",
        "code": "ERROR_CODE",
        "details": {
            "field_name": "Validation error message"
        }
    },
    "timestamp": "2026-01-10T12:00:00+00:00"
}
```

## Usage Examples

### Create Meal Plan
```bash
POST /api/cafeteria/meal-plans
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "name": "Weekly Menu - Week 1",
    "description": "Standard meal plan for first week",
    "start_date": "2026-01-13",
    "end_date": "2026-01-19",
    "status": "active"
}
```

### Create Student Preference
```bash
POST /api/cafeteria/student-preferences
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "student_id": "student-uuid",
    "requires_special_diet": true,
    "dietary_restrictions": "Vegetarian",
    "allergies": "Peanuts, Tree nuts",
    "subsidy_eligible": true,
    "subsidy_amount": 5.00
}
```

### Create Inventory Item
```bash
POST /api/cafeteria/inventory
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "item_name": "Rice",
    "category": "Grains",
    "quantity": 50,
    "unit": "kg",
    "unit_cost": 2.50,
    "vendor_id": "vendor-uuid",
    "minimum_stock_level": 10,
    "expiry_date": "2026-06-01"
}
```

### Process Meal Payment
```bash
POST /api/cafeteria/payments
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "student_id": "student-uuid",
    "amount": 25.00,
    "subsidy_amount": 5.00,
    "amount_paid": 20.00,
    "payment_method": "cash",
    "payment_date": "2026-01-10",
    "status": "paid"
}
```

### Create Vendor
```bash
POST /api/cafeteria/vendors
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
    "name": "Food Supplier Inc.",
    "contact_person": "Jane Smith",
    "phone": "9876543210",
    "email": "contact@foodsupplier.com",
    "address": "456 Food St, City",
    "status": "active"
}
```

## Integration Guide

### Student Management System Integration
The cafeteria system integrates with the existing Student management system:

- **StudentMealPreference** model links to **Student** model via `student_id` foreign key
- Student dietary preferences can be retrieved by student ID
- Meal payments are linked to students for billing

### Payment System Integration
Meal payments can integrate with the existing monetization system:

- Payment records can be synced with the broader billing system
- Subsidy amounts can be tracked separately from actual payments
- Payment status can be used for financial reporting

## Security Considerations

- All endpoints require JWT authentication
- Student dietary and allergy information is sensitive PII
- Payment data must be handled with appropriate security measures
- Vendor contact information should be protected
- Access should be role-based (students, parents, cafeteria staff, administrators)

## Best Practices

### Meal Planning
- Create meal plans on a weekly or monthly basis
- Consider nutritional balance across the week
- Account for dietary restrictions and allergies when planning
- Update meal plans regularly to reflect seasonal availability

### Inventory Management
- Set appropriate minimum stock levels for each item
- Monitor expiry dates regularly
- Use low stock alerts to prevent shortages
- Track vendor performance for quality assessment

### Student Preferences
- Allow students to update their own preferences where appropriate
- Communicate dietary restrictions clearly to cafeteria staff
- Maintain allergy information for safety
- Respect parent preferences for younger students

### Payment Processing
- Record both full amount and subsidy amount separately
- Track payment status accurately (pending, paid, failed)
- Generate transaction references for audit trails
- Handle different payment methods appropriately

## Testing

Run the cafeteria management test suite:

```bash
vendor/bin/phpunit tests/Feature/CafeteriaManagementTest.php
```

Tests cover:
- Meal plan CRUD operations
- Student preference management
- Inventory operations
- Meal payment processing
- Vendor management
- Validation error handling

## Troubleshooting

### Common Issues

**1. Low stock alerts not appearing**
- Ensure minimum_stock_level is set correctly for each item
- Check the threshold parameter in low-stock endpoint query
- Verify cron jobs are running to check stock levels

**2. Student preferences not saving**
- Verify student_id corresponds to an existing student
- Check database foreign key constraints
- Review validation error messages in API response

**3. Payment processing failures**
- Verify payment_method is a valid option
- Check student_id exists in the system
- Review transaction logs for specific error messages

### Database Issues

**Migration failures:**
```bash
# Rollback migration
php artisan migrate:rollback

# Run migration again
php artisan migrate
```

**Foreign key errors:**
- Ensure referenced records exist before creating relationships
- Check data types match between foreign keys and referenced columns
- Verify proper cascade delete rules are set

## Future Enhancements

The current implementation provides a solid foundation. Future enhancements could include:

- Advanced nutritional analysis and reporting
- Meal rating and feedback system
- Point-of-sale integration for real-time inventory updates
- Automated stock reordering based on consumption patterns
- Integration with external nutritional databases
- Mobile app for students to view menus and preferences
- Parent portal for managing student meal accounts
