#!/bin/sh
set -e

echo "=========================================="
echo "Malnu Backend - Development Environment"
echo "=========================================="

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
    echo "   Database not ready yet..."
    sleep 2
done
echo "✅ Database connection established"

# Check if vendor directory exists, if not install dependencies
if [ ! -d "/data/project/vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Check if .env file exists, if not create from example
if [ ! -f "/data/project/.env" ]; then
    echo "⚙️  Creating environment configuration..."
    cp /data/project/.env.example /data/project/.env
    php artisan key:generate
fi

# Check if database is already set up (has migrations table)
DB_SETUP=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -D"$DB_DATABASE" -e "SHOW TABLES LIKE 'migrations';" 2>/dev/null | grep -c "migrations" || true)

if [ "$DB_SETUP" = "0" ] || [ -z "$DB_SETUP" ]; then
    echo "🗄️  Setting up database..."
    php artisan migrate --force
    echo "🌱 Seeding database with test data..."
    php artisan db:seed --force
    echo "✅ Database initialized"
else
    echo "🔄 Running any pending migrations..."
    php artisan migrate --force
fi

echo "=========================================="
echo "🚀 Starting application..."
echo "=========================================="

# Execute the main command
exec "$@"
