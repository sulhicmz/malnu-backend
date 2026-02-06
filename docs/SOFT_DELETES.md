# Soft Deletes Implementation Guide

## Overview

This application implements soft deletes for all critical business data models. Soft deletes allow records to be marked as deleted without permanently removing them from the database, enabling data recovery and maintaining audit trails.

## What are Soft Deletes?

Soft deletes mean that when a record is "deleted", it is not actually removed from the database. Instead, a `deleted_at` timestamp column is set to the current time. The record remains in the database but is excluded from normal queries.

## Implementation

### 1. SoftDeletes Trait in Base Model

The base `App\Models\Model` class includes the `SoftDeletes` trait:

```php
use Hyperf\Database\Model\SoftDeletes;

abstract class Model extends BaseModel
{
    use SoftDeletes;

    protected ?string $connection = null;
}
```

This trait is automatically inherited by all models that extend `Model`.

### 2. Database Schema

All critical business tables have a `deleted_at` nullable timestamp column.

**Tables with Soft Deletes:**
- `users`, `password_reset_tokens`
- `students`, `teachers`, `staff`, `parents`, `classes`, `subjects`, `class_subjects`, `schedules`, `school_inventory`
- `grades`, `competencies`, `reports`, `student_portfolios`
- `virtual_classes`, `learning_materials`, `assignments`, `quizzes`, `discussions`, `discussion_replies`, `video_conferences`
- `online_exams`, `question_banks`, `exam_questions`, `exam_answers`, `exam_results`
- `books`, `book_loans`, `book_reviews`, `ebook_formats`, `ppdb_registrations`, `ppdb_documents`, `ppdb_tests`, `ppdb_announcements`
- `marketplace_products`, `transactions`, `transaction_items`
- `staff_attendance`, `leave_types`, `leave_balances`, `leave_requests`, `substitute_teachers`, `substitute_assignments`, `student_attendances`
- `calendars`, `calendar_events`, `calendar_event_registrations`, `calendar_shares`, `resource_bookings`
- `asset_categories`, `asset_assignments`, `asset_maintenance`
- `notifications`, `notification_recipients`, `notification_templates`, `notification_delivery_logs`, `notification_user_preferences`
- `career_assessments`, `counseling_sessions`, `industry_partners`, `ai_tutor_sessions`
- Health-related tables (already had soft deletes)

**Tables Without Soft Deletes (by design):**
- `AuditLog` - Must remain permanent for security and compliance
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` - Use CASCADE deletes for junction tables

## Usage

### Deleting Records

Use the standard `delete()` method:

```php
$user = User::find($id);
$user->delete();
```

This sets the `deleted_at` timestamp but doesn't permanently remove the record.

### Restoring Deleted Records

Use the `restore()` method:

```php
$user = User::withTrashed()->find($id);
$user->restore();
```

This clears the `deleted_at` timestamp.

### Force Deleting Records

To permanently delete a record (use with caution):

```php
$user = User::withTrashed()->find($id);
$user->forceDelete();
```

This permanently removes the record from the database.

### Querying Records

**Normal queries** automatically exclude soft-deleted records:

```php
$activeUsers = User::all();
```

**Include soft-deleted records** using `withTrashed()`:

```php
$allUsers = User::withTrashed()->get();
```

**Only soft-deleted records** using `onlyTrashed()`:

```php
$deletedUsers = User::onlyTrashed()->get();
```

**Custom queries** using `whereNotNull()`:

```php
$deletedUsers = User::whereNotNull('deleted_at')->get();
$activeUsers = User::whereNull('deleted_at')->get();
```

## Migration

After deploying this change, run:

```bash
php artisan migrate
```

This will add the `deleted_at` columns to all tables that don't have them yet.

## Security and Compliance

### GDPR Compliance
- Soft deletes maintain data for the required retention period
- Force delete available for permanent removal when legally required
- Audit trail maintains record of all deletions

### Data Recovery
- Accidentally deleted records can be restored
- Critical business data is protected from permanent loss
- Testing data cleanup doesn't affect production data

### Audit Trail
- Complete history of record modifications and deletions
- Deleted records remain in database for audit purposes
- Compliance with data retention requirements

## Performance Considerations

### Database Storage
- Soft-deleted records remain in database until permanently deleted
- Database size will gradually increase
- Consider periodic cleanup jobs for old soft-deleted records (e.g., 90+ days)

### Query Performance
- Normal queries include automatic `WHERE deleted_at IS NULL` clause
- Minimal performance impact with proper indexing
- Consider adding index on `deleted_at` column for frequently queried tables

## Troubleshooting

### Records Not Being Restored
Ensure you're using the correct method:
```php
// Wrong - doesn't restore
User::find($id)->update(['deleted_at' => null]);

// Correct
User::withTrashed()->find($id)->restore();
```

### Queries Still Returning Deleted Records
Check if you're using the correct query scope:
```php
// Wrong - includes deleted records
User::whereNotNull('deleted_at')->get();

// Correct - excludes deleted records (default behavior)
User::all();
```

### Permanent Deletion Not Working
Ensure you're calling `forceDelete()` on a trashed record:
```php
// Wrong - tries to delete with soft delete
User::find($id)->forceDelete();

// Correct - permanently deletes a soft-deleted record
User::onlyTrashed()->find($id)->forceDelete();
```

## Testing

Run soft delete tests:

```bash
vendor/bin/co-phpunit tests/Feature/SoftDeletesTest.php
```

Tests cover:
- Soft delete functionality
- Restore operations
- Force delete operations
- Query scopes (withTrashed, onlyTrashed)
- Exclusion of soft-deleted records from normal queries

## Related Issues

- Issue #353 - Implement soft deletes for critical models

## References

- Hyperf Documentation: https://hyperf.wiki
- Laravel Soft Deletes: https://laravel.com/docs/eloquent#soft-deleting
