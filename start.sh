#!/bin/sh
set -e

echo "==> Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache || echo "  (view cache skipped)"

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} -t public public/index.php
