#!/bin/bash
set -e

echo "Starting deployment entrypoint..."

# Wait for database to be ready
echo "Waiting for database connection..."
max_tries=60
count=0
while [ $count -lt $max_tries ]; do
    if php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'Connected'; exit(0); } catch(Exception \$e) { echo 'Connection failed: ' . \$e->getMessage() . PHP_EOL; exit(1); }" > /dev/null 2>&1; then
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
# if [ "$APP_ENV" == "production" ]; then
#     echo "Caching configuration for production..."
#    php artisan config:cache || true
#    php artisan event:cache || true
#    php artisan route:cache || true
#    php artisan view:cache || true
# fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
