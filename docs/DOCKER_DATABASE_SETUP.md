# Docker Database Setup Guide

This guide explains how to set up and configure the database services when using Docker Compose.

## Overview

The Docker Compose configuration includes database services (MySQL and PostgreSQL) that can be used with the application. By default, MySQL 8.0 is configured and enabled.

## Quick Start

1. **Start all services** (including database):
   ```bash
   docker-compose up -d
   ```

2. **Run database migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

3. **Access the application**:
   - Application: http://localhost:9501
   - MySQL: localhost:3306
   - Redis: localhost:6379

## Database Options

### MySQL (Default)

MySQL 8.0 is configured as the default database in Docker Compose.

**Configuration** (.env):
```bash
DB_CONNECTION=mysql
DB_HOST=db              # Docker service name
DB_PORT=3306
DB_DATABASE=hypervel
DB_USERNAME=hyperf
DB_PASSWORD=secret      # Change this in production!
```

**Features**:
- Health check ensures database is ready before app starts
- Persistent data volume (dbdata)
- Automatic restart on failure
- UTF-8 character set (utf8mb4)

### PostgreSQL (Alternative)

PostgreSQL is available as an alternative to MySQL. To use PostgreSQL:

1. Comment out MySQL service in `docker-compose.yml`
2. Uncomment PostgreSQL service in `docker-compose.yml`
3. Update `.env` configuration:
   ```bash
   DB_CONNECTION=postgres
   DB_HOST=postgres        # Docker service name
   DB_PORT=5432
   DB_DATABASE=hypervel
   DB_USERNAME=hyperf
   DB_PASSWORD=secret      # Change this in production!
   ```

**Features**:
- Health check for readiness detection
- Persistent data volume (pgdata)
- Automatic restart on failure

### SQLite (Local Development Only)

For local development without Docker, you can use SQLite:

**Configuration** (.env):
```bash
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Note**: SQLite is not available in Docker Compose. Use MySQL or PostgreSQL when running with Docker.

## Security Configuration

### Production Deployment

Before deploying to production, change the default database credentials:

**In docker-compose.yml**:
```yaml
environment:
  MYSQL_ROOT_PASSWORD: your_secure_root_password_here
  MYSQL_DATABASE: hypervel
  MYSQL_USER: hyperf
  MYSQL_PASSWORD: your_secure_password_here
```

**In .env**:
```bash
DB_PASSWORD=your_secure_password_here
```

**Generate secure passwords**:
```bash
# Generate a 32-character random password
openssl rand -base64 32
```

### Port Conflicts

If you have MySQL running locally on port 3306:

**Option 1**: Stop local MySQL service
```bash
# On Linux/macOS
sudo systemctl stop mysql

# On macOS with Homebrew
brew services stop mysql
```

**Option 2**: Change Docker MySQL port mapping
```yaml
ports:
  - "3307:3306"  # Map to different host port
```

Then update `.env`:
```bash
DB_PORT=3307
```

## Data Persistence

Database data is persisted in Docker volumes:

- `dbdata`: MySQL data
- `pgdata`: PostgreSQL data
- `redisdata`: Redis data

**Backup volumes**:
```bash
# Backup MySQL data
docker run --rm -v malnu-backend_dbdata:/data -v $(pwd):/backup alpine tar czf /backup/mysql-backup.tar.gz /data

# Restore MySQL data
docker run --rm -v malnu-backend_dbdata:/data -v $(pwd):/backup alpine tar xzf /backup/mysql-backup.tar.gz -C /
```

## Database Initialization

### Run Migrations

After starting the database service, run migrations:

```bash
# Inside app container
docker-compose exec app php artisan migrate

# Or with fresh start (wipes existing data)
docker-compose exec app php artisan migrate:fresh --seed
```

### Create Database (Manual)

If automatic database creation fails:

```bash
# Access MySQL container
docker-compose exec db mysql -u root -p

# Create database
CREATE DATABASE hypervel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'hyperf'@'%' IDENTIFIED BY 'secret';

# Grant privileges
GRANT ALL PRIVILEGES ON hypervel.* TO 'hyperf'@'%';
FLUSH PRIVILEGES;
```

## Troubleshooting

### Database Connection Failed

**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solutions**:
1. Check database service is running: `docker-compose ps`
2. Wait for health check: `docker-compose logs db`
3. Check `.env` DB_HOST is set to `db` (Docker service name)
4. Verify DB_CONNECTION matches database type (mysql/postgres/sqlite)

### Health Check Failing

**Error**: Database health check fails repeatedly

**Solutions**:
1. Check database logs: `docker-compose logs db`
2. Verify credentials in `docker-compose.yml` match `.env`
3. Ensure enough resources allocated to Docker
4. Try removing volume and starting fresh:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ```

### Migration Errors

**Error**: Migration fails with SQL errors

**Solutions**:
1. Reset database: `docker-compose exec app php artisan migrate:fresh`
2. Check migration files for syntax errors
3. Verify database schema is compatible
4. Run specific migration: `docker-compose exec app php artisan migrate:rollback`

### Slow Database Performance

**Solutions**:
1. Check container resources: `docker stats`
2. Allocate more memory to Docker Desktop
3. Optimize MySQL configuration (my.cnf)
4. Use Redis for caching (already configured)

## Database Service Management

### Start Database Only

```bash
# Start database and Redis
docker-compose up -d db redis

# Start all services
docker-compose up -d
```

### Stop Database

```bash
# Stop database only
docker-compose stop db

# Stop all services
docker-compose down
```

### View Database Logs

```bash
# Follow database logs
docker-compose logs -f db

# View recent logs
docker-compose logs --tail=50 db
```

### Access Database Shell

```bash
# MySQL shell
docker-compose exec db mysql -u hyperf -p hypervel

# PostgreSQL shell
docker-compose exec postgres psql -U hyperf -d hypervel

# Or with root access
docker-compose exec db mysql -u root -p
```

## Advanced Configuration

### Custom MySQL Configuration

Create `docker/mysql/my.cnf`:
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 256M
query_cache_size = 32M
```

Mount in docker-compose.yml:
```yaml
volumes:
  - dbdata:/var/lib/mysql
  - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf:ro
```

### Connection Pooling

The application uses connection pooling configured in `config/database.php`:

```php
'pool' => [
    'min_connections' => 1,
    'max_connections' => 10,
    'connect_timeout' => 10.0,
    'wait_timeout' => 3.0,
    'heartbeat' => -1,
    'max_idle_time' => 60,
],
```

Adjust these values based on your workload.

## Migration from Other Databases

### From SQLite to MySQL

1. Export SQLite data:
   ```bash
   docker-compose exec app php artisan db:export sqlite-to-mysql
   ```

2. Switch configuration:
   - Change `DB_CONNECTION=mysql` in `.env`
   - Restart containers: `docker-compose restart app`

3. Import data:
   ```bash
   docker-compose exec app php artisan db:import mysql-from-sqlite
   ```

## Support

For issues not covered here:
- Check [Docker Compose Documentation](https://docs.docker.com/compose/)
- Review [MySQL Documentation](https://dev.mysql.com/doc/)
- Open an issue on GitHub

## See Also

- [Docker Setup](DOCKER.md) - General Docker setup guide
- [Database Schema](DATABASE_SCHEMA.md) - Database structure documentation
- [API Documentation](API.md) - API endpoints and usage
