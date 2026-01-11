#!/bin/bash
set -e

echo "Starting deployment entrypoint..."

# Wait for database to be ready
echo "Waiting for database connection..."
max_tries=30
count=0
while [ $count -lt $max_tries ]; do
    if php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'Connected'; exit(0); } catch(Exception \$e) { exit(1); }" > /dev/null 2>&1; then
        echo "Database connection established."
        break
    fi
    echo "Waiting for database... ($count/$max_tries)"
    sleep 2
    count=$((count + 1))
done

if [ $count -ge $max_tries ]; then
    echo "Error: Could not connect to database after $max_tries attempts."
    exit 1
fi

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
