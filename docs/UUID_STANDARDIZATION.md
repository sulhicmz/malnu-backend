# UUID Standardization Guide

## Overview

This document describes the standardized UUID implementation used across the Malnu Backend application. The codebase now uses a consistent, optimized approach for UUID generation and validation.

## UUID Generation Method

### Database-Level UUID (Recommended)

The application uses **MySQL 8.0+ native `UUID()` function** for UUID generation via `Db::raw('(UUID())')` in the `UsesUuid` trait.

**Benefits of Database-Level UUID:**

1. **Performance**: MySQL generates UUIDs natively with optimized C code, faster than PHP-level generation
2. **Distribution**: Better randomization and distribution for indexing
3. **Consistency**: Matches database-level approach used in migrations
4. **Overhead**: Reduced PHP execution overhead

### When to Use Database-Level vs Application-Level

| Scenario | Recommended Approach | Reason |
|-----------|---------------------|---------|
| New UUID generation (default) | Database-level `Db::raw('(UUID())')` | Optimized performance, better distribution |
| UUID testing/unit tests | Application-level `Str::uuid()`` | Easier to test, deterministic |
| Legacy compatibility | Application-level `Str::uuid()`` | Matches older patterns |

## UUID Validation

The `UsesUuid` trait provides three validation methods:

### `isValidUuid(string $uuid): bool`

Validates if a string is a valid UUID v4 format following RFC 4122.

**Format:** 8-4-4-4-12 (hex digits separated by hyphens)

```php
use App\Traits\UsesUuid;

$isValid = UsesUuid::isValidUuid('550e8400-e29b-41d4-a716-446655');

if ($isValid) {
    // UUID is valid v4 format
}
```

### `normalizeUuid(string $uuid): string`

Normalizes UUID to lowercase for consistent storage and comparison.

```php
$normalized = UsesUuid::normalizeUuid('550E8400-E29B-41D4-A716-446655');
// Result: '550e8400-e29b-41d4-a716-446655'
```

### `validateAndNormalizeUuid(string $uuid): ?string`

Convenience method that validates and normalizes a UUID in one call. Returns `null` if invalid.

```php
$result = UsesUuid::validateAndNormalizeUuid('INVALID-UUID');
if ($result === null) {
    // Handle invalid UUID
}
```

## Usage in Models

Models using the `UsesUuid` trait will automatically generate UUIDs using database-level generation:

```php
<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Hyperf\Db\Model\Model;

class User extends Model
{
    use UsesUuid;

    protected $table = 'users';
    
    // UUID is automatically generated and set via initializeUsesUuid()
}
```

## Database Migrations

### Consistency with UsesUuid Trait

When creating migrations with UUID primary keys, ensure consistency with the trait by using database-level UUID:

```php
<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Consistent with UsesUuid trait - uses database-level UUID
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            
            $table->string('name', 255);
            $table->datetimes();
        });
    }
}
```

## Security Best Practices

### UUID in URLs and APIs

- **Always Normalize**: Use `normalizeUuid()` before comparing or storing
- **Validate Input**: Use `isValidUuid()` for user-provided UUIDs
- **Use validateAndNormalizeUuid()**: Combines both for API endpoints

### Example API Validation

```php
public function getRecord(string $uuid)
{
    $normalizedUuid = UsesUuid::validateAndNormalizeUuid($uuid);
    
    if ($normalizedUuid === null) {
        return $this->errorResponse('Invalid UUID format');
    }
    
    $record = Model::find($normalizedUuid);
    // ...
}
```

## UUID Validation Patterns

### Valid UUID v4 Examples

- `550e8400-e29b-41d4-a716-446655`
- `6ba7b810-9dad-11d1-80b402-1234567`
- `a1b2c3d-4e5f-6a4e7aa4c0e32a123abcde`

### Invalid UUID Examples

- `not-a-uuid` - Wrong format
- `550e8400` - Missing segments
- `550e8400-E29B-41D4-A716-446655XYZ` - Invalid characters
- `g00-b00-0000-0000-0000-0000-000` - Invalid characters

## Performance Considerations

### Indexing

UUID v4 randomization provides good distribution for B-tree indexes:
- UUIDs are uniformly distributed across index space
- Avoids index hotspot issues common with auto-incrementing integers

### Storage

- **Binary format**: Consider using `CHAR(36)` for UUIDs, or `BINARY(16)` for even better performance
- **Collation**: Use case-insensitive collation (`utf8mb4_unicode_ci`) for consistent comparison

### Query Optimization

```php
// Good - uses UUID directly
User::where('id', $uuid)->first();

// Good - uses normalized UUID
$normalized = UsesUuid::normalizeUuid($uuid);
User::where('id', $normalized)->first();

// Avoid - string operations in WHERE clauses
// Bad: LIKE operations on UUIDs
User::where('id', 'like', '%' . $uuid . '%')->first();
```

## Troubleshooting

### Issue: Duplicate UUIDs

**Problem**: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry`

**Solution**: 
- Ensure all models using `UsesUuid` call `initializeUsesUuid()` before saving
- Check for duplicate data before insert
- Use unique constraints in migrations

### Issue: UUID Format Inconsistency

**Problem**: UUIDs appearing with different formats (uppercase, lowercase, with/without hyphens)

**Solution**: Always use `normalizeUuid()` for storage and comparison:
```php
$normalized = UsesUuid::normalizeUuid($uuid);
User::create(['id' => $normalized, ...]);
```

### Issue: Migration Conflicts

**Problem**: New migration wants different UUID format than existing data

**Solution**: 
- Create migration to standardize existing data to new format
- Use separate columns during transition: `new_id` and `old_id`
- Rollout changes incrementally

## Migration Strategy for Existing Data

When changing UUID generation method in production:

1. **Backup database** before changes
2. **Test migration** on staging with copy of production data
3. **Create data verification script** to validate UUIDs before/after migration
4. **Rollback plan** ready to revert if issues occur
5. **Monitor** after migration for UUID-related errors

## Compliance and Regulations

### GDPR (EU General Data Protection Regulation)
- UUIDs are considered pseudonymized identifiers
- Cannot directly identify individuals without additional data
- Store processing activities separately with UUID references

### FERPA (Family Educational Rights and Privacy Act)
- UUIDs support student privacy through indirect identification
- Maintain audit trails linking UUID-based records to students

### ISO/IEC 27025
- UUID v4 follows ISO/IEC 11578:1996 standard
- RFC 4122 compliant

## References

- [RFC 4122: A Universally Unique IDentifier (UUID) URN Namespace](https://tools.ietf.org/html/rfc4122)
- [MySQL 8.0 Reference Manual - UUID() Function](https://dev.mysql.com/doc/refman/8.0/en/miscellaneous-functions.html#function_uuid)
- [Best Practices for UUIDs in Database Systems](https://www.percona.com/blog/2019/11/22/uuids-and-performance/)
