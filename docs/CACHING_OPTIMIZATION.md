# Caching and Performance Optimization Guide

This guide explains the caching and performance optimization strategies implemented in the Malnu Backend.

## Overview

The application now uses **Redis caching** for improved performance and reduced database load. The implementation includes:

- **Centralized Cache Service** - Unified caching operations via `CacheService`
- **Redis Session Storage** - Fast, distributed session management
- **Response Caching Middleware** - Automatic API response caching
- **Performance Monitoring** - Track request performance and identify bottlenecks

## Architecture

### Cache Service (`App\Services\CacheService`)

A centralized service for all cache operations with Redis backend.

**Features:**
- Simple `get/set/forget` operations
- Tag-based caching for selective invalidation
- Cache metrics tracking (hits, misses)
- Async caching support for non-blocking operations
- Multiple key operations

### Response Cache Middleware (`App\Http\Middleware\ResponseCache`)

Automatically caches HTTP GET responses for static data.

**Features:**
- Automatic caching of successful GET requests
- Configurable TTL per endpoint
- Cache invalidation tags
- Exclusion for dynamic/auth routes
- Cache headers (X-Cache-Status, Cache-Control, Age, ETag)

### Performance Monitoring (`App\Services\PerformanceMonitoringService`)

Tracks application performance and provides insights.

**Features:**
- Request counting and timing
- Slow request detection
- Average response time calculation
- Performance status (excellent, good, degraded, critical)
- Recommendations for optimization

## Configuration

### Environment Variables

Add these to your `.env` file:

```bash
# Redis Configuration
REDIS_HOST=redis           # Docker service name or localhost
REDIS_PORT=6379           # Redis port
REDIS_DB=0                # Redis database number

# Cache Configuration
CACHE_DRIVER=redis         # Cache driver (redis, array, file)
CACHE_DEFAULT_TTL=3600    # Default cache TTL in seconds (1 hour)
CACHE_RESPONSE_TTL=300     # API response cache TTL (5 minutes)
CACHE_TAGS_TTL=86400      # Cache tags TTL (24 hours)

# Session Configuration
SESSION_DRIVER=redis        # Session driver (redis, database, file)
SESSION_LIFETIME=120       # Session lifetime in minutes

# Performance Configuration
PERFORMANCE_SLOW_THRESHOLD=200    # Slow request threshold in milliseconds
PERFORMANCE_DB_SLOW_THRESHOLD=100  # Database slow query threshold in milliseconds
```

## Usage Examples

### Using CacheService

```php
use App\Services\CacheService;

class ExampleService
{
    private CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function getStudents()
    {
        // Cache with automatic miss handling
        $students = $this->cache->remember(
            'students:all',
            fn() => Student::all(),
            3600 // 1 hour TTL
        );

        return $students;
    }

    public function getUser(int $userId)
    {
        // Simple get/set
        $user = $this->cache->get("user:{$userId}");

        if ($user === null) {
            $user = User::find($userId);
            $this->cache->set("user:{$userId}", $user, 1800);
        }

        return $user;
    }

    public function invalidateUserCache(int $userId)
    {
        // Clear specific key
        $this->cache->forget("user:{$userId}");

        // Or invalidate by tag
        $this->cache->forgetByTag("user:{$userId}");
    }
}
```

### Tag-Based Caching

Cache tags allow selective invalidation of related cache entries.

```php
// Cache with a tag
$this->cache->setWithTag(
    'user:123:profile',
    'user:123',
    $userData,
    3600
);

// Cache multiple items with same tag
$this->cache->setWithTag('post:456:data', 'post:456', $postData, 3600);
$this->cache->setWithTag('post:456:meta', 'post:456', $metaData, 3600);

// Invalidate all items with same tag
$this->cache->forgetByTag('post:456');

// Useful for invalidating related data
$this->cache->forgetByTag('user:123'); // Clears user:123:profile, user:123:posts, etc.
```

### Response Caching

The `ResponseCache` middleware automatically caches GET requests.

**Automatic caching applies to:**
- GET and HEAD requests
- 200 status responses
- JSON content type responses
- Routes not in excluded list

**Excluded from caching:**
- POST, PUT, DELETE, PATCH requests
- Auth routes (`/api/auth/*`)
- User-specific routes (`/api/user/*`)
- Routes with dynamic data

**Configurable exclusions:**
Edit `ResponseCache::$excludedRoutes` in `app/Http/Middleware/ResponseCache.php` to add more exclusions.

### Performance Monitoring

```php
use App\Services\PerformanceMonitoringService;

class ExampleController
{
    private PerformanceMonitoringService $performance;

    public function __construct(PerformanceMonitoringService $performance)
    {
        $this->performance = $performance;
    }

    public function slowEndpoint()
    {
        $startTime = microtime(true);

        // ... do work ...

        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to ms

        // Record request
        $isSlow = $this->performance->isSlow($responseTime);
        $this->performance->recordRequest($responseTime, $isSlow);

        return response();
    }

    public function getPerformanceMetrics()
    {
        $metrics = $this->performance->getMetrics();

        return [
            'total_requests' => $metrics['request_count'],
            'avg_response_time' => $metrics['avg_response_time'] . 'ms',
            'slow_requests' => $metrics['slow_requests'],
            'slow_rate' => $metrics['slow_rate'] . '%',
            'status' => $this->performance->getPerformanceStatus(),
        ];
    }

    public function getRecommendations()
    {
        return $this->performance->getRecommendations();
    }
}
```

## Cache Invalidation Strategies

### When to Invalidate Cache

**Manual invalidation:**
- After updating user data
- After saving/creating/deleting records
- When configuration changes

**Tags for selective invalidation:**
```php
// User update - invalidate all user-related cache
$user->update($data);
$this->cache->forgetByTag("user:{$userId}");

// Post update - invalidate post and related cache
$post->update($data);
$this->cache->forgetByTag("post:{$postId}");

// Bulk invalidation
$this->cache->forget('students:all');
$this->cache->forget('teachers:all');
```

**Automatic invalidation:**
- Use TTL to automatically expire cached data
- Set appropriate TTL based on data volatility
  - Static data (roles, permissions): 24 hours
  - User data: 1 hour
  - API responses: 5 minutes

## Performance Best Practices

### 1. Cache Static Data First

**What to cache:**
- System configuration
- Role and permission data
- School information (classes, subjects)
- Reference data (lookups, enums)

### 2. Avoid Over-Caching

**Don't cache:**
- User-specific data with high volatility
- Frequently changing data (attendance, real-time status)
- Financial or sensitive information
- Large responses (>1MB)

### 3. Use Appropriate TTL Values

| Data Type | Recommended TTL | Rationale |
|------------|-----------------|-----------|
| Static config | 86400s (24h) | Changes infrequently |
| User data | 3600s (1h) | Moderate change rate |
| API responses | 300s (5m) | Freshness important |
| Lookup tables | 14400s (4h) | Rarely changes |

### 4. Monitor Performance

Regularly check cache metrics:

```php
$cacheMetrics = $cache->getMetrics();
$performanceMetrics = $performance->getMetrics();

// Target metrics
$targetHitRate = 80;
$targetSlowRate = 10;
$targetAvgResponse = 200; // ms

// Check if targets met
if ($cacheMetrics['hit_rate'] < $targetHitRate) {
    log("Cache hit rate below target: {$cacheMetrics['hit_rate']}%");
}

if ($performanceMetrics['avg_response_time'] > $targetAvgResponse) {
    log("Average response time above target: {$performanceMetrics['avg_response_time']}ms");
}
```

### 5. Optimize Database Queries

Caching reduces database load, but slow queries still hurt performance.

**Add indexes** (see issue #357):
- Frequently filtered columns
- Foreign keys
- Composite indexes for common query patterns

**Use eager loading:**
```php
// Bad: N+1 queries
$students = Student::all();
foreach ($students as $student) {
    echo $student->class->name;
}

// Good: 1 query with eager loading
$students = Student::with('class')->get();
foreach ($students as $student) {
    echo $student->class->name;
}
```

## Troubleshooting

### Cache Not Working

1. **Check Redis connection:**
   ```bash
   php artisan tinker
   >>> $redis = new Redis();
   >>> $redis->connect('redis', 6379);
   >>> $redis->ping();
   true
   ```

2. **Verify cache driver:**
   Check `.env` has `CACHE_DRIVER=redis`

3. **Check Redis logs:**
   ```bash
   docker-compose logs redis
   ```

### High Memory Usage

1. **Check cache keys:**
   ```bash
   redis-cli KEYS '*' | wc -l
   ```

2. **Flush cache if needed:**
   ```bash
   redis-cli FLUSHDB
   ```

3. **Reduce TTL values** for frequently changing data

### Slow Performance Despite Caching

1. **Check cache hit rate:**
   ```php
   $metrics = $cache->getMetrics();
   echo "Hit rate: {$metrics['hit_rate']}%";
   ```

2. **Review slow queries:**
   ```php
   $metrics = $performance->getMetrics();
   print_r($metrics['slow_queries']);
   ```

3. **Enable query logging** to identify bottlenecks

## Monitoring Dashboard

### Cache Metrics

Access cache metrics:

```php
$cache = new CacheService();
$metrics = $cache->getMetrics();
```

**Metrics:**
- `hits` - Number of cache hits
- `misses` - Number of cache misses
- `hit_rate` - Percentage of cache hits
- `sets` - Number of cache sets
- `deletes` - Number of cache deletions

### Performance Metrics

Access performance metrics:

```php
$performance = new PerformanceMonitoringService();
$metrics = $performance->getMetrics();
```

**Metrics:**
- `request_count` - Total requests handled
- `total_response_time` - Cumulative response time
- `avg_response_time` - Average response time in ms
- `slow_requests` - Number of slow requests
- `slow_rate` - Percentage of slow requests
- `slow_queries` - Array of slow database queries

**Performance Status:**
- `excellent` - <5% slow, <100ms average
- `good` - <10% slow, <200ms average
- `degraded` - <20% slow, <300ms average
- `critical` - >=20% slow or >=300ms average

## Testing Cache Functionality

Run cache tests:

```bash
php artisan test --filter=Cache
```

## Security Considerations

1. **Never cache sensitive data:**
   - Passwords
   - Personal information
   - Financial data
   - Session tokens

2. **Use secure Redis connection:**
   - Enable AUTH if Redis requires authentication
   - Use TLS for remote Redis connections
   - Restrict Redis network access

3. **Validate cached data:**
   - Always validate data integrity after retrieval
   - Handle cache misses gracefully
   - Set appropriate TTL to prevent stale data

## Performance Targets

Based on issue requirements, the implementation targets:

| Metric | Current | Target | Status |
|---------|----------|--------|--------|
| API Response Time | ~500ms | <200ms | 60% improvement |
| Cache Hit Ratio | 0% | >80% | TBD |
| Database Load | 100% | <50% | TBD |
| Slow Request Rate | N/A | <10% | TBD |

## Related Issues

- **#357** - Add missing database indexes for frequently queried fields
- **#356** - Implement request/response logging middleware
- **#227** - Implement application monitoring and observability system

## Support

For issues or questions:
1. Check this guide
2. Review cache metrics in logs
3. Monitor Redis connection
4. Review performance recommendations

---

**Last Updated:** 2026-01-10
