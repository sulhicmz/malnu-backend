# Malnu Backend

> Backend for Malnu Kananga School Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-HyperVel-green.svg)](https://hyperf.wiki)

## 🎯 Overview

Malnu Backend is a comprehensive school management system built with **HyperVel** (Laravel-style framework with Hyperf/Swoole support). It provides high-performance API endpoints for student information management, academic records, administrative operations, and more.

## 🚀 Quick Start

### Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/sulhicmz/malnu-backend.git
cd malnu-backend

# Set up environment
cp .env.example .env
# Edit .env with your configuration (see Docker section below)

# Generate JWT secret (required for authentication)
php -r "echo bin2hex(random_bytes(32));" # Copy the output to JWT_SECRET in .env

# Start all services (MySQL, Redis, and application)
docker-compose up -d

# Run database migrations
docker-compose exec app php artisan migrate

# View logs
docker-compose logs -f app
```

The application will be available at `http://localhost:9501`

### Docker Configuration Notes

- **Database**: MySQL 8.0 on port 3306 (configured in `docker-compose.yml`)
- **Cache**: Redis 7 on port 6379
- **Environment**: `.env.example` uses Docker-compatible settings (DB_HOST=db, REDIS_HOST=redis)
- **Hot Reload**: Application auto-reloads on code changes

For detailed Docker setup and troubleshooting, see [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md).

### Local Development (Without Docker)

If you prefer local development without Docker:

```bash
# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env to use local database (change DB_CONNECTION to mysql, set DB_HOST=127.0.0.1)

# Generate JWT secret
php -r "echo bin2hex(random_bytes(32));" # Copy the output to JWT_SECRET in .env

# Start MySQL and Redis services (use docker-compose or local installations)
docker-compose up -d mysql redis

# Run migrations
php artisan migrate

# Start the server
php artisan start
```

## 📚 Documentation

**📖 [Complete Documentation Index](docs/INDEX.md)** - Navigate all documentation from one place

### Getting Started
- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** - Comprehensive onboarding and setup
- **[Project Structure](docs/PROJECT_STRUCTURE.md)** - Architecture and code organization
- **[Contributing](docs/CONTRIBUTING.md)** - Contribution guidelines

### Core Documentation
- **[Architecture](docs/ARCHITECTURE.md)** - System architecture and design patterns
- **[Database Schema](docs/DATABASE_SCHEMA.md)** - Database design and relationships
- **[API Documentation](docs/API.md)** - API endpoints and usage
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment instructions

### Status & Planning
- **[Application Status](docs/APPLICATION_STATUS.md)** - Current system status and health
- **[Roadmap](docs/ROADMAP.md)** - Development roadmap and milestones
- **[Task Management](docs/TASK_MANAGEMENT.md)** - Task breakdown and priorities

### Specialized Topics
- **[Security Analysis](docs/SECURITY_ANALYSIS.md)** - Security assessment and recommendations
- **[Calendar System](docs/CALENDAR_SYSTEM.md)** - Calendar and scheduling module
- **[Backup System](docs/BACKUP_SYSTEM.md)** - Backup and disaster recovery
- **[API Error Handling](docs/API_ERROR_HANDLING.md)** - Error handling patterns

## 🏗️ Technology Stack

- **Framework**: HyperVel (Hyperf + Swoole)
- **PHP**: 8.2+
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Frontend**: React + Vite

## 🔒 Key Features

- **Student Information System** - Academic records, grades, transcripts
- **Attendance Tracking** - Automated attendance management
- **Calendar & Events** - School calendar and scheduling
- **Assessment Management** - Exams, tests, and grading
- **Parent Portal** - Parent communication and updates
- **Financial Management** - Fee management and billing
- **E-Learning Platform** - Online learning resources

## 🔐 Security

We take security seriously. For information about:
- Security policy and vulnerability reporting: **[Security Policy](SECURITY.md)**
- Security assessment and current status: **[Security Analysis](docs/SECURITY_ANALYSIS.md)**

## 📊 Project Status

**Repository Health**: Improving (Critical issues being addressed)

See [Application Status](docs/APPLICATION_STATUS.md) for detailed information about:
- System health assessment
- Critical blockers and issues
- Development phases and progress

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](docs/CONTRIBUTING.md) for:
- Contribution guidelines
- Code style standards
- Pull request process

## 📝 License

See [LICENSE](LICENSE) file for details.

## 🔗 Links

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/sulhicmz/malnu-backend/issues)
- **Pull Requests**: [GitHub Pull Requests](https://github.com/sulhicmz/malnu-backend/pulls)
