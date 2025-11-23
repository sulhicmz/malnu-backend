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
   - **FULLY DEPRECATED** - No new development should occur here
   - **WILL BE REMOVED** in the next major release
   - Contains modules: ERaport, LaporanAnalitik, ManajemenSekolah, SistemMonetisasi

## Current Status

**⚠️ CRITICAL DEPRECATION NOTICE ⚠️**: 

**The dual application structure is being deprecated. ALL development efforts must focus on the main HyperVel application in the root directory. The `web-sch-12` directory will be completely removed in the next major release.**

- **No new features** should be added to the web-sch-12 application
- **No new development** should occur in the legacy application
- **All future work** should be done in the main HyperVel application
- **Migration plan** is in progress to consolidate functionality

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
