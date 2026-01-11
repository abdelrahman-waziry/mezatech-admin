#!/bin/bash
set -e

echo "Starting deployment entrypoint..."

# Wait for database if needed (optional, basic check)
# sleep 5

# Cache configuration
if [ "$APP_ENV" == "production" ]; then
    echo "Caching configuration for production..."
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
