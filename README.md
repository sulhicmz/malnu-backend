# malnu-backend

Backend for Malnu Kananga - School Management System

## Project Structure

This repository contains two applications due to historical development:

1. **Main Application** (root directory) - **PRIMARY**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts

2. **Legacy Application** (`web-sch-12/` directory) - **DEPRECATED**: Laravel 12 with modular architecture
   - Older implementation with fewer features
   - Under evaluation for deprecation
   - No new development should occur here

## Current Status

**⚠️ IMPORTANT**: All development efforts should focus on the main application in the root directory. The `web-sch-12` directory is maintained only for legacy purposes and will be deprecated in the future.

## Framework Information

### Main Application (HyperVel)
- Based on Hyperf framework with Swoole support
- Laravel-style syntax and conventions
- High-performance with native coroutine support
- Modern PHP architecture (PHP 8.2+)

### Legacy Application (Laravel)
- Standard Laravel 12 application
- Modular architecture using nwidart/laravel-modules
- Traditional synchronous processing

## Getting Started

For development, please focus on the main application:

1. Install dependencies: `composer install`
2. Set up environment: `cp .env.example .env` and configure
3. Run migrations: `php artisan migrate`
4. Start the server: `php artisan start`

## Documentation

- [Project Structure Details](PROJECT_STRUCTURE.md)
- [Application Status and Purpose](APPLICATION_STATUS.md)
- [Migration and Consolidation Plan](MIGRATION_PLAN.md)
- [Contribution Guidelines](CONTRIBUTING.md)

## Performance Optimization

This application implements several performance optimization strategies:

### Caching
- **Redis Integration**: Full Redis caching implementation for improved performance
- **Query Caching**: Frequently accessed database queries are cached
- **Service Layer Caching**: Computed results and expensive operations are cached
- **Cache Invalidation**: Proper cache invalidation strategies implemented

### Database Optimization
- **Index Strategy**: Added indexes to frequently queried columns
- **Query Analysis**: Optimized queries using eager loading to prevent N+1 problems
- **Connection Pooling**: Database connection pooling implemented
- **Query Optimization**: Use of query scopes and optimized relationship loading

### Available Commands
- `php artisan performance:monitor` - Show performance statistics
- `php artisan performance:monitor clear-cache` - Clear application cache
- `php artisan performance:monitor optimize` - Run performance optimizations

### API Endpoints for Performance
- `GET /api/users` - Get all users with caching
- `GET /api/users/{id}` - Get specific user with caching
- `GET /api/users/with-roles` - Get users with roles (optimized to prevent N+1)
- `GET /api/users/paginated-with-relationships` - Paginated users with relationships
- `POST /api/users/clear-cache` - Clear user-related caches
