# Malnu Backend

Backend system for MA Malnu Kananga - A comprehensive school management system built with HyperVel/Laravel.

## Project Structure

This repository contains two main projects:

### 1. Main Backend (`/`)
- **Framework**: HyperVel (Laravel-style with coroutine support)
- **Purpose**: Primary backend API service
- **Location**: Root directory
- **Tech Stack**: PHP 8.2+, Swoole, Redis, SQLite/MySQL

### 2. Web School Module (`/web-sch-12/`)
- **Framework**: Laravel 12 with Modules
- **Purpose**: Modular web-based school management interface
- **Location**: `web-sch-12/` directory
- **Features**: User management, permissions, data tables

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm (for frontend assets)
- Redis
- MySQL/SQLite database

## Installation

### Main Backend
```bash
# Clone repository
git clone <repository-url>
cd malnu-backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
php artisan start
```

### Web School Module
```bash
# Navigate to module directory
cd web-sch-12

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## Development

### Code Quality
```bash
# Run code style checks
composer run cs-fix

# Run static analysis
composer run analyse

# Run tests
composer run test
```

### Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## Security

If you discover any security related issues, please read our [Security Policy](SECURITY.md) and report them responsibly.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- Create an issue in this repository
- Contact: maskom_team@ma-malnukananga.sch.id
