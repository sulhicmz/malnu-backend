# Dockerfile for Malnu Backend Development Environment
FROM hyperf/hyperf:8.3-alpine-v3.19-swoole-v6

# Set working directory
WORKDIR /data/project

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    oniguruma \
    oniguruma-dev \
    linux-headers

# Copy composer files for dependency installation
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /data/project \
    && chmod -R 755 storage bootstrap/cache

# Expose port
EXPOSE 9501

# Set default command
CMD ["php", "bin/hyperf.php", "start"]
