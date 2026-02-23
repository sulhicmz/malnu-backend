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

# Set up environment (with automatic secret generation)
./scripts/setup-env.sh

# Or manually: Set up environment
cp .env.example .env
# Edit .env with your configuration

# Run migrations
php artisan migrate

# Start the server
php artisan start
```

### Using Make (Recommended)

This project includes a `Makefile` with unified developer commands. Run `make help` to see all available commands:

```bash
# View all available commands
make help

# Quick setup (install, env, migrate)
make setup

# Start Docker services
make up

# Run tests
make test

# Run all checks (lint, analyse, test)
make check
```

**Common commands:**
| Command | Description |
|---------|-------------|
| `make setup` | Complete project setup |
| `make up` / `make down` | Start/stop Docker services |
| `make test` | Run PHPUnit tests |
| `make lint` | Check code style |
| `make analyse` | Run PHPStan |
| `make check` | Run all quality checks |
| `make db-reset` | Reset and reseed database |

**Note**: The `setup-env.sh` script automatically generates secure random values for `APP_KEY` and `JWT_SECRET`. For production, always regenerate these secrets using:
```bash
openssl rand -base64 32  # For APP_KEY
openssl rand -hex 32     # For JWT_SECRET
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
