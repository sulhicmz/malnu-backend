# Malnu Backend

> Backend for Malnu Kananga School Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-HyperVel-green.svg)](https://hyperf.wiki)

## 🎯 Overview

Malnu Backend is a comprehensive school management system built with **HyperVel** (Laravel-style framework with Hyperf/Swoole support). It provides high-performance API endpoints for student information management, academic records, administrative operations, and more.

## 🚀 Quick Start

### Standard Setup

```bash
# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env with your configuration

# Run migrations
php artisan migrate

# Start the server
php artisan start
```

### Docker Setup (Recommended for Development)

```bash
# Copy Docker environment configuration
cp .env.docker.example .env

# Start all services (nginx, app, mysql, redis)
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate

# Generate application key
docker compose exec app php artisan key:generate

# Access the application
# API: http://localhost
# Hyperf Server: http://localhost:9501
```

For detailed Docker instructions, see [DOCKER_DEVELOPMENT.md](docs/DOCKER_DEVELOPMENT.md).

For detailed setup instructions, see [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md).

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
 - **[Security Policy](SECURITY.md)** - Security policy, vulnerability reporting, and best practices
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
