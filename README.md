# malnu-backend

Backend for Malnu Kananga - School Management System

## ⚠️ CRITICAL: Project Structure Warning

**This repository contains TWO separate applications. DEVELOPERS: Please read carefully.**

### Primary Application (ACTIVE - Use This One)
- **Location**: Root directory of this repository
- **Framework**: HyperVel (Laravel-style with Swoole support)
- **Status**: Actively maintained and developed
- **All new development should happen here**

### Legacy Application (DEPRECATED - Do Not Use)
- **Location**: `web-sch-12/` directory
- **Framework**: Laravel 12 with modular architecture
- **Status**: Deprecated, will be removed in future
- **No new development should occur here**

## Current Status

**⚠️ IMPORTANT**: All development efforts must focus on the main application in the root directory. The `web-sch-12` directory is maintained only for legacy purposes and will be completely removed in the future.

## Framework Information

### Main Application (HyperVel)
- Based on Hyperf framework with Swoole support
- Laravel-style syntax and conventions
- High-performance with native coroutine support
- Modern PHP architecture (PHP 8.2+)

### Legacy Application (Laravel) - DEPRECATED
- Standard Laravel 12 application
- Modular architecture using nwidart/laravel-modules
- Traditional synchronous processing
- **Will be removed - Do not develop here**

## Getting Started

For development, please focus ONLY on the main application:

1. Install dependencies: `composer install`
2. Set up environment: `cp .env.example .env` and configure
3. Run migrations: `php artisan migrate`
4. Start the server: `php artisan start`

## Documentation

- [Project Structure Details](PROJECT_STRUCTURE.md)
- [Application Status and Purpose](APPLICATION_STATUS.md)
- [Migration and Consolidation Plan](MIGRATION_PLAN.md)
- [Contribution Guidelines](CONTRIBUTING.md)
