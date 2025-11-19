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

## Performance & Caching

This application implements Redis-based caching for improved performance:

- **Redis Configuration**: Redis is configured as the default cache and session driver
- **Query Caching**: Frequently accessed data is cached with TTL
- **Eager Loading**: N+1 query issues are prevented using eager loading
- **Database Indexes**: Optimized indexes on frequently queried columns
- **Performance Monitoring**: Query execution time logging for slow queries (>100ms)

### Cache Implementation
- User data is cached using the UserService
- Database indexes added to users, students, teachers, grades, and other frequently queried tables
- Cache TTL set to 1 hour (3600 seconds) for most data
- Cache invalidation strategies implemented

### API Endpoints with Caching
- `GET /api/users` - Get all users with cached results
- `GET /api/users/{id}` - Get specific user with caching
- `GET /api/users/role/{role}` - Get users by role with caching

## Documentation

- [Project Structure Details](PROJECT_STRUCTURE.md)
- [Application Status and Purpose](APPLICATION_STATUS.md)
- [Migration and Consolidation Plan](MIGRATION_PLAN.md)
- [Contribution Guidelines](CONTRIBUTING.md)
