# Soft Deletes Implementation Guide

## Overview

This system implements soft deletes for critical business data models. Soft deletes prevent permanent data loss by marking records as deleted with a `deleted_at` timestamp instead of physically removing them from the database.

## Benefits

- **Data Recovery**: Accidentally deleted records can be restored
- **Audit Trail**: Complete history of record modifications and deletions
- **Compliance**: Meets data retention requirements for educational systems
- **Testing**: Soft-deleted records don't interfere with development and testing

## Enabled Models

Soft deletes are enabled for the following critical models:

### User Management
- `User` - User accounts and authentication data
- `Student` - Student enrollment and academic records
- `Teacher` - Teacher employment and teaching history
- `Staff` - Staff employment records
- `ParentOrtu` - Parental information

### School Management
- `ClassModel` - Class information
- `Subject` - Subject catalog
- `ClassSubject` - Class-subject assignments
- `Schedule` - Class schedules
- `SchoolInventory` - School inventory

### Academic Records
- `Grade` - Student grades
- `Competency` - Student competencies
- `Report` - Academic reports
- `StudentPortfolio` - Student portfolios
- `Exam` - Examination records
- `QuestionBank` - Question banks
- `ExamQuestion` - Exam questions
- `ExamAnswer` - Exam answers
- `ExamResult` - Exam results

### E-Learning
- `LearningMaterial` - Learning materials
- `Assignment` - Assignments
- `Quiz` - Quizzes
- `Discussion` - Discussion threads
- `DiscussionReply` - Discussion replies
- `VirtualClass` - Virtual classes
- `VideoConference` - Video conferences

### Attendance
- `LeaveType` - Leave type definitions
- `LeaveBalance` - Leave balances
- `StaffAttendance` - Staff attendance records
- `LeaveRequest` - Leave requests
- `SubstituteTeacher` - Substitute teachers
- `SubstituteAssignment` - Substitute assignments

### Calendar
- `Calendar` - Calendar definitions
- `CalendarEvent` - Calendar events
- `CalendarEventRegistration` - Event registrations
- `CalendarShare` - Calendar shares
- `ResourceBooking` - Resource bookings

### Digital Library
- `Book` - Book catalog
- `BookLoan` - Book loans
- `BookReview` - Book reviews
- `EbookFormat` - E-book formats

### PPDB (Admissions)
- `PpdbRegistration` - PPDB registrations
- `PpdbDocument` - PPDB documents
- `PpdbTest` - PPDB tests
- `PpdbAnnouncement` - PPDB announcements

### Monetization
- `MarketplaceProduct` - Marketplace products
- `Transaction` - Transactions
- `TransactionItem` - Transaction items

### Career Development
- `CareerAssessment` - Career assessments
- `CounselingSession` - Counseling sessions
- `IndustryPartner` - Industry partners

### AI Assistant
- `AiTutorSession` - AI tutor sessions

## Excluded Models

The following models do NOT have soft deletes enabled for specific reasons:

- `AuditLog` - Audit logs must be permanent for security and compliance
- `Role` and `Permission` - System roles/permissions should not be deleted
- `ModelHasRole`, `ModelHasPermission`, `RoleHasPermission` - Junction tables use foreign key constraints

## Database Schema

Soft deletes are implemented by adding a `deleted_at` timestamp column to each table:

```sql
ALTER TABLE table_name ADD COLUMN deleted_at TIMESTAMP NULL;
```

When a record is soft deleted, the `deleted_at` column is set to the current timestamp. The record remains in the database but is automatically excluded from normal queries.

## API Usage

### Soft Delete (Default Behavior)

When you call `delete()` on a model, it will be soft deleted by default:

```php
$user = User::find($userId);
$user->delete();  // Sets deleted_at timestamp
```

The record will no longer appear in standard queries:

```php
User::all();  // Excludes soft-deleted records
User::find($userId);  // Returns null for soft-deleted records
```

### Restore Soft-Deleted Records

Restore a soft-deleted record:

```php
$trashedUser = User::withTrashed()->find($userId);
$trashedUser->restore();  // Sets deleted_at to null
```

### Query Soft-Deleted Records

Use the `withTrashed()` scope to include soft-deleted records:

```php
$allUsers = User::withTrashed()->all();  // Includes soft-deleted records
```

Use the `onlyTrashed()` scope to get only soft-deleted records:

```php
$trashedUsers = User::onlyTrashed()->all();  // Only soft-deleted records
```

### Force Delete (Permanent Deletion)

To permanently delete a record (bypass soft delete):

```php
$user = User::find($userId);
$user->forceDelete();  // Physically removes from database
```

## API Endpoints

### Regular Delete (Soft Delete)

**DELETE** `/api/users/{id}`

Soft deletes a user record. The user is marked as deleted but remains in the database.

**Response:**
```json
{
  "message": "User deleted successfully"
}
```

### Restore Record

**POST** `/api/users/{id}/restore`

Restores a soft-deleted user record.

**Response:**
```json
{
  "message": "User restored successfully",
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "deleted_at": null
  }
}
```

### Force Delete (Permanent Deletion)

**DELETE** `/api/users/{id}/force`

Permanently deletes a user record. This action cannot be undone.

**Response:**
```json
{
  "message": "User permanently deleted"
}
```

## Admin Operations

Only users with admin roles should have access to:

1. **View trashed records** - `/api/users/trashed`
2. **Restore records** - `/api/users/{id}/restore`
3. **Force delete records** - `/api/users/{id}/force`

## Data Retention Policy

Soft-deleted records should be retained for a configurable period before permanent deletion. The default retention period is **90 days**.

### Cleanup Job

A scheduled cleanup job permanently deletes records with `deleted_at` older than the retention period:

```php
// Runs daily at midnight
$schedule->command('cleanup:soft-deleted')
    ->daily()
    ->at('00:00');
```

## Security Considerations

1. **Access Control**: Restore and force delete operations should be restricted to admin users
2. **Audit Trail**: All delete, restore, and force delete operations are logged
3. **Data Privacy**: Soft-deleted records are excluded from API responses unless explicitly requested
4. **Authorization**: Use JWT authentication and role-based access control

## Migration Instructions

### Adding Migrations

After deploying this feature, run the migrations to add `deleted_at` columns:

```bash
php artisan migrate
```

### Verification

Verify soft deletes are working:

```bash
# Create a test record
php artisan tinker
>>> $user = User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password']);

# Soft delete it
>>> $user->delete();

# Verify it's excluded from normal queries
>>> User::find($user->id);  // Should return null

# Verify it exists with withTrashed()
>>> User::withTrashed()->find($user->id);  // Should return the user
```

## Troubleshooting

### Records Not Soft Deleting

If records are being permanently deleted instead of soft deleted:

1. Check that the model extends `App\Models\Model` (which includes SoftDeletes)
2. Verify the `deleted_at` column exists in the database table
3. Confirm foreign key constraints don't have `ON DELETE CASCADE`

### Soft-Deleted Records Appearing in Queries

If soft-deleted records are appearing in normal queries:

1. Check for direct database queries that bypass Eloquent
2. Verify the `deleted_at` column is nullable and has a default of null
3. Ensure no custom scopes are overriding the soft delete behavior

### Unable to Restore Records

If restore operations fail:

1. Verify the user has admin role
2. Check the record exists with `withTrashed()`
3. Confirm the `deleted_at` timestamp is set (not null)
4. Ensure no foreign key constraints are blocking the restore

## Performance Considerations

- **Database Size**: Soft-deleted records increase database size (mitigated by cleanup jobs)
- **Query Performance**: Slight performance impact from additional `WHERE deleted_at IS NULL` clause (typically negligible)
- **Indexing**: Consider adding indexes on `deleted_at` column for frequently queried tables

## Compliance

This soft delete implementation helps meet the following compliance requirements:

- **GDPR**: Data retention and right to erasure capabilities
- **FERPA**: Educational data protection and retention policies
- **Audit Requirements**: Complete audit trail of data modifications
- **Data Protection**: Prevents accidental data loss

## References

- Laravel Documentation: https://laravel.com/docs/eloquent#soft-deleting
- Hyperf Documentation: https://hyperf.wiki/3.0/#/en/db/eloquent
- Data Retention Best Practices: https://www.ed.gov/fpdoc
