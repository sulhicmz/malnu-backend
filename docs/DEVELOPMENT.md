# Development Setup Guide

## 🚀 Quick Start

This guide will help you set up the Malnu Backend development environment on your local machine.

## 📋 Prerequisites

### Required Software
- **PHP 8.2+** - Application runtime
- **Composer** - PHP package manager
- **Redis** - Caching and session storage
- **SQLite** - Development database (included with PHP)
- **Git** - Version control

### Recommended Tools
- **VS Code** - Code editor
- **PHPStorm** - Advanced PHP IDE
- **Postman** - API testing
- **Docker** - Containerized development (optional)

## 🛠️ Installation Steps

### 1. Clone Repository
```bash
git clone https://github.com/sulhicmz/malnu-backend.git
cd malnu-backend
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
# Create SQLite database (default for development)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 5. Start Redis Server
```bash
# On macOS (using Homebrew)
brew services start redis

# On Ubuntu/Debian
sudo systemctl start redis-server

# On Windows (using WSL)
sudo service redis-server start
```

### 6. Start Development Server
```bash
php artisan start
```

The application will be available at `http://localhost:9501`

## 🔧 Configuration

### Environment Variables

Edit `.env` file to configure your environment:

```env
# Application
APP_NAME=MalnuBackend
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9501

# Database
DB_CONNECTION=sqlite
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=malnu_backend
# DB_USERNAME=root
# DB_PASSWORD=

# Cache
CACHE_DRIVER=redis

# Queue
QUEUE_CONNECTION=database

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0

# Security
JWT_SECRET=fc13c20fb40f1eb359bd83dfadd4efa1d8eb028db811cb7d980ebf0223da4e55
```

### Database Configuration

#### SQLite (Default - Development)
```env
DB_CONNECTION=sqlite
```

#### MySQL (Production)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malnu_backend
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Redis Configuration
```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

## 🧪 Testing

### Run Tests
```bash
# Run all tests
composer test

# Run PHPUnit directly
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/Feature/ExampleTest.php
```

### Test Database
Tests use a separate SQLite database automatically. No additional setup required.

### Code Quality Checks
```bash
# Run static analysis
composer analyse

# Run code style check
composer cs-diff

# Fix code style issues
composer cs-fix
```

## 📁 Project Structure

### Key Directories
- `app/` - Application code
- `config/` - Configuration files
- `database/` - Database migrations and seeders
- `routes/` - Route definitions
- `tests/` - Test files
- `docs/` - Documentation
- `frontend/` - React frontend code

### Model Organization
Models are organized by domain:
- `app/Models/SchoolManagement/` - Core school entities
- `app/Models/ELearning/` - Online learning features
- `app/Models/DigitalLibrary/` - Library management
- `app/Models/OnlineExam/` - Examination system

## 🔐 Development Security

### JWT Secret
Generate a secure JWT secret:
```bash
php artisan jwt:secret
# Or manually set in .env:
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
```

### Security Headers
Security headers are pre-configured in `.env.example`:
```env
SECURITY_HEADERS_ENABLED=true
CSP_ENABLED=true
HSTS_ENABLED=true
```

## 🚀 Common Development Tasks

### Creating New Model
```bash
# Create model with migration
php artisan make:model Student --migration

# Create model with factory and seeder
php artisan make:model Teacher --migration --factory --seeder
```

### Creating New Controller
```bash
# Create API controller
php artisan make:controller Api/StudentController --api

# Create resource controller
php artisan make:controller StudentController --resource
```

### Creating Migration
```bash
# Create new migration
php artisan make:migration create_new_table

# Create migration with model
php artisan make:migration create_students_table --create=students
```

### Running Migrations
```bash
# Run all pending migrations
php artisan migrate

# Fresh migration (drop all tables)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback
```

### Database Seeding
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## 🐛 Debugging

### Enable Debug Mode
```env
APP_DEBUG=true
```

### View Logs
```bash
# View application logs
tail -f storage/logs/laravel.log

# View recent logs
php artisan log:clear
```

### Database Queries
Enable query logging in `.env`:
```env
DB_LOG=true
```

## 📊 Performance Monitoring

### Application Performance
```bash
# View performance stats
php artisan performance:monitor

# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear
```

### Redis Monitoring
```bash
# Redis CLI
redis-cli

# Monitor Redis commands
redis-cli monitor

# View Redis info
redis-cli info
```

## 🔧 IDE Configuration

### VS Code
Recommended extensions:
- PHP Intelephense
- Laravel Blade Snippets
- GitLens
- SQLite Viewer

### PHPStorm
- Configure PHP interpreter to PHP 8.2+
- Enable Laravel plugin
- Configure database connection for SQLite

## 🐳 Docker Development (Optional)

### Docker Compose
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "9501:9501"
    volumes:
      - .:/var/www/html
    depends_on:
      - redis
      - mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: malnu_backend
    ports:
      - "3306:3306"
```

### Docker Commands
```bash
# Build and start containers
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop containers
docker-compose down
```

## 📝 Contributing

### Code Style
- Follow PSR-12 coding standards
- Use strict types declaration
- Add PHPDoc comments for public methods
- Run code style checks before committing

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/new-feature

# Commit changes
git add .
git commit -m "feat: add new feature"

# Push and create PR
git push origin feature/new-feature
```

### Pre-commit Checks
```bash
# Run all quality checks
composer test
composer analyse
composer cs-diff
```

## 🆘 Troubleshooting

### Common Issues

#### Permission Errors
```bash
# Fix storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### Redis Connection Failed
```bash
# Check Redis status
redis-cli ping

# Restart Redis
brew services restart redis  # macOS
sudo systemctl restart redis  # Linux
```

#### Migration Errors
```bash
# Reset database
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

#### Composer Issues
```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies
composer install --no-dev --optimize-autoloader
```

## 📚 Additional Resources

- [HyperVel Documentation](https://hypervel.com/docs)
- [Laravel Documentation](https://laravel.com/docs)
- [PHPStan Documentation](https://phpstan.org/)
- [PHPUnit Documentation](https://phpunit.de/)

---

*For additional help, create an issue in the GitHub repository.*