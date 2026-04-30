#!/bin/sh

# Ensure storage directories exist
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage

# FORCE a fresh migration once to clear the stuck state
echo "Running FRESH database migrations..."
php artisan migrate:fresh --force --no-interaction

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
