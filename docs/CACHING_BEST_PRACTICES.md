# Caching Best Practices

## Redis Configuration

The application is configured to use Redis as the default cache driver. Make sure your environment has Redis properly configured:

```env
CACHE_DRIVER=redis
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0
```

## Cache Implementation Guidelines

### 1. Use Appropriate TTL Values
- Short-lived data: 5-15 minutes (e.g., real-time statistics)
- Medium-lived data: 1-2 hours (e.g., user profiles, settings)
- Long-lived data: 24 hours (e.g., static reference data)

### 2. Cache Key Naming Convention
- Use descriptive, consistent naming: `module:entity:id` or `module:action:parameters`
- Examples:
  - `users:all` - All users list
  - `user:id:uuid` - Specific user by ID
  - `permissions:user:uuid` - Permissions for a specific user

### 3. Cache Invalidation Strategy
- Invalidate specific cache entries when data changes
- Use cache tags for related data invalidation
- Clear related caches when updating entities

### 4. Performance Monitoring
- Monitor cache hit ratios (aim for >80%)
- Track slow queries (aim for <100ms average)
- Use PerformanceMonitor utility to track metrics

## Query Optimization

### 1. Eager Loading
Always use eager loading to prevent N+1 queries:

```php
// Bad - N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->name; // N+1 query
}

// Good - Single query with eager loading
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->name; // No additional query
}
```

### 2. Database Indexes
The following indexes have been added for performance:
- `users.email` - For user authentication
- `users.is_active` - For filtering active users
- `users.created_at` - For sorting and filtering
- `users.username` - For username lookups
- `users.last_login_time` - For activity tracking

## Performance Monitoring

Use the PerformanceMonitor utility to track:
- Query execution times
- Cache hit/miss ratios
- Slow query identification
- Overall execution time

Example usage:
```php
PerformanceMonitor::startTimer();
// Your code here
$executionTime = PerformanceMonitor::getExecutionTime();
```

## Testing

All caching implementations should include tests to verify:
- Cache hit behavior
- Cache miss behavior
- Cache invalidation
- Performance improvements