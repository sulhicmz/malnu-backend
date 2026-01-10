# Redis Caching Strategy

## Overview

This document describes the Redis caching strategy implemented to improve application performance and reduce database load.

## Performance Targets

| Metric | Current | Target |
|---------|---------|--------|
| API Response Time | ~500ms | <200ms (60% improvement) |
| Cache Hit Ratio | 0% | >80% |
| Database Load | 100% | 50%+ reduction |

## Configuration

### Environment Variables

```bash
# Cache Configuration
CACHE_DRIVER=redis
CACHE_DEFAULT_TTL=3600    # 1 hour default
CACHE_TAGS_TTL=86400    # 24 hours for tagged cache
CACHE_QUERY_TTL=300        # 5 minutes for query cache

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

### What to Cache

**Static Data (TTL: 1-24 hours):**
- System settings and configuration
- Roles and permissions
- School information
- Class and subject data
- Calendar events

**Query Results (TTL: 5 minutes):**
- Student listings with filters
- Teacher listings
- Attendance reports
- Grade records

**Session Data (TTL: 2 hours):**
- User authentication sessions
- Session preferences

### What Not to Cache

- User-specific data (personal info)
- Real-time data (live attendance)
- Transaction data (grades, payments)
- Passwords and tokens
- Data that changes frequently (counts, live status)

## Usage

### Basic Caching

```php
use App\Services\CacheService;
use Hyperf\Di\Annotation\Inject;

class ExampleController
{
    #[Inject]
    private CacheService $cache;

    public function index()
    {
        $students = $this->cache->remember(
            'students:all',
            function () {
                return Student::with(['class'])->get();
            },
            3600 // 1 hour TTL
        );

        return $this->successResponse($students);
    }
}
```

### Cache Invalidation

When data changes, invalidate cache:

```php
// Invalidate single item
$this->cache->forget('students:all');

// Invalidate multiple items
$this->cache->forgetMultiple(['users:active', 'roles:all']);

// Flush all cache
$this->cache->flush();
```

### Cache Annotations

Use Hyperf's `#[Cacheable]` annotation for automatic caching:

```php
use Hyperf\Cache\Annotation\Cacheable;

class ExampleController
{
    #[Cacheable(prefix: "students", ttl: 3600)]
    public function index()
    {
        return Student::with(['class'])->get();
    }
}
```

## Response Caching

### CacheResponseMiddleware

The `CacheResponseMiddleware` provides HTTP-level caching for GET, HEAD, and OPTIONS requests.

**Cacheable Requests:**
- Methods: GET, HEAD, OPTIONS
- Status codes: 200, 301, 302, 304
- Content-Type: application/json
- No authorization headers

**Cache Headers:**
- `Cache-Control: public, max-age=<ttl>, must-revalidate`
- `ETag: <cache-key>`
- `X-Cache-Key: <cache-key>`
- `X-Cache-Status: HIT or MISS`
- `Age: <cache-age>`

### Configuration

Add middleware to `config/autoload/middlewares.php`:

```php
return [
    App\Http\Middleware\CacheResponseMiddleware::class,
];
```

Or apply to specific routes:

```php
use Hyperf\HttpServer\Annotation\Middleware;

class ExampleController
{
    #[Middleware(CacheResponseMiddleware::class)]
    public function index()
    {
        return Student::with(['class'])->get();
    }
}
```

## Query Caching Examples

### Cache Query Results

```php
$students = $this->cache->remember(
    'students:class:' . $classId,
    function () use ($classId) {
        return Student::where('class_id', $classId)
            ->with(['class'])
            ->get();
    },
    300 // 5 minutes
);
```

### Cache with Conditional Logic

```php
public function show(string $id)
{
    $student = $this->cache->remember(
        'student:' . $id,
        function () use ($id) {
            return Student::with(['class'])->find($id);
        }
    );

    return $this->successResponse($student);
}
```

## Performance Monitoring

### Cache Hit/Miss Tracking

Monitor cache effectiveness by checking headers:

```bash
# Check cache hit ratio
grep "X-Cache-Status: HIT" storage/logs/*.log | wc -l
grep "X-Cache-Status: MISS" storage/logs/*.log | wc -l

# Calculate hit ratio
hits=$(grep "X-Cache-Status: HIT" storage/logs/*.log | wc -l)
total=$(grep "X-Cache-Status:" storage/logs/*.log | wc -l)
ratio=$((hits * 100 / total))
echo "Cache hit ratio: ${ratio}%"
```

## Best Practices

1. **Cache static data** - Data that rarely changes (roles, permissions)
2. **Use appropriate TTL** - Shorter TTL for volatile data, longer for static
3. **Invalidate on changes** - Always clear cache when data is modified
4. **Don't cache everything** - User data, transactions, and frequently changing data
5. **Monitor performance** - Track cache hit/miss ratios
6. **Consider memory usage** - Redis has memory limits
7. **Use cache warming** - Pre-populate cache during off-peak hours
8. **Tag related items** - Use tags for bulk invalidation

## Troubleshooting

### Cache Not Working

**Check configuration:**
```bash
# Verify cache driver is redis
grep CACHE_DRIVER .env

# Verify Redis is running
docker-compose ps | grep redis

# Test Redis connection
redis-cli PING
```

**Common Issues:**

1. **Cache always MISS**
   - Check if cache key is correct
   - Verify data isn't being invalidated too frequently
   - Check TTL values are appropriate

2. **Stale data served**
   - Increase TTL for frequently changing data
   - Implement proper cache invalidation

3. **High memory usage**
   - Reduce cache TTL values
   - Remove rarely used items from cache
   - Consider using Redis maxmemory policy

4. **Performance not improving**
   - Monitor cache hit ratio (should be >80%)
   - Check if cached data is frequently accessed
   - Verify Redis isn't becoming bottleneck

### Redis Connection Issues

**Cannot connect to Redis:**
```bash
# Check Redis is accessible
redis-cli -h localhost -p 6379 PING

# Check Redis logs
docker-compose logs redis

# Test from application
php artisan tinker
>>> $redis = app('redis');
>>> $redis->ping();
```

**Connection pool exhausted:**
```bash
# Check Redis connection pool
redis-cli INFO clients

# Increase connection limit in config
# Or add connection pooling
```

## Advanced Topics

### Cache Warming

Pre-populate cache with frequently accessed data:

```php
public function warmCache(): void
{
    $frequentlyAccessed = [
        'students:active',
        'teachers:all',
        'classes:current',
    ];

    foreach ($frequentlyAccessed as $key) {
        $this->cache->rememberForever($key, function () use ($key) {
            return $this->getDataForCacheKey($key);
        });
    }
}
```

### Cache Segmentation

Use different Redis instances or databases for different cache types:

```php
// Static cache (long TTL)
$staticCache = $this->cache->get('static');

// Query cache (short TTL)
$queryCache = $this->cache->get('queries');

// Session cache (medium TTL)
$sessionCache = $this->cache->get('sessions');
```

## Integration with Existing Features

### JWT Authentication

JWT blacklist can use Redis for invalidation:

```bash
# Add JWT token to blacklist on logout
redis-cli SET "jwt:blacklist:$token" "1" EX 86400
```

### Rate Limiting

Rate limiting can use Redis for distributed limiting:

```php
$rateLimitKey = 'ratelimit:user:' . $userId;
$attempts = $this->redis->incr($rateLimitKey);

if ($attempts > $limit) {
    throw new RateLimitException('Too many requests');
}

$this->redis->expire($rateLimitKey, 3600);
```

### Session Management

Sessions stored in Redis provide:

- Distributed session access across multiple servers
- Fast session read/write operations
- Automatic expiration based on TTL
- Session invalidation on logout

## Security Considerations

### Cache Poisoning

- Validate cache keys
- Use signed cache keys for sensitive data
- Implement cache versioning
- Never cache user-specific private data

### Secure Headers

When caching responses:
- Don't cache authorized/private data
- Use `Cache-Control: private` for user-specific responses
- Consider `Vary` headers for user-specific content

### Data Privacy

- Don't cache personally identifiable information (PII)
- Clear user-specific cache on logout
- Implement opt-out for sensitive data

## Monitoring and Metrics

### Key Metrics to Track

1. **Cache Hit Ratio** - (HIT / (HIT + MISS)) * 100
2. **Average Response Time** - With vs without cache
3. **Memory Usage** - Redis memory consumption
4. **Keys Count** - Total cached items
5. **Eviction Rate** - Keys removed due to memory limits

### Monitoring Commands

```bash
# Redis info
redis-cli INFO

# Memory usage
redis-cli INFO memory

# Slow log
redis-cli SLOWLOG get 10

# Monitor specific keys
redis-cli MONITOR
```

## Future Enhancements

1. **Cache Partitioning** - Separate caches for different data types
2. **Distributed Cache Locking** - Prevent cache stampede
3. **Automatic Cache Warming** - Scheduled jobs to warm cache
4. **CDN Integration** - Push cache to edge locations
5. **Multi-level Caching** - L1: Redis, L2: CDN, L3: Browser
