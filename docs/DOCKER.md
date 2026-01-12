# Docker Setup Guide

This guide provides comprehensive instructions for setting up and running the Malnu Backend application using Docker Compose.

## Table of Contents

- [Quick Start](#quick-start)
- [Prerequisites](#prerequisites)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Database Configuration](#database-configuration)
- [Redis Configuration](#redis-configuration)
- [Docker Compose Commands](#docker-compose-commands)
- [Health Checks](#health-checks)
- [Data Persistence](#data-persistence)
- [Troubleshooting](#troubleshooting)
- [Production Deployment](#production-deployment)
- [Security Best Practices](#security-best-practices)

## Quick Start

```bash
# Clone the repository
git clone https://github.com/sulhicmz/malnu-backend.git
cd malnu-backend

# Copy environment configuration
cp .env.example .env

# Start all services
docker compose up -d

# Run database migrations
docker compose exec app php artisan migrate

# View logs
docker compose logs -f
```

The application will be available at `http://localhost:9501`

## Prerequisites

- Docker Engine 20.10 or later
- Docker Compose v2 (use `docker compose` instead of `docker-compose`)
- At least 2GB RAM available
- 10GB free disk space

## Configuration

### Environment Variables

Copy the `.env.example` file to `.env` and configure as needed:

```bash
cp .env.example .env
```

Key configuration options:

- **DB_CONNECTION**: Database type (`mysql`, `pgsql`, or `sqlite`)
- **DB_HOST**: Database host name (use `db` for Docker, `localhost` for local)
- **DB_DATABASE**: Database name
- **DB_USERNAME** and **DB_PASSWORD**: Database credentials
- **REDIS_HOST**: Redis host name (use `redis` for Docker, `localhost` for local)

### Docker Compose Configuration

The `docker-compose.yml` file defines the following services:

- **app**: Hyperf application server
- **db**: MySQL database (default) or PostgreSQL (optional)
- **redis**: Redis cache and session storage

## Running the Application

### Start Services

```bash
# Start all services in detached mode
docker compose up -d

# Start with log output
docker compose up
```

### Stop Services

```bash
# Stop all services
docker compose stop

# Stop and remove containers
docker compose down

# Stop and remove containers, volumes, and networks
docker compose down -v
```

### View Logs

```bash
# View all logs
docker compose logs

# View logs for a specific service
docker compose logs -f app
docker compose logs -f db
docker compose logs -f redis

# View last 100 lines
docker compose logs --tail=100
```

## Database Configuration

### MySQL (Default)

MySQL 8.0 is configured as the default database:

```yaml
db:
  image: mysql:8.0
  container_name: malnu_mysql
  ports:
    - "3306:3306"
  environment:
    MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-malnu_root_password_2024}
    MYSQL_DATABASE: ${DB_DATABASE:-malnu_backend}
    MYSQL_USER: ${DB_USERNAME:-malnu_user}
    MYSQL_PASSWORD: ${DB_PASSWORD:-malnu_password_2024}
```

#### Environment Variables

- `DB_CONNECTION=mysql`
- `DB_HOST=db`
- `DB_PORT=3306`
- `DB_DATABASE=malnu_backend`
- `DB_USERNAME=malnu_user`
- `DB_PASSWORD=malnu_password_2024`
- `DB_ROOT_PASSWORD=malnu_root_password_2024`

#### Database Commands

```bash
# Access MySQL container
docker compose exec db mysql -u malnu_user -pmalnu_password_2024 malnu_backend

# Access MySQL as root
docker compose exec db mysql -u root -pmalnu_root_password_2024

# Backup database
docker compose exec db mysqldump -u root -pmalnu_root_password_2024 malnu_backend > backup.sql

# Restore database
docker compose exec -T db mysql -u root -pmalnu_root_password_2024 malnu_backend < backup.sql
```

#### Custom MySQL Configuration

To customize MySQL settings:

1. Create a custom configuration file:
```bash
mkdir -p docker/mysql
cat > docker/mysql/my.cnf <<EOF
[mysqld]
max_connections=200
innodb_buffer_pool_size=512M
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
EOF
```

2. The configuration is automatically mounted to `/etc/mysql/conf.d/custom.cnf`

3. Restart the database service:
```bash
docker compose restart db
```

### PostgreSQL (Alternative)

To use PostgreSQL instead of MySQL:

1. Comment out the MySQL service in `docker-compose.yml`
2. Uncomment the PostgreSQL service
3. Update `.env`:

```bash
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=malnu_backend
DB_USERNAME=malnu_user
DB_PASSWORD=malnu_password_2024
```

4. Restart services:
```bash
docker compose down
docker compose up -d
```

#### PostgreSQL Commands

```bash
# Access PostgreSQL container
docker compose exec postgres psql -U malnu_user -d malnu_backend

# Backup database
docker compose exec postgres pg_dump -U malnu_user malnu_backend > backup.sql

# Restore database
docker compose exec -T postgres psql -U malnu_user -d malnu_backend < backup.sql
```

### SQLite (Local Development)

For local development without Docker database:

```bash
# Update .env
DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite

# Create database file
touch database/database.sqlite

# Run migrations
php artisan migrate
```

**Note**: When using SQLite, the database service can be removed from `docker-compose.yml`.

## Redis Configuration

Redis is configured for caching, sessions, and queues:

```yaml
redis:
  image: redis:7-alpine
  container_name: malnu_redis
  ports:
    - "6379:6379"
```

### Environment Variables

- `REDIS_HOST=redis` (Docker) or `REDIS_HOST=localhost` (local)
- `REDIS_PORT=6379`
- `REDIS_AUTH=` (no password by default)

### Redis Commands

```bash
# Access Redis CLI
docker compose exec redis redis-cli

# Test connection
docker compose exec redis redis-cli ping

# View all keys
docker compose exec redis redis-cli KEYS '*'

# Flush all data (CAUTION!)
docker compose exec redis redis-cli FLUSHALL
```

### Redis Commander (Optional)

To add a web-based Redis management interface, add this service to `docker-compose.yml`:

```yaml
redis-commander:
  image: rediscommander/redis-commander:latest
  container_name: malnu_redis_commander
  environment:
    - REDIS_HOSTS=local:redis:6379
  ports:
    - "8081:8081"
  depends_on:
    - redis
  networks:
    - malnu_network
```

Access at `http://localhost:8081`

## Docker Compose Commands

### Service Management

```bash
# List running containers
docker compose ps

# Restart a specific service
docker compose restart app

# Rebuild and start
docker compose up -d --build

# Remove stopped containers
docker compose rm -f
```

### Resource Monitoring

```bash
# View resource usage
docker compose top

# View container stats
docker stats

# Check container health
docker compose ps
```

### Database Migrations

```bash
# Run migrations
docker compose exec app php artisan migrate

# Rollback migrations
docker compose exec app php artisan migrate:rollback

# Fresh migration with seeding
docker compose exec app php artisan migrate:fresh --seed

# Check migration status
docker compose exec app php artisan migrate:status
```

### Composer and Artisan Commands

```bash
# Install dependencies
docker compose exec app composer install

# Update dependencies
docker compose exec app composer update

# Clear cache
docker compose exec app php artisan cache:clear

# Clear config cache
docker compose exec app php artisan config:clear

# Generate application key
docker compose exec app php artisan key:generate

# Queue worker
docker compose exec app php artisan queue:work
```

## Health Checks

All services include health checks to ensure they are running correctly:

### MySQL Health Check

```bash
# Check health status
docker compose ps

# Manual health check
docker compose exec db mysqladmin ping -h localhost -u root -pmalnu_root_password_2024
```

### Redis Health Check

```bash
# Manual health check
docker compose exec redis redis-cli ping
```

### Application Health Check

```bash
# Check if application is responding
curl http://localhost:9501/

# Check specific endpoint
curl http://localhost:9501/api/health
```

## Data Persistence

Docker volumes are used to persist data:

- `dbdata`: MySQL database files
- `pgdata`: PostgreSQL database files (if using PostgreSQL)
- `redisdata`: Redis data persistence

### Backup Data

```bash
# Backup MySQL
docker compose exec db mysqldump -u root -pmalnu_root_password_2024 malnu_backend > backup_$(date +%Y%m%d).sql

# Backup PostgreSQL
docker compose exec postgres pg_dump -U malnu_user malnu_backend > backup_$(date +%Y%m%d).sql

# Backup Redis
docker compose exec redis redis-cli SAVE
docker cp malnu_redis:/data/dump.rdb redis_backup_$(date +%Y%m%d).rdb

# Backup all volumes
docker run --rm -v malnu_backend_dbdata:/data -v $(pwd):/backup alpine tar czf /backup/dbdata_backup.tar.gz -C /data .
docker run --rm -v malnu_backend_redisdata:/data -v $(pwd):/backup alpine tar czf /backup/redisdata_backup.tar.gz -C /data .
```

### Restore Data

```bash
# Restore MySQL
docker compose exec -T db mysql -u root -pmalnu_root_password_2024 malnu_backend < backup.sql

# Restore PostgreSQL
docker compose exec -T postgres psql -U malnu_user -d malnu_backend < backup.sql

# Restore Redis
docker compose stop redis
docker cp redis_backup.rdb malnu_redis:/data/dump.rdb
docker compose start redis
```

## Troubleshooting

### Container Won't Start

```bash
# Check container logs
docker compose logs app
docker compose logs db
docker compose logs redis

# Check container status
docker compose ps

# Restart specific service
docker compose restart db
```

### Database Connection Issues

1. Check if database is ready:
```bash
docker compose exec db mysqladmin ping -h localhost -u root -pmalnu_root_password_2024
```

2. Check environment variables:
```bash
docker compose exec app env | grep DB_
```

3. Verify database service is running:
```bash
docker compose ps db
```

4. Check network connectivity:
```bash
docker compose exec app ping db
```

### Port Already in Use

If port 3306 (MySQL) or 6379 (Redis) is already in use on your host:

```bash
# Check what's using the port
sudo lsof -i :3306
sudo lsof -i :6379

# Option 1: Stop conflicting service
sudo systemctl stop mysql  # For local MySQL
sudo systemctl stop redis  # For local Redis

# Option 2: Change port mapping in docker-compose.yml
ports:
  - "3307:3306"  # Use host port 3307 instead of 3306
```

### Permission Issues

If you encounter permission issues with file mounts:

```bash
# Fix permissions for mounted volumes
sudo chown -R $USER:$USER .
sudo chmod -R 755 .

# Or run with elevated privileges (not recommended for production)
docker compose --privileged -u root up
```

### Out of Disk Space

```bash
# Check disk usage
docker system df

# Clean up unused resources
docker system prune -a

# Remove unused volumes
docker volume prune

# Clean up specific volume
docker volume rm malnu_backend_dbdata
```

### Application Not Responding

1. Check if Hyperf server is running:
```bash
docker compose logs app | grep "Hyperf"
```

2. Check if port is accessible:
```bash
curl http://localhost:9501/
```

3. Restart the application:
```bash
docker compose restart app
```

## Production Deployment

### Security Checklist

Before deploying to production:

- [ ] Change all default passwords
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong `APP_KEY` (generate with `php artisan key:generate`)
- [ ] Use strong `JWT_SECRET` (generate with `openssl rand -hex 32`)
- [ ] Configure HTTPS/SSL
- [ ] Set up proper firewall rules
- [ ] Enable rate limiting
- [ ] Configure CORS correctly
- [ ] Set up database backups
- [ ] Configure log rotation

### Environment Variables for Production

```bash
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generated-secure-key>

# Database
DB_CONNECTION=mysql
DB_HOST=<database-host>
DB_PORT=3306
DB_DATABASE=<database-name>
DB_USERNAME=<database-user>
DB_PASSWORD=<secure-password>
DB_ROOT_PASSWORD=<secure-root-password>

# Redis
REDIS_HOST=<redis-host>
REDIS_PORT=6379
REDIS_AUTH=<redis-password-if-configured>

# JWT
JWT_SECRET=<generated-secure-secret>
JWT_TTL=30
JWT_REFRESH_TTL=1440
JWT_BLACKLIST_ENABLED=true
```

### Using Docker in Production

For production deployment:

1. **Use Docker Swarm or Kubernetes** for orchestration
2. **Configure resource limits**:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
  db:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

3. **Use external services** for database and Redis (AWS RDS, ElastiCache, etc.)

4. **Configure secrets management** (Docker Secrets, HashiCorp Vault, AWS Secrets Manager)

5. **Set up monitoring** (Prometheus, Grafana, ELK Stack)

6. **Configure log aggregation** (CloudWatch, Loggly, ELK Stack)

## Security Best Practices

### Default Passwords

**WARNING**: The following default passwords are for development only. They MUST be changed in production:

- MySQL Root Password: `malnu_root_password_2024`
- MySQL User Password: `malnu_password_2024`
- PostgreSQL Password: `malnu_password_2024`

Generate secure passwords:
```bash
openssl rand -base64 32
```

### Secrets Management

Never commit secrets to version control. Use one of these approaches:

1. **Environment variables**: Load from `.env` file (ensure `.env` is in `.gitignore`)
2. **Docker Secrets**: For Docker Swarm
3. **External secret manager**: HashiCorp Vault, AWS Secrets Manager, Azure Key Vault

### Network Isolation

The application uses a dedicated Docker network (`malnu_network`) for inter-container communication. This provides network isolation and prevents unauthorized access.

### Volume Security

- Ensure proper file permissions on mounted volumes
- Use read-only mounts where possible (e.g., `:ro` suffix)
- Encrypt volumes at rest (Docker supports encryption plugins)

### Container Security

- Use minimal Alpine images (already configured)
- Keep images updated: `docker compose pull`
- Scan images for vulnerabilities: `docker scan`
- Run containers as non-root users (where supported)
- Limit container capabilities

## Additional Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Hyperf Documentation](https://hyperf.wiki/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Redis Documentation](https://redis.io/documentation)
- [Docker Security Best Practices](https://docs.docker.com/engine/security/)

## Support

For issues or questions:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review Docker logs: `docker compose logs`
3. Check the [main documentation](README.md)
4. Open an issue on [GitHub](https://github.com/sulhicmz/malnu-backend/issues)
