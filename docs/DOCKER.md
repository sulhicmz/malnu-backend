# Docker Setup Guide

This guide provides comprehensive instructions for setting up and using Docker for the Malnu Backend application.

## Overview

The Malnu Backend uses Docker Compose to run the following services:

- **app**: Hyperf/HyperVel application with PHP 8.3 and Swoole
- **db**: MySQL 8.0 database (default) - enabled
- **postgres**: PostgreSQL 16 (alternative) - disabled by default
- **redis**: Redis 7 for caching and sessions

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB RAM available for Docker
- At least 10GB free disk space

## Quick Start

### 1. Start All Services

```bash
docker-compose up -d
```

This will start all enabled services in detached mode.

### 2. Verify Services are Running

```bash
docker-compose ps
```

You should see all services listed as "Up" or "healthy".

### 3. View Application Logs

```bash
# View all logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f redis
```

### 4. Access the Application

- **Application API**: http://localhost:9501
- **MySQL Database**: localhost:3306
- **Redis**: localhost:6379

## Database Configuration

### MySQL (Default)

MySQL is configured with the following default credentials:

- **Root Password**: `malnu_root_password_2024`
- **Database**: `malnu_backend`
- **User**: `malnu_user`
- **Password**: `malnu_password_2024`

**Security Notice**: Change these default passwords in production! Set them in your `.env` file:

```env
DB_ROOT_PASSWORD=your_secure_root_password
DB_DATABASE=malnu_backend
DB_USERNAME=malnu_user
DB_PASSWORD=your_secure_password
```

#### Connect to MySQL

```bash
# From within the container
docker-compose exec db mysql -u malnu_user -p malnu_backend

# From your host machine
mysql -h 127.0.0.1 -P 3306 -u malnu_user -p malnu_backend
```

### PostgreSQL (Alternative)

To use PostgreSQL instead of MySQL:

1. **Comment out MySQL service** in `docker-compose.yml`
2. **Uncomment PostgreSQL service** in `docker-compose.yml`
3. **Update `.env` configuration**:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=malnu_backend
DB_USERNAME=malnu_user
DB_PASSWORD=malnu_password_2024
```

4. **Restart services**:

```bash
docker-compose down
docker-compose up -d
```

#### Connect to PostgreSQL

```bash
# From within the container
docker-compose exec postgres psql -U malnu_user -d malnu_backend

# From your host machine
psql -h 127.0.0.1 -p 5432 -U malnu_user -d malnu_backend
```

## Redis Configuration

Redis is configured without authentication by default for development:

- **Host**: `redis` (within Docker network) or `localhost` (from host)
- **Port**: 6379
- **Password**: None

### Connect to Redis

```bash
# From within the container
docker-compose exec redis redis-cli

# From your host machine
redis-cli -h 127.0.0.1 -p 6379
```

## Docker Compose Commands

### Starting Services

```bash
# Start all services
docker-compose up -d

# Start specific services
docker-compose up -d db redis

# Start services with build (rebuild images)
docker-compose up -d --build
```

### Stopping Services

```bash
# Stop all services
docker-compose stop

# Stop specific services
docker-compose stop db

# Stop and remove containers
docker-compose down

# Stop and remove containers, volumes, and networks
docker-compose down -v
```

### Viewing Logs

```bash
# View all logs
docker-compose logs

# Follow logs in real-time
docker-compose logs -f

# View logs for specific service
docker-compose logs -f app

# View last 100 lines
docker-compose logs --tail=100
```

### Managing Services

```bash
# Restart a specific service
docker-compose restart app

# Rebuild a service
docker-compose up -d --build app

# Execute a command in a container
docker-compose exec app php artisan migrate

# Run a one-off command
docker-compose run --rm app php artisan db:seed
```

## Data Persistence

Docker volumes are used for data persistence:

- **dbdata**: MySQL database data
- **pgdata**: PostgreSQL database data
- **redisdata**: Redis data

### Backup Database Data

```bash
# Backup MySQL
docker-compose exec db mysqldump -u root -p malnu_backend > backup.sql

# Backup PostgreSQL
docker-compose exec postgres pg_dump -U malnu_user malnu_backend > backup.sql
```

### Restore Database Data

```bash
# Restore MySQL
docker-compose exec -T db mysql -u root -p malnu_backend < backup.sql

# Restore PostgreSQL
docker-compose exec -T postgres psql -U malnu_user malnu_backend < backup.sql
```

## Health Checks

All services include health checks to ensure they are running properly:

- **MySQL**: Checks if MySQL is accepting connections
- **PostgreSQL**: Checks if PostgreSQL is ready to accept connections
- **Redis**: Checks if Redis is responding to PING
- **App**: No health check (application-level monitoring required)

### Check Service Health

```bash
docker-compose ps
```

Look for the "healthy" status in the State column.

## Custom Configuration

### MySQL Configuration

Create a custom MySQL configuration file at `docker/mysql/my.cnf`:

```ini
[mysqld]
max_connections=200
innodb_buffer_pool_size=1G
default_authentication_plugin=mysql_native_password
```

### PostgreSQL Configuration

Create a custom PostgreSQL configuration file at `docker/postgres/postgresql.conf`:

```ini
max_connections = 200
shared_buffers = 256MB
effective_cache_size = 1GB
```

## Troubleshooting

### Database Connection Issues

If you can't connect to the database:

1. **Check if the service is running**:
   ```bash
   docker-compose ps db
   ```

2. **Check service logs**:
   ```bash
   docker-compose logs db
   ```

3. **Verify environment variables** in `.env`:
   ```env
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=malnu_backend
   DB_USERNAME=malnu_user
   DB_PASSWORD=malnu_password_2024
   ```

4. **Test connection from within the app container**:
   ```bash
   docker-compose exec app php -r "echo 'Database: ' . env('DB_HOST') . PHP_EOL;"
   ```

### Port Already in Use

If you see an error about ports being in use:

1. **Check what's using the port**:
   ```bash
   # Linux/macOS
   lsof -i :3306

   # Windows
   netstat -ano | findstr :3306
   ```

2. **Stop the conflicting service** or change the port mapping in `docker-compose.yml`:
   ```yaml
   ports:
     - "3307:3306"  # Use 3307 on host instead
   ```

### Out of Disk Space

If Docker is using too much disk space:

```bash
# Remove dangling images
docker image prune

# Remove unused volumes (WARNING: This deletes data!)
docker volume prune

# View disk usage
docker system df
```

### Container Won't Start

1. **Check logs for errors**:
   ```bash
   docker-compose logs app
   ```

2. **Rebuild the image**:
   ```bash
   docker-compose up -d --build app
   ```

3. **Remove old containers**:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

### Permission Issues (Linux)

If you encounter permission issues with volumes:

```bash
# Fix permissions for volume mounts
sudo chown -R $USER:$USER docker/
```

Or add the user to the docker group:

```bash
sudo usermod -aG docker $USER
# Log out and back in for changes to take effect
```

## Production Deployment

For production use, consider these security measures:

1. **Change all default passwords** in `.env`
2. **Use Docker secrets** for sensitive data
3. **Restrict network access** using Docker networks
4. **Enable HTTPS/TLS** for database connections
5. **Set up automated backups**
6. **Monitor resource usage**
7. **Use a reverse proxy** (e.g., Nginx) for the application
8. **Limit container resources**:

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '1.0'
          memory: 1G
```

## Advanced Usage

### Multi-Stage Builds

For smaller production images, use multi-stage builds in the Dockerfile.

### Docker Swarm/Kubernetes

For scaling, consider deploying to Docker Swarm or Kubernetes.

### CI/CD Integration

Integrate Docker Compose into your CI/CD pipeline for automated testing:

```bash
# In CI pipeline
docker-compose up -d db redis
docker-compose run --rm app php artisan test
docker-compose down
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [MySQL Docker Hub](https://hub.docker.com/_/mysql)
- [PostgreSQL Docker Hub](https://hub.docker.com/_/postgres)
- [Redis Docker Hub](https://hub.docker.com/_/redis)

## Support

If you encounter issues not covered in this guide:

1. Check the [GitHub Issues](https://github.com/sulhicmz/malnu-backend/issues)
2. Review the [Developer Guide](DEVELOPER_GUIDE.md)
3. Ask for help in the [Discussions](https://github.com/sulhicmz/malnu-backend/discussions)
