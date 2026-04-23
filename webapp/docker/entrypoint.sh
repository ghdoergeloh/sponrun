#!/bin/sh
set -e

# Run post-install scripts now that APP_KEY is available
php artisan package:discover --ansi

# Create storage symlink (idempotent)
php artisan storage:link --force 2>/dev/null || true

# Cache config/routes/views for performance (only in non-local environments)
if [ "${APP_ENV:-production}" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
