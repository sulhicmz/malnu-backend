# Malnu Backend

> Backend for Malnu Kananga School Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-HyperVel-green.svg)](https://hyperf.wiki)

## 🎯 Overview

Malnu Backend is a comprehensive school management system built with **HyperVel** (Laravel-style framework with Hyperf/Swoole support). It provides high-performance API endpoints for student information management, academic records, administrative operations, and more.

## 🚀 Quick Start

### Standard Installation

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

### Using Docker Compose

```bash
# Build and start all services (app, mysql, postgres, redis)
docker-compose up -d

# View service status
docker-compose ps

# View logs
docker-compose logs -f app

# Stop all services
docker-compose down

# Stop services and remove volumes (deletes database data)
docker-compose down -v
```

#### Database Options

The Docker Compose setup includes three database options:

1. **MySQL (Default for Docker)** - Full-featured relational database
   ```bash
   # In .env (already configured for Docker)
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=malnu
   DB_USERNAME=malnu_user
   DB_PASSWORD=malnu_password_change_in_production
   ```

2. **PostgreSQL** - Advanced open-source database
   ```bash
   # In .env
   DB_CONNECTION=postgres
   DB_HOST=postgres
   DB_PORT=5432
   DB_DATABASE=malnu
   DB_USERNAME=malnu_user
   DB_PASSWORD=malnu_password_change_in_production
   ```

3. **SQLite** - Lightweight file-based database (development only)
   ```bash
   # To use SQLite instead of MySQL/PostgreSQL:
   # 1. Set DB_CONNECTION=sqlite
   # 2. Comment out all database host/port/user/password settings
   # 3. SQLite will use database/database.sqlite file in project root
   DB_CONNECTION=sqlite
   ```

**⚠️ Important**: Change default database passwords in `docker-compose.yml` and `.env` before deploying to production.

#### Testing Database Connectivity

After starting Docker services, you can verify database connectivity using the provided test script:

```bash
# Run database connectivity test
bash test-docker-databases.sh
```

The test script will:
- Verify Docker is running
- Check all services (app, mysql, postgres, redis) are running
- Test MySQL database connectivity and version
- Test Redis connectivity
- Validate `.env` configuration
- Check Docker volumes for data persistence

For detailed setup instructions, see [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md).

## 📚 Documentation

**📖 [Complete Documentation Index](docs/INDEX.md)** - Navigate all documentation from one place

### Getting Started
- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** - Comprehensive onboarding and setup
- **[Quick Start](docs/DEVELOPER_GUIDE.md#quick-start-new-to-the-project)** - Fast-track onboarding for new developers
- **[HyperVel Framework Guide](docs/HYPERVEL_FRAMEWORK_GUIDE.md)** - Understanding HyperVel concepts and patterns
- **[Business Domains Guide](docs/BUSINESS_DOMAINS_GUIDE.md)** - Overview of 11 business domains
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
