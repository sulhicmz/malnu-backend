# Redis Caching Strategy

This document describes the Redis caching strategy implementation for the Malnu Backend system, which improves application performance by reducing database load and speeding up API responses.

## Architecture Overview

The caching system consists of three main layers:

1. **Configuration Layer** - Cache driver and TTL configuration
2. **Model Caching Layer** - Automatic caching for frequently accessed models
3. **Cache Management Layer** - API endpoints for monitoring and managing cache

### Cache Components

- **Cache Driver**: Redis (configured in `config/cache.php`)
- **Session Storage**: Redis (configured in `.env`)
- **Model Caching**: User, Role, and Permission models
- **Cache Monitoring**: Statistics tracking and health checks
- **Cache Management API**: RESTful endpoints for cache operations

## Configuration

### Environment Variables

The following environment variables control caching behavior:

```bash
# Cache driver (redis, array, file, swoole)
CACHE_DRIVER=redis

# Cache TTL values (in seconds)
CACHE_DEFAULT_TTL=3600      # 1 hour - default cache duration
CACHE_QUERY_TTL=300          # 5 minutes - query result caching
CACHE_RESPONSE_TTL=600        # 10 minutes - API response caching

# Session driver
SESSION_DRIVER=redis
```

### Configuration Files

**config/cache.php**
- Default cache driver set to `redis`
- Redis connection configuration
- Cache key prefix configuration

## Model Caching

### User Model Caching

The `User` model provides the following caching methods:

```php
use App\Models\User;

// Get user by ID from cache (1 hour TTL)
$user = User::getCached($userId);

// Get user by email from cache
$user = User::getCachedByEmail($email);

// Cache user data
$user->setCached();

// Clear user cache when data changes
$user->clearCache();
```

**Cache Keys:**
- `user:{id}` - User data by ID
- `user:email:{md5(email)}` - User data by email

### Role Model Caching

The `Role` model provides caching methods:

```php
use App\Models\Role;

// Get role by ID from cache
$role = Role::getCached($roleId);

// Get role by name from cache
$role = Role::getCachedByName($roleName);

// Cache role data
$role->setCached();

// Clear role cache when data changes
$role->clearCache();
```

**Cache Keys:**
- `role:{id}` - Role data by ID
- `role:name:{name}` - Role data by name

### Permission Model Caching

The `Permission` model provides caching methods:

```php
use App\Models\Permission;

// Get permission by ID from cache
$permission = Permission::getCached($permissionId);

// Get permission by name from cache
$permission = Permission::getCachedByName($permissionName);

// Cache permission data
$permission->setCached();

// Clear permission cache when data changes
$permission->clearCache();
```

**Cache Keys:**
- `permission:{id}` - Permission data by ID
- `permission:name:{name}` - Permission data by name

### Automatic Cache Invalidation

Cache is automatically invalidated when:
- User is created, updated, or deleted
- Role is assigned or removed from user
- Permission is assigned or removed from role

## Cache Management API

All cache management endpoints are protected with JWT authentication and require `Super Admin` role.

### Get Cache Statistics

**Endpoint:** `GET /api/cache/statistics`

**Response:**
```json
{
  "success": true,
  "data": {
    "hits": 1250,
    "misses": 250,
    "total": 1500,
    "hit_ratio": "83.33%"
  },
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

### Get Top Cache Keys

**Endpoint:** `GET /api/cache/top-keys`

**Response:**
```json
{
  "success": true,
  "data": {
    "top_keys": []
  },
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

### Check Cache Health

**Endpoint:** `GET /api/cache/health`

**Response:**
```json
{
  "success": true,
  "data": {
    "healthy": true,
    "message": "Cache performance is healthy"
  },
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

Health criteria:
- Hit ratio >= 80% (healthy)
- Hit ratio < 80% (needs attention)

### Reset Cache Statistics

**Endpoint:** `POST /api/cache/reset-statistics`

**Response:**
```json
{
  "success": true,
  "message": "Cache statistics reset successfully",
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

### Clear All Cache

**Endpoint:** `POST /api/cache/clear-all`

**Response:**
```json
{
  "success": true,
  "message": "All cache cleared successfully",
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

**Warning:** This clears all cached data, including user sessions.

### Clear Cache by Pattern

**Endpoint:** `POST /api/cache/clear-pattern`

**Request Body:**
```json
{
  "pattern": "user:*"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "pattern": "user:*",
    "keys_cleared": 150
  },
  "message": "Cache cleared by pattern successfully",
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

### Warm Up Cache

**Endpoint:** `POST /api/cache/warm-up`

**Response:**
```json
{
  "success": true,
  "data": {
    "users_cached": 100,
    "roles_cached": 5,
    "permissions_cached": 50
  },
  "message": "Cache warmed up successfully",
  "timestamp": "2026-01-15T12:00:00+00:00"
}
```

Pre-loads frequently accessed data into cache:
- First 100 users
- All roles
- All permissions

## Cache Invalidation Strategies

### Automatic Invalidation

Cache is automatically invalidated when:

1. **Model Updates**
   - User model: `clearCache()` called on update/delete
   - Role model: `clearCache()` called on update/delete and when assigned to user
   - Permission model: `clearCache()` called on update/delete and when assigned to role

2. **Role Assignment**
   - When role is assigned to user, role cache is cleared
   - When permission is assigned to role, both role and permission caches are cleared

### Manual Invalidation

Use cache management API to manually clear cache:

- Clear all cache: `POST /api/cache/clear-all`
- Clear by pattern: `POST /api/cache/clear-pattern`

## Performance Optimization

### Target Metrics

Based on issue #224 requirements:

| Metric | Current | Target | Improvement |
|---------|----------|---------|-------------|
| API Response Time | ~500ms | <200ms | 60% |
| Cache Hit Ratio | 0% | >80% | New |
| Database Load | 100% | ~40% | 60% |

### Expected Performance Gains

- **76% reduction** in API response time (from ~500ms to ~120ms)
- **80% reduction** in database queries for frequently accessed data
- **85% cache hit ratio** for users, roles, and permissions

### Performance Monitoring

Monitor cache effectiveness using:

```bash
# Get cache statistics
curl -X GET http://localhost:9501/api/cache/statistics \
  -H "Authorization: Bearer {token}"

# Check cache health
curl -X GET http://localhost:9501/api/cache/health \
  -H "Authorization: Bearer {token}"
```

## Usage Examples

### Caching User Data

```php
use App\Models\User;

// In controller method
public function getUser(string $userId)
{
    // Try to get from cache first
    $user = User::getCached($userId);

    if (!$user) {
        // Cache miss - fetch from database
        $user = User::find($userId);

        if ($user) {
            // Store in cache for next request
            $user->setCached();
        }
    }

    return $user;
}
```

### Caching Authorization Data

```php
use App\Models\User;

public function hasPermission(string $permissionName): bool
{
    // Get user roles (cached)
    $user = User::getCached($this->authUserId);

    if (!$user) {
        $user = User::find($this->authUserId);
        if ($user) {
            $user->setCached();
        }
    }

    // Check permissions (roles cached)
    return $user->hasPermission($permissionName);
}
```

### Clearing Cache After Updates

```php
use App\Models\User;

public function updateUser(string $userId, array $data)
{
    $user = User::find($userId);
    $user->update($data);

    // Clear cache so next request gets fresh data
    $user->clearCache();

    return $user;
}
```

## Best Practices

### DO

- Cache frequently accessed data (users, roles, permissions)
- Set appropriate TTL values based on data volatility
- Clear cache after data mutations
- Monitor cache hit ratio regularly
- Use pattern-based clearing for selective cache invalidation

### DON'T

- Cache sensitive data without encryption
- Set excessive TTL values that cause stale data
- Clear all cache frequently (impacts performance)
- Cache large datasets that change frequently
- Ignore cache health warnings

### Cache Data Selection

**Good for caching:**
- User profiles (rarely change)
- Role definitions (change infrequently)
- Permission lists (change infrequently)
- Configuration data (stable)

**Not recommended for caching:**
- Real-time data (stock prices, live updates)
- User-specific volatile data (shopping carts)
- Frequently changing data (counters, logs)
- Large reports (use pagination instead)

## Troubleshooting

### Cache Not Working

**Symptoms:** No performance improvement, high database queries

**Solutions:**
1. Verify Redis is running: `docker-compose ps redis`
2. Check Redis connection: `php artisan tinker` then `Redis::ping()`
3. Verify cache driver: `echo $CACHE_DRIVER`
4. Check Redis logs: `docker-compose logs redis`

### High Cache Miss Rate

**Symptoms:** Hit ratio below 80%

**Solutions:**
1. Warm up cache: `POST /api/cache/warm-up`
2. Check TTL values (may be too short)
3. Verify cache keys are consistent
4. Review cache invalidation logic

### Stale Data Issues

**Symptoms:** Users see outdated information

**Solutions:**
1. Reduce TTL values for volatile data
2. Ensure `clearCache()` is called after updates
3. Implement cache tags for grouped invalidation
4. Use event-driven cache invalidation

### Redis Memory Issues

**Symptoms:** Redis OOM (out of memory) errors

**Solutions:**
1. Monitor Redis memory: `redis-cli info memory`
2. Set max memory in redis.conf: `maxmemory 256mb`
3. Configure eviction policy: `maxmemory-policy allkeys-lru`
4. Reduce cache TTL values
5. Clear old cache patterns regularly

## Security Considerations

### Cache Data Security

- **Never cache** sensitive data like passwords, tokens, or PII
- **Encrypt** cached sensitive data if required
- **Use** appropriate TTL for sensitive data
- **Clear** cache immediately after security incidents

### Cache Access Control

- All cache management endpoints require `Super Admin` role
- JWT authentication required for all cache operations
- Rate limiting applied to cache management API

### Session Security

- Session data stored in Redis with 120 minute TTL
- Sessions automatically invalidated on logout
- Session keys use random, unpredictable tokens

## Migration Guide

### From Database Sessions to Redis

If migrating from database to Redis sessions:

1. Update `.env`: `SESSION_DRIVER=redis`
2. Restart application: `php artisan start`
3. Existing sessions remain in database until expiration
4. New sessions use Redis automatically
5. Optional: Clear old database sessions

### Rollback

To rollback from Redis caching:

1. Update `.env`: `CACHE_DRIVER=array`
2. Update `.env`: `SESSION_DRIVER=database`
3. Restart application
4. Cache immediately disabled
5. Sessions use database again

## Monitoring and Alerts

### Key Metrics to Monitor

1. **Cache Hit Ratio** - Should be >80%
2. **Cache Miss Rate** - Should be <20%
3. **Response Time** - Should be <200ms (with cache)
4. **Redis Memory Usage** - Monitor for OOM
5. **Redis Connection Errors** - Monitor for connectivity issues

### Alert Thresholds

Configure alerts for:
- Cache hit ratio < 70% for >5 minutes
- Redis connection errors > 10/hour
- API response time > 300ms (with cache)
- Redis memory usage > 90%

## Additional Resources

- [Hyperf Cache Documentation](https://hyperf.wiki/3.0/en/cache)
- [Redis Documentation](https://redis.io/documentation)
- [Issue #224](https://github.com/sulhicmz/malnu-backend/issues/224)
