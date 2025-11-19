# Performance Optimization Guide

This document outlines the performance optimizations implemented in the application.

## Redis Caching Implementation

### Configuration
- Redis is configured as the default cache driver
- Session storage uses Redis
- Queue system uses Redis

### Cache Strategy
- Query caching with TTL of 1 hour for frequently accessed data
- Eager loading to prevent N+1 queries
- Cache invalidation on data updates

### Available Cache Commands
```bash
# Clear all cache
php artisan cache:clear

# The cache system automatically handles:
- User data caching
- Role and permission caching  
- Frequently accessed model relationships
```

## Database Indexes

The following indexes have been added to optimize query performance:

### Users Table
- `idx_users_email`: Index on email column for authentication queries
- `idx_users_username`: Index on username column
- `idx_users_is_active`: Index on is_active column for filtering
- `idx_users_created_at`: Index on created_at for sorting

### Authentication Tables
- `idx_roles_name`: Index on role name
- `idx_permissions_name`: Index on permission name
- Composite indexes on model_has_roles and model_has_permissions tables

### Core Application Tables
- Indexes on foreign key relationships
- Indexes on frequently queried columns
- Indexes on date columns used for sorting

## Query Optimization

### Eager Loading
The application uses eager loading to prevent N+1 query problems:
- User relationships are loaded with `with(['student', 'teacher', 'parent', 'staff'])`
- Role relationships are properly loaded
- Model relationships are optimized

### Service Layer Caching
The UserService demonstrates proper caching patterns:
- `getAllUsers()`: Caches all users with relationships
- `getUserById()`: Caches individual users
- `getUserByEmail()`: Caches users by email
- `getUsersByRole()`: Caches users by role

## Performance Monitoring

### Cache Hit Ratio
Monitor cache performance by tracking hit/miss ratios.

### Query Performance
- Average query time should be <100ms
- Use query logging to identify slow queries
- Monitor for N+1 problems

## Best Practices

### For Developers
1. Always use eager loading when accessing relationships in loops
2. Cache expensive queries with appropriate TTL
3. Clear cache when updating data that affects cached results
4. Use database indexes for frequently queried columns
5. Monitor performance metrics regularly

### Cache Keys Naming Convention
- Format: `entity_type_operation_identifier`
- Example: `user_id_123`, `user_email_test@example.com`, `user_role_student`

This performance optimization ensures the application can handle high load scenarios and provides fast response times for users.