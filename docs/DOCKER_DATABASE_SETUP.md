# Docker Database Setup Guide

This guide provides detailed instructions for setting up and configuring the MySQL database service in Docker Compose for the Malnu Backend project.

## Overview

The project uses Docker Compose to manage database services. The MySQL database service is now enabled and configured in `docker-compose.yml` with:

- **Database Engine**: MySQL 8.0
- **Service Name**: `db`
- **Default Database**: `hypervel`
- **Default Port**: 3306 (mapped to host)

## Quick Start

### 1. Start Database Service

```bash
docker-compose up -d db redis
```

This starts both the MySQL database and Redis services in detached mode.

### 2. Verify Database is Running

```bash
docker-compose ps
```

You should see the `db` service status as "Up".

### 3. Check Database Logs

```bash
docker-compose logs db
```

### 4. Run Migrations

Once the database is running, apply migrations:

```bash
php artisan migrate
```

## Configuration

### Environment Variables

The following environment variables in `.env` control the database configuration:

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_CONNECTION` | `mysql` | Database connection type |
| `DB_HOST` | `db` | Docker service name (not localhost!) |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `hypervel` | Database name |
| `DB_USERNAME` | `hypervel` | Database user |
| `DB_PASSWORD` | `secret_change_in_production` | Database password |
| `DB_ROOT_PASSWORD` | `root_password_change_in_production` | MySQL root password |

### Important: DB_HOST Configuration

When using Docker Compose, **DO NOT** set `DB_HOST` to `localhost` or `127.0.0.1`. Use the service name `db` instead:

✅ **Correct** (Docker):
```env
DB_HOST=db
```

❌ **Incorrect** (will fail):
```env
DB_HOST=localhost
```

### Production Security

For production deployments, you must change the default passwords:

1. Generate secure passwords:
```bash
# Generate secure root password
openssl rand -base64 32

# Generate secure user password
openssl rand -base64 24
```

2. Update `.env` with the generated values:
```env
DB_PASSWORD=your_secure_user_password_here
DB_ROOT_PASSWORD=your_secure_root_password_here
```

3. **Never commit** passwords to version control
4. Add `.env` to `.gitignore` (already done)

## Database Initialization

### First-Time Setup

1. Ensure `.env` file exists:
```bash
cp .env.example .env
```

2. Start the database service:
```bash
docker-compose up -d db redis
```

3. Wait for MySQL to be ready (healthcheck will verify):
```bash
docker-compose logs -f db
# Look for "ready for connections" message
```

4. Run migrations:
```bash
php artisan migrate
```

5. Run seeders (optional):
```bash
php artisan db:seed
```

### Database Health Check

The MySQL service includes a health check that runs every 10 seconds:

```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
  interval: 10s
  timeout: 5s
  retries: 5
  start_period: 30s
```

The database is considered healthy after 30 seconds of startup time.

## Data Persistence

Database data is persisted in a Docker volume named `dbdata`:

```yaml
volumes:
  - dbdata:/var/lib/mysql
```

This ensures:
- Data survives container restarts
- Data persists even when containers are removed
- Easy backup and restore of database volume

### Backup Database Volume

```bash
# Stop the database
docker-compose stop db

# Backup the volume
docker run --rm -v malnu-backend_dbdata:/data -v $(pwd):/backup alpine tar czf /backup/db-backup-$(date +%Y%m%d).tar.gz -C /data .

# Start the database
docker-compose start db
```

### Restore Database Volume

```bash
# Stop the database
docker-compose stop db

# Restore the volume
docker run --rm -v malnu-backend_dbdata:/data -v $(pwd):/backup alpine sh -c "rm -rf /data/* && tar xzf /backup/db-backup-YYYYMMDD.tar.gz -C /data"

# Start the database
docker-compose start db
```

## Common Operations

### Connect to Database Container

```bash
docker-compose exec db bash
```

### Access MySQL CLI

```bash
docker-compose exec db mysql -u hypervel -p hypervel
```

Enter the password when prompted.

### Run SQL Commands Directly

```bash
docker-compose exec db mysql -u hypervel -p hypervel -e "SHOW TABLES;"
```

### View Database Logs

```bash
docker-compose logs -f db
```

### Restart Database Service

```bash
docker-compose restart db
```

### Stop Database Service

```bash
docker-compose stop db
```

### Remove Database Container (keeps data)

```bash
docker-compose rm -f db
```

### Remove Database Container and Volume (⚠️ DELETES DATA)

```bash
docker-compose down -v
```

## Troubleshooting

### Database Connection Errors

**Problem**: Application cannot connect to database

**Solutions**:
1. Verify `.env` has `DB_HOST=db` (not localhost)
2. Check database service is running: `docker-compose ps`
3. Check database logs: `docker-compose logs db`
4. Verify healthcheck passed: `docker-compose ps` (look for "healthy")

### Database Not Starting

**Problem**: Database container exits immediately

**Solutions**:
1. Check logs: `docker-compose logs db`
2. Verify port 3306 is not in use: `lsof -i :3306`
3. Check available disk space: `df -h`
4. Remove corrupted volume and start fresh:
```bash
docker-compose down -v dbdata
docker-compose up -d db
```

### Migration Errors

**Problem**: Migrations fail with connection errors

**Solutions**:
1. Verify database is healthy: `docker-compose ps`
2. Wait for database to fully start (30 seconds)
3. Check credentials in `.env`
4. Test connection manually:
```bash
docker-compose exec db mysql -u hypervel -p hypervel -e "SELECT 1;"
```

### Permission Issues

**Problem**: Database has permission errors on startup

**Solutions**:
1. Remove corrupted volume and recreate:
```bash
docker-compose down -v
docker-compose up -d db
```

2. Check SELinux/AppArmor if on Linux

## Network Configuration

### Default Network

Docker Compose creates a default network named `malnu-backend_default` (based on project directory). All services (app, db, redis) communicate over this network.

### Access Database from Host

To connect to the database from your host machine (e.g., using a GUI tool):

- **Host**: `localhost`
- **Port**: `3306`
- **User**: `hypervel` or `root`
- **Password**: As configured in `.env`

### Access Database from Another Container

From another Docker container on the same network:

- **Host**: `db` (service name)
- **Port**: `3306`

## Performance Tuning

### MySQL Configuration

For production, you can add a custom MySQL configuration file:

1. Create `docker/mysql/my.cnf`:
```ini
[mysqld]
max_connections=200
innodb_buffer_pool_size=256M
query_cache_size=32M
```

2. Update `docker-compose.yml`:
```yaml
db:
  volumes:
    - dbdata:/var/lib/mysql
    - ./docker/mysql:/etc/mysql/conf.d
```

### Connection Pooling

The application uses connection pooling (configured in `config/database.php`):

```php
'pool' => [
    'min_connections' => 1,
    'max_connections' => 10,
    'connect_timeout' => 10.0,
    'wait_timeout' => 3.0,
],
```

Adjust these values based on your load.

## Security Best Practices

1. **Always change default passwords** in production
2. **Never commit** `.env` file with credentials
3. **Use strong passwords** (use `openssl rand -base64 32`)
4. **Limit database access** to the application only
5. **Regular backups** of the database volume
6. **Monitor database logs** for suspicious activity
7. **Keep MySQL updated** (use latest 8.0.x)
8. **Enable SSL** for database connections in production

## Development vs Production

### Development
- Use default passwords (or simple ones)
- Expose port 3306 to host for debugging
- Enable query logging
- Use sqlite as fallback for quick tests

### Production
- Generate strong, unique passwords
- Consider using Docker secrets or external secrets manager
- Do not expose port 3306 to host (remove ports mapping)
- Enable binary logging for backups
- Use MySQL clustering for high availability
- Implement database backup strategy

## Additional Resources

- [MySQL Docker Hub](https://hub.docker.com/_/mysql)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Hyperf Database Documentation](https://hyperf.wiki/3.1/#/en/db)
- [Application Development Guide](../DEVELOPER_GUIDE.md)
