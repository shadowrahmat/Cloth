#!/usr/bin/env bash
# Pull the latest code and rebuild. Run on the server as root:
#   bash /var/www/cloth/deploy/update.sh
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/cloth}"
BRANCH="${BRANCH:-main}"
cd "$APP_DIR"

php artisan down --retry=15 || true
trap 'php artisan up' EXIT

git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit --no-fund
npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache
systemctl reload php8.4-fpm
systemctl restart cloth-queue

echo "Deployed $(git rev-parse --short HEAD)"
