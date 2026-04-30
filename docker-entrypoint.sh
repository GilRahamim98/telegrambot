#!/bin/sh

# Run migrations automatically
php artisan migrate --force

# Start Apache in the foreground
exec apache2-foreground
