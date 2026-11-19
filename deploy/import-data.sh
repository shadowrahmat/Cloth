#!/usr/bin/env bash
# Import the database dump and uploaded images sent by deploy/upload-data.ps1.
# Runs on the server as root:
#   bash /var/www/cloth/deploy/import-data.sh /tmp/cloth-db.sql /tmp/cloth-storage.tar.gz
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/cloth}"
DUMP="${1:?path to .sql dump}"
IMAGES="${2:-}"
cd "$APP_DIR"

envval() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
DB="$(envval DB_DATABASE)"

php artisan down --retry=15 || true
trap 'chown -R www-data:www-data storage bootstrap/cache; php artisan up' EXIT

echo "==> Replacing database '${DB}' with the uploaded dump"
# Runs as root: MySQL on Ubuntu authenticates root over the local socket.
mysql -e "DROP DATABASE IF EXISTS \`${DB}\`; CREATE DATABASE \`${DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql "$DB" < "$DUMP"
php artisan migrate --force   # apply any migrations newer than the dump

# Login tokens, sessions and cache from the development PC must not carry over.
for t in personal_access_tokens sessions cache cache_locks jobs failed_jobs; do
  mysql "$DB" -e "TRUNCATE TABLE \`${t}\`" 2>/dev/null || true
done

if [[ -n "$IMAGES" && -f "$IMAGES" ]]; then
  echo "==> Extracting uploaded images into storage/app/public"
  mkdir -p storage/app/public
  tar -xzf "$IMAGES" -C storage/app/public
fi
php artisan storage:link >/dev/null 2>&1 || true
chown -R www-data:www-data storage bootstrap/cache

echo "==> Making image URLs domain-independent"
bash deploy/fix-image-urls.sh http://localhost:8000
bash deploy/fix-image-urls.sh http://127.0.0.1:8000

echo "==> Rotating any account that still uses the demo password 'password'"
php artisan tinker --execute='
foreach (App\Models\User::all() as $u) {
    if (Illuminate\Support\Facades\Hash::check("password", $u->password)) {
        $new = Illuminate\Support\Str::password(16, symbols: false);
        $u->password = $new;
        $u->save();
        echo "  {$u->role}  {$u->email}  ->  {$new}\n";
        file_put_contents("/root/cloth-credentials.txt", "{$u->email}={$new}\n", FILE_APPEND);
    }
}'

php artisan optimize:clear >/dev/null
php artisan optimize >/dev/null
rm -f "$DUMP" "$IMAGES"
echo "==> Import complete. New passwords above are also saved in /root/cloth-credentials.txt"
