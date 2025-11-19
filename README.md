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

This application implements comprehensive performance optimization strategies:

### Redis Caching
- Configured Redis as the default cache driver
- Implemented repository-level caching for frequently accessed data
- Cache invalidation strategies for data consistency
- Cache hit ratio monitoring

### Database Optimization
- Added database indexes for frequently queried columns
- Eager loading implementation to prevent N+1 queries
- Query performance tracking and monitoring
- Slow query detection and logging

### API Endpoints
- `/api/users` - Cached user listing with eager loading
- `/api/users/{id}` - Cached individual user retrieval
- `/api/performance/report` - Performance metrics dashboard
- `/api/performance/cache` - Cache statistics
- `/api/performance/query` - Query performance metrics
- `/api/performance/reset` - Reset performance statistics

### Performance Targets
- Response Time: <200ms for API endpoints
- Database Queries: <100ms average execution time
- Cache Hit Ratio: >80%
- Support for 1000+ concurrent users
