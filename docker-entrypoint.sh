#!/bin/sh

# Ensure storage directories exist
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage

echo "Checking database connection..."
if php artisan db:monitor; then
    echo "Database connection successful."
else
    echo "Database connection failed! Check your credentials."
    exit 1
fi

echo "Running migrations..."
# We use --verbose to see the ACTUAL error if it fails
php artisan migrate:fresh --force --no-interaction --verbose || { echo "Migration failed!"; exit 1; }

echo "Starting Apache..."
exec apache2-foreground
