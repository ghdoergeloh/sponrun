#!/bin/sh
set -e

php artisan storage:link --force 2>/dev/null || true

if [ "${APP_ENV:-production}" != "local" ]; then
    php artisan config:cache
    php artisan route:cache
fi

exec "$@"
