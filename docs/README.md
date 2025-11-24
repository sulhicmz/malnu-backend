# malnu-backend

Backend for Malnu Kananga - School Management System

## Project Structure

This repository contains a single application:

1. **Main Application** (root directory) - **PRIMARY**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts

## Current Status

The repository now contains a single application focused on the HyperVel framework. The legacy Laravel application has been completely removed to eliminate confusion and reduce maintenance overhead.

## Migration Strategy

A phased migration approach is being implemented:
1. **Phase 1**: Identify unique features in web-sch-12 that need migration
2. **Phase 2**: Implement equivalent functionality in main application
3. **Phase 3**: Remove web-sch-12 directory completely

## Immediate Actions Required

- Review any dependencies on the web-sch-12 application
- Plan migration of any unique functionality to the main application
- Update any integrations to use main application endpoints
- Update developer documentation and onboarding materials

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
