#!/bin/bash

# Test Docker Database Connectivity for Malnu Backend
# This script verifies that database services are properly configured

set -e

echo "=========================================="
echo "Docker Database Connectivity Test"
echo "=========================================="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ ERROR: Docker is not running. Please start Docker and try again."
    exit 1
fi

echo "✅ Docker is running"
echo ""

# Check if services are running
echo "Checking Docker services..."
if ! docker compose ps | grep -q "app"; then
    echo "❌ ERROR: App service is not running. Start services with: docker compose up -d"
    exit 1
fi

if ! docker compose ps | grep -q "mysql"; then
    echo "❌ ERROR: MySQL service is not running. Start services with: docker compose up -d"
    exit 1
fi

if ! docker compose ps | grep -q "redis"; then
    echo "❌ ERROR: Redis service is not running. Start services with: docker compose up -d"
    exit 1
fi

echo "✅ All services are running"
echo ""

# Test MySQL connectivity
echo "Testing MySQL connectivity..."
MYSQL_CONTAINER=$(docker compose ps -q mysql)
if docker compose exec -T mysql mysqladmin ping -h localhost -u root -proot_password_change_in_production > /dev/null 2>&1; then
    echo "✅ MySQL is responding"
    if docker compose exec -T mysql mysql -u malnu_user -pmalnu_password_change_in_production -e "SELECT 1" malnu > /dev/null 2>&1; then
        echo "✅ MySQL database 'malnu' is accessible"
    else
        echo "❌ ERROR: Cannot access MySQL database 'malnu'"
        exit 1
    fi
else
    echo "❌ ERROR: MySQL is not responding"
    exit 1
fi
echo ""

# Test Redis connectivity
echo "Testing Redis connectivity..."
REDIS_CONTAINER=$(docker compose ps -q redis)
if docker compose exec -T redis redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis is responding"
else
    echo "❌ ERROR: Redis is not responding"
    exit 1
fi
echo ""

# Test database configuration
echo "Checking .env configuration..."
if [ -f .env ]; then
    if grep -q "^DB_CONNECTION=mysql" .env; then
        echo "✅ DB_CONNECTION is set to mysql"
    elif grep -q "^DB_CONNECTION=postgres" .env; then
        echo "✅ DB_CONNECTION is set to postgres"
    elif grep -q "^DB_CONNECTION=sqlite" .env; then
        echo "⚠️  WARNING: DB_CONNECTION is set to sqlite"
        echo "   For Docker database services, use mysql or postgres instead"
    else
        echo "❌ ERROR: DB_CONNECTION is not set in .env"
        exit 1
    fi

    if grep -q "^DB_HOST=mysql" .env; then
        echo "✅ DB_HOST is set to mysql (Docker service name)"
    elif grep -q "^DB_HOST=postgres" .env; then
        echo "✅ DB_HOST is set to postgres (Docker service name)"
    elif grep -q "^DB_HOST=localhost" .env; then
        echo "⚠️  WARNING: DB_HOST is set to localhost"
        echo "   For Docker, use 'mysql' or 'postgres' as DB_HOST"
    fi

    if grep -q "^REDIS_HOST=redis" .env; then
        echo "✅ REDIS_HOST is set to redis (Docker service name)"
    else
        echo "⚠️  WARNING: REDIS_HOST should be set to 'redis' for Docker"
    fi
else
    echo "⚠️  WARNING: .env file not found. Copy from .env.example:"
    echo "   cp .env.example .env"
fi
echo ""

# Check volumes
echo "Checking Docker volumes..."
if docker volume ls | grep -q "malnu-backend_mysql_data"; then
    echo "✅ MySQL data volume exists"
else
    echo "⚠️  WARNING: MySQL data volume may not exist. It will be created when starting services."
fi

if docker volume ls | grep -q "malnu-backend_postgres_data"; then
    echo "✅ PostgreSQL data volume exists"
else
    echo "⚠️  WARNING: PostgreSQL data volume may not exist. It will be created when starting services."
fi

if docker volume ls | grep -q "malnu-backend_redis_data"; then
    echo "✅ Redis data volume exists"
else
    echo "⚠️  WARNING: Redis data volume may not exist. It will be created when starting services."
fi
echo ""

echo "=========================================="
echo "✅ All checks passed!"
echo ""
echo "Docker database services are properly configured."
echo ""
echo "Next steps:"
echo "1. Run database migrations: docker compose exec app php artisan migrate"
echo "2. Start the development server: docker compose exec app php artisan start"
echo "3. Access the application at: http://localhost:9501"
echo "=========================================="
