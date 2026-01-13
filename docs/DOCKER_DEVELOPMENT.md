# Docker Development Environment

This guide explains how to set up and use the Docker development environment for Malnu Backend.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+

## Quick Start

1. **Copy Docker environment configuration:**
   ```bash
   cp .env.docker.example .env
   ```

2. **Generate application key:**
   ```bash
   # Run this command inside the Docker container after starting
   php artisan key:generate
   ```

3. **Start all services:**
   ```bash
   docker compose up -d
   ```

4. **Run migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

5. **Access the application:**
   - API: http://localhost
   - Hyperf Server: http://localhost:9501

## Services

The Docker Compose configuration includes the following services:

| Service | Description | Ports |
|---------|-------------|-------|
| nginx | Web server (Nginx) | 80:80 |
| app | Hyperf PHP application | 9501:9501 |
| db | MySQL 8.0 database | 3306:3306 |
| redis | Redis cache/queue | 6379:6379 |

## Development Workflow

### Running Commands Inside Containers

Execute commands inside the app container:

```bash
# Run any artisan command
docker compose exec app php artisan <command>

# Example: Run tests
docker compose exec app php artisan test

# Example: Run migrations
docker compose exec app php artisan migrate

# Example: Clear cache
docker compose exec app php artisan cache:clear
```

### Viewing Logs

View logs for all services:
```bash
docker compose logs -f
```

View logs for a specific service:
```bash
docker compose logs -f app
docker compose logs -f db
docker compose logs -f redis
docker compose logs -f nginx
```

### Stopping Services

Stop all services:
```bash
docker compose down
```

Stop all services and remove volumes:
```bash
docker compose down -v
```

## Database Configuration

### MySQL Credentials

Default credentials (configured in `docker-compose.yml`):

- **Host**: `db` (Docker service name)
- **Port**: `3306`
- **Database**: `hyperf`
- **Username**: `hyperf`
- **Password**: `secret`

⚠️ **Important**: Change these credentials in `.env` file before using in production!

### Connecting to Database from Host

If you need to connect to the database from your host machine:

```bash
mysql -h 127.0.0.1 -P 3306 -u hyperf -p
# Password: secret
```

Or use a GUI tool like MySQL Workbench, DBeaver, or TablePlus:
- **Host**: 127.0.0.1
- **Port**: 3306
- **User**: hyperf
- **Password**: secret
- **Database**: hyperf

## Troubleshooting

### Container Won't Start

Check if ports are already in use:
```bash
# Check port 80
sudo lsof -i :80

# Check port 9501
sudo lsof -i :9501

# Check port 3306
sudo lsof -i :3306

# Check port 6379
sudo lsof -i :6379
```

### Permission Issues with Volume Mounts

If you encounter permission errors on Linux, you may need to adjust volume mounts in `docker-compose.yml`:

```yaml
volumes:
  - .:/data/project:Z  # Add :Z flag for Linux
```

Or run Docker with elevated privileges (not recommended):

```bash
sudo docker compose up -d
```

### Database Connection Errors

1. Verify database service is running:
   ```bash
   docker compose ps db
   ```

2. Check database logs:
   ```bash
   docker compose logs db
   ```

3. Wait for database to be healthy (check healthcheck status):
   ```bash
   docker compose ps
   ```

4. Verify `.env` file has correct database credentials:
   ```bash
   grep DB_ .env
   ```

### Application Not Accessible

1. Check all services are running:
   ```bash
   docker compose ps
   ```

2. Check nginx logs:
   ```bash
   docker compose logs nginx
   ```

3. Check app logs:
   ```bash
   docker compose logs app
   ```

### Rebuilding Containers

If you make changes to the Dockerfile or need to rebuild:

```bash
# Rebuild all containers
docker compose build --no-cache

# Rebuild specific service
docker compose build app
```

### Clearing Docker Cache

If you encounter build issues or want to start fresh:

```bash
# Stop and remove all containers, networks, and volumes
docker compose down -v

# Remove all images (use with caution)
docker rmi $(docker images -q)

# Rebuild and start
docker compose up -d --build
```

## Hot Reload

The development environment supports hot reloading:

- **PHP Files**: Changes are automatically detected by Hyperf watch mode
- **Configuration Changes**: May require container restart: `docker compose restart app`
- **Composer Dependencies**: Requires rebuild: `docker compose build app`
- **Database Changes**: Run migrations: `docker compose exec app php artisan migrate`

## Production Considerations

⚠️ **Important**: This Docker configuration is for development only. For production, consider:

1. Use production-optimized images (without development dependencies)
2. Use separate database servers (not in Docker)
3. Use Redis Cluster or managed Redis service
4. Use environment-specific configuration (not `.env.docker.example`)
5. Enable SSL/TLS for all communications
6. Use secrets management (Docker Secrets, environment-specific config providers)
7. Use health checks and restart policies for all services
8. Set resource limits and reservations
9. Use separate Docker Compose file for production (`docker-compose.prod.yml`)

## Common Commands

```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# View logs
docker compose logs -f

# Run artisan command
docker compose exec app php artisan <command>

# Access container shell
docker compose exec app sh

# Rebuild services
docker compose build

# Restart services
docker compose restart

# View service status
docker compose ps

# Remove all volumes (deletes database data!)
docker compose down -v
```

## Performance Tuning

### MySQL Performance

Edit `docker-compose.yml` to add MySQL performance tuning:

```yaml
db:
  image: mysql:8.0
  command: --default-authentication-plugin=mysql_native_password --innodb-buffer-pool-size=256M
```

### Redis Configuration

The Redis service is already optimized with Alpine Linux for smaller image size.

### Application Performance

Enable OPcache in the app Dockerfile (add to Dockerfile):

```dockerfile
RUN docker-php-ext-install opcache
```

## Security Notes

1. **Never commit** `.env` file with real credentials
2. **Change default passwords** in production
3. **Use secrets management** for production deployments
4. **Restrict database access** from host in production
5. **Keep Docker images updated** with security patches

## Additional Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Hyperf Documentation](https://hyperf.wiki/)
- [MySQL Docker Image](https://hub.docker.com/_/mysql/)
- [Redis Docker Image](https://hub.docker.com/_/redis/)
- [Nginx Documentation](https://nginx.org/en/docs/)
