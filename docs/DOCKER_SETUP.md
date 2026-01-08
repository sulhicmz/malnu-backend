# Docker Setup Guide

This guide provides comprehensive instructions for setting up and running the Malnu Backend application using Docker Compose.

## Prerequisites

- Docker (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- Git

## Quick Start

1. Clone the repository:
```bash
git clone https://github.com/sulhicmz/malnu-backend.git
cd malnu-backend
```

2. Copy environment configuration:
```bash
cp .env.example .env
```

3. Start the application:
```bash
docker-compose up -d
```

4. Run database migrations:
```bash
docker-compose exec app php artisan migrate
```

5. Access the application:
```
Application: http://localhost:9501
```

## Configuration

### Services

The Docker Compose configuration includes the following services:

#### App Service
- **Image**: hyperf/hyperf:8.3-alpine-v3.19-swoole-v6
- **Port**: 9501 (mapped to host)
- **Working Directory**: /data/project
- **Dependencies**: db, redis

#### MySQL Database Service
- **Image**: mysql:8.0
- **Port**: 3306 (mapped to host)
- **Database**: hypervel
- **User**: hypervel
- **Password**: secret (default - change in production)
- **Root Password**: root_password (default - change in production)
- **Health Check**: Automatically verified every 10 seconds

#### Redis Service
- **Image**: redis:7-alpine
- **Port**: 6379 (mapped to host)
- **Health Check**: Automatically verified every 10 seconds

### Environment Variables

Key environment variables to configure in `.env`:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=hypervel
DB_USERNAME=hypervel
DB_PASSWORD=secret

# Redis Configuration
REDIS_HOST=redis
REDIS_PORT=6379

# Session and Cache
SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=database
```

## Common Commands

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f redis
```

### Restart Services
```bash
docker-compose restart
```

### Execute Commands in Container
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Run tests
docker-compose exec app vendor/bin/phpunit

# Access shell
docker-compose exec app sh
```

### Database Operations
```bash
# Access MySQL shell
docker-compose exec db mysql -u hypervel -p hypervel

# Backup database
docker-compose exec db mysqldump -u hypervel -p hypervel > backup.sql

# Restore database
docker-compose exec -T db mysql -u hypervel -p hypervel < backup.sql
```

## Troubleshooting

### Port Conflicts

**Problem**: Port 3306 or 9501 is already in use on your system.

**Solution**:
- Modify port mappings in `docker-compose.yml`:
```yaml
ports:
  - "3307:3306"  # Use 3307 instead of 3306
```
- Update `.env` file if you change the MySQL port.

### Database Connection Issues

**Problem**: Application cannot connect to the database.

**Solutions**:
1. Ensure the database service is running:
```bash
docker-compose ps
```

2. Check database logs:
```bash
docker-compose logs db
```

3. Wait for database to be healthy:
```bash
docker-compose ps db  # Look for "healthy" status
```

4. Verify environment variables in `.env`:
   - `DB_HOST` should be `db` (Docker service name)
   - `DB_CONNECTION` should be `mysql`
   - Credentials should match `docker-compose.yml`

### Migration Errors

**Problem**: Migrations fail or table already exists.

**Solutions**:
1. Fresh start (delete all data):
```bash
docker-compose down -v
docker-compose up -d
docker-compose exec app php artisan migrate
```

2. Rollback and re-run:
```bash
docker-compose exec app php artisan migrate:rollback
docker-compose exec app php artisan migrate
```

### Permission Issues (SELinux)

**Problem**: File permission errors on SELinux-enabled systems.

**Solution**:
Add `:Z` or `:z` to volume mounts in `docker-compose.yml`:
```yaml
volumes:
  - .:/data/project:Z
```

Or run containers with elevated privileges:
```bash
docker-compose --compatibility up -d
```

### Health Check Failures

**Problem**: Database or Redis services are not becoming healthy.

**Solutions**:
1. Check if the service is running:
```bash
docker-compose ps
```

2. Inspect service logs:
```bash
docker-compose logs db
docker-compose logs redis
```

3. Restart the specific service:
```bash
docker-compose restart db
```

## Data Persistence

### Database Data
MySQL data is persisted in the `dbdata` Docker volume. To remove all data:
```bash
docker-compose down -v
```

### Redis Data
Redis data is persisted in the `redisdata` Docker volume. To remove all data:
```bash
docker-compose down -v
```

### Application Data
Application files are mounted from your local directory, so any changes you make are immediately reflected in the container.

## Production Considerations

### Security

1. **Change Default Passwords**:
   - Update `MYSQL_ROOT_PASSWORD` in `docker-compose.yml`
   - Update `MYSQL_PASSWORD` in `docker-compose.yml`
   - Update `DB_PASSWORD` in `.env`
   - Update `JWT_SECRET` in `.env` (generate with `openssl rand -hex 32`)

2. **Environment Variables**:
   - Never commit `.env` to version control
   - Use Docker secrets or environment variable files in production

3. **Network Security**:
   - Consider not exposing MySQL port (3306) in production
   - Use internal Docker networking only

### Performance

1. **Resource Limits**:
   Add resource limits to services in `docker-compose.yml`:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
```

2. **Caching**:
   - Ensure Redis is configured for sessions and cache
   - Consider using a dedicated Redis instance for production

3. **Database Optimization**:
   - Configure MySQL settings in `docker-compose.yml`:
```yaml
command: --default-authentication-plugin=mysql_native_password
```

### Monitoring

1. **Health Checks**:
   - Monitor service health with:
```bash
docker-compose ps
```

2. **Logs**:
   - Use centralized logging solution
   - Configure log rotation in Docker

### Backups

1. **Database Backups**:
```bash
# Automated backup script
docker-compose exec db mysqldump -u hypervel -p hypervel | gzip > backup-$(date +%Y%m%d).sql.gz
```

2. **Volume Backups**:
```bash
# Backup Docker volumes
docker run --rm -v malnu-backend_dbdata:/data -v $(pwd):/backup alpine tar czf /backup/dbdata-backup.tar.gz /data
```

## Development Workflow

### Hot Reload
The `app` service runs with `php artisan watch` command, which enables hot reload. Changes to PHP files are automatically detected and the server reloads.

### Running Tests
```bash
docker-compose exec app vendor/bin/phpunit
```

### Code Quality
```bash
docker-compose exec app composer cs-check
docker-compose exec app composer cs-fix
```

### Database Seeding
```bash
docker-compose exec app php artisan db:seed
```

## Advanced Topics

### Using SQLite Instead of MySQL

If you prefer to use SQLite instead of MySQL:

1. Update `.env`:
```env
DB_CONNECTION=sqlite
```

2. Comment out the database service in `docker-compose.yml`

3. Remove the database dependency from the app service:
```yaml
depends_on:
  - redis
```

### Custom MySQL Configuration

To use custom MySQL configuration:

1. Create `docker/mysql/my.cnf`:
```ini
[mysqld]
max_connections=200
innodb_buffer_pool_size=1G
```

2. Update `docker-compose.yml`:
```yaml
volumes:
  - dbdata:/var/lib/mysql
  - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
```

### Connecting to MySQL from Host

To connect to the MySQL database from your host machine:

```bash
mysql -h 127.0.0.1 -P 3306 -u hypervel -p hypervel
```

Or use a GUI tool like MySQL Workbench or DBeaver.

## Support

For issues or questions:
- Check the [main README](../README.md)
- Review [Developer Guide](DEVELOPER_GUIDE.md)
- Open an issue on GitHub

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Hyperf Documentation](https://hyperf.wiki/)
- [MySQL Docker Images](https://hub.docker.com/_/mysql)
