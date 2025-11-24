# malnu-backend

Backend for Malnu Kananga - School Management System

## Project Structure

This repository contains a single application:

1. **Main Application** (root directory) - **PRIMARY**: HyperVel framework (Laravel-style with Swoole support)
   - High-performance application with coroutine support
   - Comprehensive school management features
   - Current focus for all development efforts

## Current Status

The repository now contains a single application focused on the HyperVel framework. The legacy application has been removed to simplify the architecture and reduce maintenance overhead.

## Architecture Focus

The project now focuses entirely on the main HyperVel application:
1. All development efforts should be directed to the main application
2. The legacy application has been removed for architectural clarity

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
