# Deployment Guide

This guide provides step-by-step instructions for deploying Malnu Backend School Management System to production.

## Prerequisites

### Server Requirements

- **Operating System:** Ubuntu 22.04 LTS or newer
- **CPU:** 2+ cores
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 50GB SSD minimum
- **Network:** 100 Mbps+ connection

### Software Requirements

- **PHP 8.2+** with extensions:
  - swoole
  - redis
  - pdo_mysql
  - mbstring
  - json
  - openssl
  - ctype
  - tokenizer
  - xml

- **Nginx 1.20+**
- **MySQL 8.0+**
- **Redis 7.0+**
- **Composer 2.x**
- **Node.js 18+** (for frontend build)
- **Docker & Docker Compose** (optional, for containerized deployment)
- **Git**
- **SSL Certificate** (Let's Encrypt or commercial)

## Deployment Options

### Option 1: Traditional Deployment (Recommended for Production)

#### 1. Server Setup

##### Update System

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common
```

##### Install Nginx

```bash
sudo apt install -y nginx
sudo ufw allow 'Nginx HTTP'
sudo ufw allow 'Nginx HTTPS'
```

##### Install PHP 8.2

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-json php8.2-opcache php8.2-ctype
```

##### Install Swoole Extension

```bash
sudo apt install -y php8.2-dev php-pear
sudo pecl install swoole
echo "extension=swoole.so" | sudo tee -a /etc/php/8.2/mods-available/swoole.ini
sudo phpenmod swoole
```

##### Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

##### Install Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

##### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

##### Install Node.js & npm

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

#### 2. Application Setup

##### Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/sulhicmz/malnu-backend.git
sudo chown -R www-data:www-data malnu-backend
cd malnu-backend
```

##### Install Dependencies

```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
cd frontend
sudo -u www-data npm install
npm run build
cd ..
```

##### Configure Environment

```bash
cp .env.example .env
sudo nano .env
```

Edit `.env` with production values:

```env
APP_NAME="Malnu Backend"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malnu_production
DB_USERNAME=malnu_user
DB_PASSWORD=your-secure-password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# CRITICAL: Generate secure JWT secret
JWT_SECRET=your-very-secure-jwt-secret-key-generate-with-php-artisan-jwt-secret

# Swoole Configuration
SWOOLE_HTTP_PORT=9501
```

##### Generate Application Key

```bash
php artisan key:generate
```

##### Generate JWT Secret

```bash
php artisan jwt:secret
```

#### 3. Database Setup

##### Create Database and User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE malnu_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'malnu_user'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON malnu_production.* TO 'malnu_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

##### Run Migrations

```bash
php artisan migrate --force
```

##### Run Seeders (if needed)

```bash
php artisan db:seed --force
```

#### 4. Nginx Configuration

##### Create SSL Certificate (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

##### Configure Nginx

Create configuration file:

```bash
sudo nano /etc/nginx/sites-available/malnu-backend
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/malnu-backend/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging
    access_log /var/log/nginx/malnu-access.log;
    error_log /var/log/nginx/malnu-error.log;

    # Proxy to Swoole
    location / {
        proxy_pass http://127.0.0.1:9501;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # WebSocket support
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    # Frontend static files
    location /assets/ {
        alias /var/www/malnu-backend/frontend/dist/assets/;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM fallback (for Swoole not running)
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/malnu-backend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 5. Swoole Service Setup

##### Create Systemd Service

```bash
sudo nano /etc/systemd/system/malnu-backend.service
```

```ini
[Unit]
Description=Malnu Backend Swoole Server
After=network.target mysql.service redis.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/malnu-backend
ExecStart=/usr/bin/php artisan start
Restart=always
RestartSec=10
StandardOutput=append:/var/log/malnu-backend/swoole.log
StandardError=append:/var/log/malnu-backend/swoole-error.log

[Install]
WantedBy=multi-user.target
```

##### Create Log Directory

```bash
sudo mkdir -p /var/log/malnu-backend
sudo chown -R www-data:www-data /var/log/malnu-backend
```

##### Enable and Start Service

```bash
sudo systemctl daemon-reload
sudo systemctl enable malnu-backend
sudo systemctl start malnu-backend
```

Check status:

```bash
sudo systemctl status malnu-backend
```

#### 6. Worker Processes Setup (Optional)

For background jobs and queues:

```bash
sudo nano /etc/systemd/system/malnu-worker.service
```

```ini
[Unit]
Description=Malnu Backend Queue Worker
After=network.target redis.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/malnu-backend
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable malnu-worker
sudo systemctl start malnu-worker
```

#### 7. Monitoring and Logging

##### Configure Application Logging

Edit `config/logging.php`:

```php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => '/var/log/malnu-backend/laravel.log',
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

##### Configure Log Rotation

```bash
sudo nano /etc/logrotate.d/malnu-backend
```

```
/var/log/malnu-backend/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

#### 8. Backup Setup

##### Automated Database Backups

Create backup script:

```bash
sudo nano /var/www/malnu-backend/scripts/backup.sh
```

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/malnu-backend"
DB_NAME="malnu_production"
DB_USER="malnu_user"
DB_PASS="your-secure-password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Backup storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/malnu-backend/storage/app

# Remove backups older than 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
sudo chmod +x /var/www/malnu-backend/scripts/backup.sh
```

##### Schedule Cron Job

```bash
sudo crontab -e
```

```
# Database backup daily at 2 AM
0 2 * * * /var/www/malnu-backend/scripts/backup.sh

# Clear cache daily at 3 AM
0 3 * * * php /var/www/malnu-backend/artisan cache:clear

# Optimize database weekly on Sunday
0 4 * * 0 php /var/www/malnu-backend/artisan db:optimize
```

#### 9. Security Hardening

##### Configure Firewall

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

##### Set File Permissions

```bash
sudo chown -R www-data:www-data /var/www/malnu-backend
sudo find /var/www/malnu-backend -type f -exec chmod 644 {} \;
sudo find /var/www/malnu-backend -type d -exec chmod 755 {} \;
```

##### Configure Fail2Ban

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### Option 2: Docker Deployment

For containerized deployment, use the existing `docker-compose.yml` with production configurations.

#### Build and Deploy

```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Build frontend
docker-compose exec frontend npm run build
```

#### Docker Compose Production File

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    restart: always
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - app

  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    restart: always
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data

volumes:
  db_data:
  redis_data:
```

## Post-Deployment Checklist

### Application Verification

- [ ] Application loads correctly at https://your-domain.com
- [ ] Database migrations completed successfully
- [ ] All API endpoints respond correctly
- [ ] Authentication works (login, logout, registration)
- [ ] Frontend loads without errors
- [ ] Static assets load correctly
- [ ] WebSocket connections work (if applicable)

### Security Verification

- [ ] SSL certificate valid and properly installed
- [ ] Security headers present (check with securityheaders.com)
- [ ] Firewall configured
- [ ] Fail2Ban running
- [ ] File permissions correct
- [ ] JWT secrets generated and secure
- [ ] Database credentials secure

### Monitoring Setup

- [ ] Application logging configured
- [ ] Error monitoring configured (Sentry, Rollbar, etc.)
- [ ] Uptime monitoring configured
- [ ] Performance monitoring configured
- [ ] Database monitoring configured
- [ ] Backup system configured and tested
- [ ] Alert notifications configured

### Performance Verification

- [ ] Application response time <200ms (P95)
- [ ] Page load time <2 seconds
- [ ] Database queries optimized
- [ ] Caching working (Redis)
- [ ] CDN configured (if applicable)

## Maintenance

### Regular Tasks

**Daily:**
- Check application logs
- Monitor error rates
- Check disk space

**Weekly:**
- Review security logs
- Check backup status
- Review performance metrics

**Monthly:**
- Update dependencies (composer, npm)
- Review and update SSL certificates
- Optimize database
- Review and update firewall rules

**Quarterly:**
- Security audit
- Disaster recovery test
- Performance review
- Capacity planning

### Updates and Patches

**Application Updates:**

```bash
cd /var/www/malnu-backend
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
php artisan migrate --force
sudo systemctl restart malnu-backend
```

**System Updates:**

```bash
sudo apt update
sudo apt upgrade -y
```

## Troubleshooting

### Application Not Starting

Check service status:

```bash
sudo systemctl status malnu-backend
```

Check logs:

```bash
sudo journalctl -u malnu-backend -f
```

Check Swoole port:

```bash
sudo netstat -tulpn | grep 9501
```

### Database Connection Issues

Test MySQL connection:

```bash
mysql -u malnu_user -p malnu_production
```

Check MySQL service:

```bash
sudo systemctl status mysql
```

### Redis Connection Issues

Test Redis connection:

```bash
redis-cli ping
```

Check Redis service:

```bash
sudo systemctl status redis-server
```

### High Memory Usage

Check memory usage:

```bash
free -h
```

Check Swoole configuration in `.env` and adjust worker counts.

### Slow Performance

Enable query logging:

```env
DB_LOG_QUERIES=true
```

Analyze slow query log:

```bash
sudo tail -f /var/log/mysql/slow-query.log
```

## Rollback Procedure

If deployment causes issues:

```bash
# Stop service
sudo systemctl stop malnu-backend

# Rollback code
cd /var/www/malnu-backend
git log --oneline -10
git revert <commit-hash>

# Rollback database
php artisan migrate:rollback

# Restore backup if needed
gunzip < /var/backups/malnu-backend/db_backup_YYYYMMDD.sql.gz | mysql -u malnu_user -p malnu_production

# Restart service
sudo systemctl start malnu-backend
```

## Disaster Recovery

### Server Failure

1. **Spin up new server** with same specifications
2. **Restore database** from latest backup
3. **Deploy application** using this guide
4. **Update DNS** to point to new server
5. **Restore SSL certificates**

### Data Loss

1. **Identify point of failure**
2. **Stop application** to prevent further data loss
3. **Restore from backup** using rollback procedure
4. **Verify data integrity**
5. **Resume operations**

---

*Last Updated: January 8, 2026*
