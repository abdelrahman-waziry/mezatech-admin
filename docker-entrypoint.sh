#!/bin/bash
set -e

echo "Starting Laravel application..."

# Only cache if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    echo "Caching configuration..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start Apache
exec apache2-foreground
