# ⚠️ malnu-backend - School Management System

## ⚠️ CRITICAL NOTICE: Dual Application Structure ⚠️

**READ THIS FIRST:** This repository contains TWO separate applications. 
**ALWAYS use the PRIMARY application in the root directory. NEVER develop in the `web-sch-12/` directory.**

---

## Project Structure

This repository contains two applications due to historical development:

1. **Main Application** (root directory) - **PRIMARY & ACTIVE**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts
   - ✅ **USE THIS FOR ALL DEVELOPMENT**

2. **Legacy Application** (`web-sch-12/` directory) - **DEPRECATED & INACTIVE**: Laravel 12 with modular architecture
   - Older implementation with fewer features
   - **NO LONGER MAINTAINED**
   - ❌ **DO NOT DEVELOP HERE**
   - Will be removed in future versions

## Current Status

**⚠️ IMPORTANT**: All development efforts should focus on the main application in the root directory. The `web-sch-12` directory is deprecated and maintained only for legacy purposes. **Any development in the `web-sch-12` directory will be ignored.**

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
