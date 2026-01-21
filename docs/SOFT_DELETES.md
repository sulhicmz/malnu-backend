# Soft Deletes Implementation Guide

## Overview

Soft deletes are now implemented across all critical models in the Malnu Backend system. When a record is deleted, it is not permanently removed from the database but instead marked as deleted using a `deleted_at` timestamp.

## Benefits

### Data Recovery
- Accidentally deleted records can be restored
- No permanent data loss from accidental deletions
- Support for undo/restore functionality

### Audit Trail
- Complete history of record modifications
- Track when records were deleted and by whom
- Compliance with data retention policies

### Testing Safety
- Soft-deleted records don't interfere with development
- Test data can be cleaned up without affecting production records
- Isolated test environments

## Enabled Models

Soft delete functionality is enabled for the following models:

### User Management
- `User`
- `Student`
- `Teacher`
- `Staff`
- `Parent`

### School Management
- `Class`
- `Subject`
- `ClassSubject`
- `Schedule`
- `SchoolInventory`

### Academic Records
- `Grade`
- `Competency`
- `Report`
- `StudentPortfolio`
- `Exam`
- `QuestionBank`
- `ExamQuestion`
- `ExamAnswer`
- `ExamResult`

### E-Learning
- `LearningMaterial`
- `Assignment`
- `Quiz`
- `Discussion`
- `DiscussionReply`
- `VirtualClass`
- `VideoConference`

### Attendance
- `LeaveType`
- `LeaveBalance`
- `StaffAttendance`
- `LeaveRequest`
- `SubstituteTeacher`
- `SubstituteAssignment`

### Calendar
- `Calendar`
- `CalendarEvent`
- `CalendarEventRegistration`
- `CalendarShare`
- `ResourceBooking`

### Digital Library
- `Book`
- `BookLoan`
- `BookReview`
- `EbookFormat`
- `PpdbRegistration`
- `PpdbDocument`
- `PpdbTest`
- `PpdbAnnouncement`

### Monetization
- `MarketplaceProduct`
- `Transaction`
- `TransactionItem`

### Career Development
- `CareerAssessment`
- `CounselingSession`
- `IndustryPartner`

### AI Assistant
- `AiTutorSession`

## Excluded Models

The following models do NOT have soft deletes for specific reasons:

- `AuditLog` - Must remain permanent for security and compliance
- `Role`, `Permission`, and junction tables - System roles should use CASCADE delete

## Usage

### Soft Delete

Delete a record using the standard `delete()` method:

```php
$student = Student::find($id);
$student->delete(); // Sets deleted_at to current timestamp
```

### Restore

Restore a soft-deleted record:

```php
$student = Student::withTrashed()->find($id);
$student->restore(); // Sets deleted_at back to null
```

### Force Delete

Permanently remove a record:

```php
$student = Student::find($id);
$student->forceDelete(); // Removes record from database entirely
```

### Query Scopes

#### Normal Queries (Default)
By default, queries exclude soft-deleted records:

```php
$activeStudents = Student::all(); // Excludes soft-deleted records
```

#### Include Trashed
Include soft-deleted records in queries:

```php
$allStudents = Student::withTrashed()->get(); // Includes all records
```

#### Only Trashed
Query only soft-deleted records:

```php
$deletedStudents = Student::onlyTrashed()->get(); // Only soft-deleted records
```

#### Check if Trashed
Check if a specific record is soft-deleted:

```php
$student = Student::withTrashed()->find($id);
if ($student->trashed()) {
    // Record is soft-deleted
}
```

## API Endpoints

Soft deletes are transparent to API endpoints. Existing delete endpoints will perform soft deletes:

- `DELETE /api/students/{id}` - Soft deletes the student
- `DELETE /api/teachers/{id}` - Soft deletes the teacher
- `DELETE /api/users/{id}` - Soft deletes the user

To permanently delete a record, force delete endpoints can be implemented:

- `DELETE /api/students/{id}/force` - Permanently deletes the student
- `DELETE /api/teachers/{id}/force` - Permanently deletes the teacher

To restore a record:

- `POST /api/students/{id}/restore` - Restores a soft-deleted student
- `POST /api/teachers/{id}/restore` - Restores a soft-deleted teacher

## Migration Instructions

After merging the soft delete implementation, run the migrations:

```bash
php artisan migrate
```

This will add `deleted_at` columns to all 50+ critical tables.

## Testing

To run the soft deletes test suite:

```bash
vendor/bin/phpunit tests/Feature/SoftDeletesTest.php
```

The test suite includes tests for:
- Soft delete functionality
- Restore operations
- Force delete operations
- Query scopes (withTrashed, onlyTrashed)
- Multiple operations on the same records

## Security Considerations

- Soft-deleted records are excluded from API responses by default
- Only admin users should have access to restore/force-delete operations
- Audit logs remain permanent for compliance
- Consider implementing access control for restore and force-delete endpoints

## Compliance

Soft deletes help meet the following compliance requirements:

### GDPR
- Right to erasure: Data can be permanently deleted after retention period
- Data retention: Records retained for required periods before permanent deletion
- Data portability: Soft-deleted data can be restored when requested

### FERPA
- Educational data protection: Student records are not permanently lost
- Audit requirements: Complete record modification history
- Data integrity: Records maintained and recoverable

## Troubleshooting

### Records not appearing after delete
If records still appear in queries after deletion:

1. Check if the model extends the base `Model` class
2. Verify the `SoftDeletes` trait is included in the base model
3. Check if `deleted_at` column exists in the database

### Cannot restore deleted records
If restore operations fail:

1. Ensure you're using `withTrashed()` to find the record
2. Verify the record was actually soft-deleted (not force-deleted)
3. Check database permissions

### Performance issues
If queries are slow after implementing soft deletes:

1. Add index on `deleted_at` column for large tables
2. Use `withTrashed()` only when needed
3. Consider purging old soft-deleted records periodically

## Performance Considerations

Soft deletes add minimal performance overhead:
- Automatic `WHERE deleted_at IS NULL` on all queries
- Slight increase in database storage (timestamp column)
- Index on `deleted_at` column improves query performance

## Best Practices

1. **Use soft deletes by default** - All user-facing deletions should be soft deletes
2. **Implement force delete for admin operations** - Allow permanent deletion when necessary
3. **Add restore functionality** - Provide way to undo accidental deletions
4. **Regular cleanup** - Periodically purge old soft-deleted records (e.g., 30+ days old)
5. **Audit restore operations** - Log when records are restored for compliance
