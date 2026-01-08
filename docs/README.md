# malnu-backend

Backend for Malnu Kananga - School Management System

## Project Structure

This repository contains a single application:

1. **Main Application** (root directory) - **PRIMARY**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts

## Current Status

**✅ APPLICATION CONSOLIDATION COMPLETED ✅**: 

**The dual application structure has been deprecated. ALL development efforts must focus on the main HyperVel application in the root directory. The legacy `web-sch-12` directory has been completely removed.**

- **All future work** should be done in the main HyperVel application
- **Single codebase** for improved maintainability
- **No legacy dependencies** to manage

## Framework Information

### Main Application (HyperVel)
- Based on Hyperf framework with Swoole support
- Laravel-style syntax and conventions
- High-performance with native coroutine support
- Modern PHP architecture (PHP 8.2+)

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

### Project Documentation
- [Project Structure Details](PROJECT_STRUCTURE.md)
- [Application Status and Purpose](APPLICATION_STATUS.md)
- [Migration and Consolidation Plan](MIGRATION_PLAN.md)
- [Contribution Guidelines](CONTRIBUTING.md)

### API Documentation
- [Interactive API Documentation](/api-docs.html) - Swagger UI with try-it-out functionality
- [OpenAPI Specification](api/openapi.yaml) - Complete API specification in YAML format
- [Developer Guide](api/developer-guide.md) - Comprehensive guide for API integration
- [API Overview](API.md) - Basic API information and endpoints

### Additional Resources
- [Architecture Documentation](ARCHITECTURE.md)
- [Database Schema](DATABASE_SCHEMA.md)
- [Development Guide](DEVELOPMENT.md)
- [Roadmap](ROADMAP.md)
