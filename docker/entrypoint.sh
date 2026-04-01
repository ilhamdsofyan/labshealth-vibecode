#!/bin/sh
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link || true
php artisan package:discover --ansi || true

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan optimize:clear || true
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    php artisan optimize:clear || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || true
fi

exec "$@"
