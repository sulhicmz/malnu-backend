# malnu-backend

Backend for Malnu Kananga - School Management System

## Project Structure

This repository contains a single application:

1. **Main Application** (root directory) - **PRIMARY**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts

## Current Status

**✅ DEPRECATION COMPLETED ✅**: 

**The dual application structure has been resolved. ALL development efforts must focus on the main HyperVel application in the root directory. The `web-sch-12` directory has been completely removed.**

- **All development** should occur in the main HyperVel application
- **No legacy application** to maintain or support
- **Single codebase** for all school management features

## Migration Strategy

The migration has been completed:
1. **Phase 1**: ✅ Identified unique features in web-sch-12 (none required migration)
2. **Phase 2**: ✅ Main application already has comprehensive functionality
3. **Phase 3**: ✅ Removed web-sch-12 directory completely

## Immediate Actions Required

- Focus all development efforts on the main HyperVel application
- Update any integrations to use main application endpoints
- Update developer documentation and onboarding materials

## Framework Information

### Main Application (HyperVel)
- Based on Hyperf framework with Swoole support
- Laravel-style syntax and conventions
- High-performance with native coroutine support
- Modern PHP architecture (PHP 8.2+)



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
