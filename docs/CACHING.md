# Redis Caching System

This document describes the Redis caching system implemented for Malnu Backend API.

## Overview

The caching system provides high-performance data caching to achieve sub-200ms API response times. It uses Redis for distributed caching with automatic cache invalidation, TTL management, and response caching middleware.

## Architecture

### Components

1. **CacheService** - Centralized cache operations (app/Services/CacheService.php)
2. **CacheResponse Middleware** - HTTP response caching (app/Http/Middleware/CacheResponse.php)
3. **Service-level Caching** - Built into AttendanceService and other services
4. **Controller-level Caching** - Automatic CRUD caching via CrudOperationsTrait

## CacheService

### Overview

Centralized service for all cache operations with consistent key generation, TTL management, and automatic cache invalidation.

### Basic Operations

```php
use App\Services\CacheService;

$cache = new CacheService();

// Get cached value (returns null if not found)
$value = $cache->get('user:profile:123');

// Set value with TTL (time-to-live in seconds)
$cache->set('user:profile:123', $userData, 3600);

// Remember pattern - cache or compute if missing
$result = $cache->remember('expensive:operation', 300, function () {
    return $this->expensiveOperation();
});

// Forget specific key
$cache->forget('user:profile:123');

// Forget all keys matching prefix
$cache->forgetByPrefix('user:');

// Flush all cache
$cache->flush();
```

### TTL Constants

Predefined TTL values for consistency:

| Constant | Value | Use Case |
|----------|-------|----------|
| `TTL_SHORT` | 60 seconds | Real-time data (chronic absentees) |
| `TTL_MEDIUM` | 300 seconds (5 min) | User-facing data (attendance) |
| `TTL_LONG` | 3600 seconds (1 hour) | Reports and statistics |
| `TTL_DAY` | 86400 seconds (24 hours) | Rarely changing data |

### Cache Key Generation

Cache keys are automatically generated with MD5 hashing for consistent storage:

```php
// Simple key
$key = 'user:profile:123';

// Complex key (hashed)
$key = $cache->generateKey('attendance', ['student' => '123', 'date' => '2026-01-14']);
// Output: attendance:student:123:date:2026-01-14 (MD5 hashed)
```

### Cache Key Prefixes

All cache keys use prefixes for organized storage and easy invalidation:

| Prefix | Example Keys | Purpose |
|---------|--------------|---------|
| `attendance:` | attendance:student:123, attendance:class:456 | Attendance data |
| `user:` | user:profile:123, user:permissions:123 | User data |
| `api:` | api:/api/students, api:/api/teachers | API responses |
| `report:` | report:attendance:2026-01 | Report generation |

## AttendanceService Caching

### Cached Methods

AttendanceService automatically caches read operations and invalidates on write operations:

| Method | TTL | Invalidation Trigger |
|--------|-----|---------------------|
| `getStudentAttendance()` | 5 min | `markAttendance()`, `markBulkAttendance()` |
| `getClassAttendance()` | 5 min | `markAttendance()`, `markBulkAttendance()` |
| `calculateAttendanceStatistics()` | 5 min | `markAttendance()`, `markBulkAttendance()` |
| `calculateClassStatistics()` | 5 min | `markAttendance()`, `markBulkAttendance()` |
| `detectChronicAbsenteeism()` | 1 min | `markAttendance()`, `markBulkAttendance()` |
| `generateAttendanceReport()` | 1 hour | `markAttendance()`, `markBulkAttendance()` |

### Usage Example

```php
use App\Services\AttendanceService;

$attendanceService = new AttendanceService();

// First call - queries database
$attendance = $attendanceService->getStudentAttendance('student-uuid');

// Subsequent calls (within 5 min) - returns from cache
$attendance = $attendanceService->getStudentAttendance('student-uuid');

// Write operation - invalidates cache automatically
$attendanceService->markAttendance([
    'student_id' => 'student-uuid',
    'date' => '2026-01-14',
    'status' => 'present'
]);

// Next call - queries database again (cache invalidated)
$attendance = $attendanceService->getStudentAttendance('student-uuid');
```

### Manual Cache Invalidation

```php
// Invalidate all student attendance cache
$attendanceService->invalidateAttendanceCache();
```

## Controller Caching (CrudOperationsTrait)

### Overview

Controllers using CrudOperationsTrait automatically get caching for index() and show() methods with automatic invalidation on write operations.

### Enable Caching

```php
use App\Http\Controllers\BaseController;
use App\Traits\CrudOperationsTrait;

class StudentController extends BaseController
{
    use CrudOperationsTrait;

    protected bool $useCache = true;  // Enable caching
    protected int $cacheTTL = 300;    // 5 minutes TTL
    protected string $cachePrefix = 'student:';

    // index() and show() automatically cached
}
```

### Cached Operations

| Method | Cache Key Pattern | TTL | Auto-Invalidate |
|--------|------------------|-----|-----------------|
| `index()` | `{prefix}:index:{page}:{per_page}` | $cacheTTL | store() |
| `show($id)` | `{prefix}:show:{id}` | $cacheTTL | update(), destroy() |

### Disable Caching for Specific Controllers

```php
class AdminController extends BaseController
{
    use CrudOperationsTrait;

    protected bool $useCache = false;  // Disable caching
}
```

## CacheResponse Middleware

### Overview

HTTP response caching middleware that caches GET requests for API endpoints with automatic X-Cache headers.

### Configuration

Register middleware in `config/middleware.php`:

```php
'middleware' => [
    // ...
    \App\Http\Middleware\CacheResponse::class,
],
```

### Middleware Settings

| Setting | Value | Description |
|---------|-------|-------------|
| Default TTL | 300 seconds (5 min) | Cache duration for all responses |
| Cacheable Methods | GET only | Only GET requests are cached |
| Excluded Paths | /api/login, /api/register, /api/logout | Auth endpoints never cached |
| Cache Headers | X-Cache: HIT/MISS | Monitoring header for cache status |

### Excluded Paths

The following paths are excluded from caching (hardcoded for security):

- `/api/login` - Authentication endpoint
- `/api/register` - Registration endpoint
- `/api/logout` - Logout endpoint
- `/api/password/reset` - Password reset endpoint

### Usage

Add middleware to routes:

```php
// routes/api.php

use App\Http\Middleware\CacheResponse;

Route::middleware([CacheResponse::class])->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/teachers', [TeacherController::class, 'index']);
    Route::get('/classes', [ClassController::class, 'index']);
});

// Auth routes - no caching (excluded automatically)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
```

### Response Headers

Cached responses include monitoring headers:

```
HTTP/1.1 200 OK
Content-Type: application/json
X-Cache: HIT          # Cache hit (returned from cache)
X-Cache-Key: api:/api/students?page=1
Cache-Control: max-age=300
```

```
HTTP/1.1 200 OK
Content-Type: application/json
X-Cache: MISS         # Cache miss (fetched from API)
X-Cache-Key: api:/api/students?page=1
Cache-Control: max-age=300
```

## Configuration

### Environment Variables

Set in `.env`:

```env
# Cache driver (required)
CACHE_DRIVER=redis

# Redis connection (optional - uses Redis defaults)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
```

### Redis Configuration

Configure in `config/cache.php` (already configured for this project):

```php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
```

## Enabling Caching in Production

### Step 1: Start Redis Service

Using Docker:
```bash
docker-compose up -d redis
```

Or standalone Redis:
```bash
sudo systemctl start redis
```

### Step 2: Configure Environment

Edit `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Step 3: Verify Connectivity

```bash
php artisan cache:clear
php artisan config:cache
```

### Step 4: Enable Controller Caching

Add to controllers:
```php
protected bool $useCache = true;
```

### Step 5: Enable Response Caching (Optional)

Add CacheResponse middleware to routes:
```bash
# routes/api.php
use App\Http\Middleware\CacheResponse;

Route::middleware([CacheResponse::class])->group(function () {
    // Cached routes
});
```

## Performance Impact

### Expected Improvements

| Operation | Response Time (No Cache) | Response Time (With Cache) | Improvement |
|-----------|-------------------------|---------------------------|-------------|
| Student attendance lookup | ~200ms | ~20ms | 90% reduction |
| Class attendance statistics | ~500ms | ~100ms | 80% reduction |
| Attendance report generation | ~1000ms | ~50ms | 95% reduction |
| Generic CRUD read | ~150ms | ~30ms | 80% reduction |
| API endpoint response | ~200ms | ~10ms | 95% reduction |

### Cache Hit Rates (Estimated)

| Data Type | Estimated Hit Rate | Reason |
|-----------|-------------------|--------|
| Student attendance | 70-80% | Teachers checking same students repeatedly |
| Class attendance | 80-90% | Multiple teachers checking same class |
| Attendance statistics | 60-70% | Moderate frequency access |
| Chronic absentees | 40-50% | Lower frequency, short TTL |
| API responses | 95%+ | Highly repetitive GET requests |

## Monitoring & Debugging

### Check Redis Status

```bash
# Check if Redis is running
redis-cli ping
# Output: PONG

# Check Redis info
redis-cli info

# Check cache keys
redis-cli KEYS "attendance:*"

# Check key TTL
redis-cli TTL attendance:student:123
```

### Clear Cache

```bash
# Clear all cache
php artisan cache:clear

# Clear specific cache prefix (requires custom command)
redis-cli --scan --pattern "attendance:*" | xargs redis-cli DEL
```

### Monitor Cache Performance

Watch X-Cache headers in browser dev tools or use monitoring tools:

```bash
# View cache headers
curl -I http://localhost:9501/api/students
```

## Testing

### CacheService Tests

Tests in `tests/Feature/CacheServiceTest.php`:
- Basic get/set/forget operations
- Remember pattern with callbacks
- Key generation and hashing
- TTL value management
- Complex data type caching (arrays, objects)
- Flush operations

Run tests:
```bash
vendor/bin/co-phpunit tests/Feature/CacheServiceTest.php
```

### Integration Tests

Test cache behavior with real Redis:
```bash
# Ensure Redis is running
docker-compose up -d redis

# Run tests
composer test
```

## Best Practices

### When to Cache

✅ **DO Cache:**
- Read-heavy operations (attendance lookups, statistics)
- Computationally expensive queries (reports, aggregations)
- Data that doesn't change frequently (user profiles, class lists)
- API responses that benefit from sub-100ms response times

❌ **DON'T Cache:**
- Real-time data requiring immediate consistency
- Write operations (POST, PUT, PATCH, DELETE)
- Authentication and authorization checks
- Payment transactions or financial operations
- Data with strict privacy requirements

### TTL Selection

| Data Type | Recommended TTL | Rationale |
|-----------|-----------------|-----------|
| Real-time alerts | 60s (short) | Freshness critical |
| User data | 300s (medium) | Balance freshness and performance |
| Statistics | 300s (medium) | Acceptable 5-minute delay |
| Reports | 3600s (long) | Expensive to compute |
| Reference data | 86400s (day) | Rarely changes |

### Cache Invalidation

Always implement cache invalidation when data changes:
- Service methods: Auto-invalidate via `invalidateAttendanceCache()`
- Controllers: Auto-invalidate via CrudOperationsTrait
- Custom: Use `CacheService::forget()` or `forgetByPrefix()`

### Key Prefixes

Use consistent key prefixes:
- Organize by domain: `attendance:`, `user:`, `report:`
- Enable easy invalidation: `forgetByPrefix('attendance:')`
- Avoid conflicts: Use descriptive prefixes

## Troubleshooting

### Cache Not Working

**Issue**: Data not being cached

**Solutions**:
1. Check Redis is running: `redis-cli ping`
2. Verify CACHE_DRIVER=redis in `.env`
3. Clear cache: `php artisan cache:clear`
4. Check Redis logs: `docker-compose logs redis`

### Cache Stale Data

**Issue**: Old data returned from cache

**Solutions**:
1. Manually invalidate: `$cache->forgetByPrefix('attendance:')`
2. Clear all cache: `php artisan cache:clear`
3. Check TTL is appropriate for use case
4. Verify auto-invalidation on write operations

### Redis Connection Issues

**Issue**: Cannot connect to Redis

**Solutions**:
1. Verify Redis is running: `redis-cli ping`
2. Check REDIS_HOST and REDIS_PORT in `.env`
3. Verify Redis container is up: `docker-compose ps`
4. Check Redis logs: `docker-compose logs redis`

## Future Enhancements

Planned improvements (deferred to future sprints):

- Cache monitoring dashboard with hit/miss rates
- Automatic cache warming on application startup
- Configurable cache tags for organized invalidation
- Distributed cache invalidation across multiple instances
- Cache compression for large objects
- Cache analytics and reporting

---

**Last Updated**: January 14, 2026
**Implemented**: TASK-52 - Redis Caching System
**Related**: [Performance Standards](../blueprint.md#performance-standards)
