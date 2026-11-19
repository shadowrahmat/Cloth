#!/usr/bin/env bash
# -----------------------------------------------------------------------------
# One-time server setup for DRAWER CLOTHING on a fresh Ubuntu 22.04 / 24.04 VPS.
#
# Run as root:
#   REPO_URL=https://github.com/<user>/<repo>.git bash provision.sh
#
# Optional env vars:
#   BRANCH      git branch to deploy            (default: main)
#   APP_URL     public URL of the site          (default: http://<server IP>)
#   APP_DIR     install directory               (default: /var/www/cloth)
#   GIT_TOKEN   GitHub token, only for a private repository
#
# Safe to re-run: existing database, .env and checkout are kept.
# -----------------------------------------------------------------------------
set -euo pipefail

: "${REPO_URL:?Set REPO_URL, e.g. REPO_URL=https://github.com/user/repo.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/cloth}"
SERVER_IP="$(curl -4 -fsS https://api.ipify.org || hostname -I | awk '{print $1}')"
APP_URL="${APP_URL:-http://${SERVER_IP}}"
PHP_V="8.4"
DB_NAME="cloth"
DB_USER="cloth"
CREDS_FILE="/root/cloth-credentials.txt"

log() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }

if [[ $EUID -ne 0 ]]; then echo "Run as root (sudo -i)." >&2; exit 1; fi
export DEBIAN_FRONTEND=noninteractive

# --------------------------------------------------------------- packages
log "Installing system packages (nginx, PHP ${PHP_V}, MySQL, Node 22)"
apt-get update -y
apt-get install -y software-properties-common curl git unzip ufw ca-certificates gnupg
if ! grep -rq "ondrej/php" /etc/apt/sources.list.d/ 2>/dev/null; then
  add-apt-repository -y ppa:ondrej/php
fi
if ! command -v node >/dev/null || [[ "$(node -v | cut -d. -f1 | tr -d v)" -lt 22 ]]; then
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
fi
apt-get update -y
apt-get install -y nginx mysql-server nodejs \
  "php${PHP_V}-fpm" "php${PHP_V}-cli" "php${PHP_V}-mysql" "php${PHP_V}-mbstring" \
  "php${PHP_V}-xml" "php${PHP_V}-curl" "php${PHP_V}-zip" "php${PHP_V}-gd" \
  "php${PHP_V}-bcmath" "php${PHP_V}-intl" "php${PHP_V}-sqlite3"

if ! command -v composer >/dev/null; then
  log "Installing Composer"
  curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm /tmp/composer-setup.php
fi

# Uploads up to 20 MB (media library, bulk product images)
for ini in "/etc/php/${PHP_V}/fpm/php.ini" "/etc/php/${PHP_V}/cli/php.ini"; do
  sed -i 's/^upload_max_filesize.*/upload_max_filesize = 20M/; s/^post_max_size.*/post_max_size = 64M/; s/^memory_limit.*/memory_limit = 512M/' "$ini"
done

# ---------------------------------------------------------------- database
log "Configuring MySQL"
systemctl enable --now mysql
if [[ -f "$CREDS_FILE" ]] && grep -q '^DB_PASSWORD=' "$CREDS_FILE"; then
  DB_PASS="$(grep '^DB_PASSWORD=' "$CREDS_FILE" | cut -d= -f2-)"
else
  DB_PASS="$(openssl rand -hex 20)"
  echo "DB_PASSWORD=${DB_PASS}" > "$CREDS_FILE"
  chmod 600 "$CREDS_FILE"
fi
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# ------------------------------------------------------------------- code
log "Fetching application code into ${APP_DIR}"
CLONE_URL="$REPO_URL"
if [[ -n "${GIT_TOKEN:-}" ]]; then
  CLONE_URL="${REPO_URL/https:\/\//https://x-access-token:${GIT_TOKEN}@}"
fi
mkdir -p "$(dirname "$APP_DIR")"
if [[ -d "$APP_DIR/.git" ]]; then
  git -C "$APP_DIR" fetch origin "$BRANCH"
  git -C "$APP_DIR" checkout -B "$BRANCH" "origin/$BRANCH"
else
  git clone --branch "$BRANCH" "$CLONE_URL" "$APP_DIR"
  # Don't leave the token in .git/config
  git -C "$APP_DIR" remote set-url origin "$REPO_URL"
fi
git config --global --add safe.directory "$APP_DIR"

cd "$APP_DIR"

if [[ ! -f .env ]]; then
  log "Writing production .env"
  cp deploy/env.production.example .env
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|; s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env
  NEW_ENV=1
fi

log "Installing PHP and JS dependencies, building assets"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
[[ "${NEW_ENV:-0}" == 1 ]] && php artisan key:generate --force
npm ci --no-audit --no-fund
npm run build

log "Running migrations"
php artisan migrate --force
php artisan storage:link || true

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

php artisan optimize

# ------------------------------------------------------------------- nginx
log "Configuring nginx"
cat > /etc/nginx/sites-available/cloth <<NGINX
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root ${APP_DIR}/public;
    index index.php;
    charset utf-8;
    client_max_body_size 64M;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;

    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php${PHP_V}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* { deny all; }
    location ~ \.php$ { return 404; }
}
NGINX
ln -sf /etc/nginx/sites-available/cloth /etc/nginx/sites-enabled/cloth
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now "php${PHP_V}-fpm" nginx
systemctl reload "php${PHP_V}-fpm" nginx

# --------------------------------------------------- queue worker + scheduler
log "Setting up queue worker and scheduler"
cat > /etc/systemd/system/cloth-queue.service <<UNIT
[Unit]
Description=DRAWER CLOTHING queue worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable --now cloth-queue
systemctl restart cloth-queue

echo "* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/cloth-scheduler

# ---------------------------------------------------------------- firewall
log "Enabling firewall (SSH + HTTP + HTTPS)"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

log "Done. Site is live at ${APP_URL}"
echo "Database password is stored in ${CREDS_FILE}"
echo "Next: import your data and images (deploy/upload-data.ps1 from your PC)."
