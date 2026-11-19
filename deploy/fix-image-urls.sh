#!/usr/bin/env bash
# Rewrite absolute image URLs saved by the admin (e.g. http://localhost:8000/storage/...)
# into site-relative ones (/storage/...), so images work on any domain.
#
#   bash deploy/fix-image-urls.sh                       # default: http://localhost:8000
#   bash deploy/fix-image-urls.sh http://203.0.113.10   # after moving from IP to a domain
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/cloth}"
cd "$APP_DIR"

OLD="${1:-http://localhost:8000}"
OLD="${OLD%/}"
BS='\'
OLD_JSON="${OLD//"/"/"${BS}/"}"   # JSON-escaped form: http:\/\/host

envval() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
DB="$(envval DB_DATABASE)"
# Runs as root: MySQL on Ubuntu authenticates root over the local socket.
MYSQL=(mysql)

# table.column pairs that hold image URLs
COLUMNS=(
  products.featured_image products.gallery
  product_variants.image categories.image order_items.image
  brands.logo collections.image homepage_sections.content homepage_sections.settings
  social_gallery_items.image testimonials.avatar blog_posts.featured_image
  site_settings.value pages.content
)

# Escape a value for a single-quoted MySQL string literal: ' -> '' and \ -> \\
esc() { local s="$1"; s="${s//"'"/"''"}"; s="${s//"$BS"/"$BS$BS"}"; printf '%s' "$s"; }
SQL=""
for tc in "${COLUMNS[@]}"; do
  t="${tc%%.*}"; c="${tc##*.}"
  exists="$("${MYSQL[@]}" -N -e "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='${DB}' AND TABLE_NAME='${t}' AND COLUMN_NAME='${c}'")"
  [[ "$exists" == 1 ]] || continue
  SQL+="UPDATE \`${t}\` SET \`${c}\` = REPLACE(REPLACE(\`${c}\`, '$(esc "$OLD")', ''), '$(esc "$OLD_JSON")', '') WHERE \`${c}\` LIKE '%$(esc "${OLD#*://}")%';"$'\n'
done

if [[ -z "${DRY_RUN:-}" ]]; then
  "${MYSQL[@]}" "$DB" -e "$SQL"
else
  printf '%s' "$SQL"; exit 0
fi
php artisan cache:clear >/dev/null
echo "Rewrote image URLs starting with ${OLD} to relative /storage/... paths."
