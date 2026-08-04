#!/bin/bash
# After Baileys 7 upgrade: keep working orgs, force fresh QR for everyone else.
# Default keeps Surabhi (org 1). Override: KEEP_ORG_IDS=1,5 bash scripts/migrate-orgs-baileys7.sh
set -euo pipefail

APP_DIR=/var/www/connectpulse
KEEP_ORG_IDS="${KEEP_ORG_IDS:-1}"
cd "$APP_DIR"

git config --global --add safe.directory "$APP_DIR" || true
git checkout -- whatsapp-bridge/ecosystem.config.cjs whatsapp-bridge/package.json \
  whatsapp-bridge/package-lock.json 2>/dev/null || true
git pull origin master

SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")
if [ -z "$SECRET" ] || [ "$SECRET" = "change-me-in-production" ]; then
  SECRET=$(openssl rand -hex 32)
  if grep -q '^WHATSAPP_BRIDGE_SECRET=' .env; then
    sed -i "s|^WHATSAPP_BRIDGE_SECRET=.*|WHATSAPP_BRIDGE_SECRET=${SECRET}|" .env
  else
    echo "WHATSAPP_BRIDGE_SECRET=${SECRET}" >> .env
  fi
fi
sed -i "s|BRIDGE_SECRET: '.*'|BRIDGE_SECRET: '${SECRET}'|" whatsapp-bridge/ecosystem.config.cjs

echo "==> Install Baileys 7 bridge + restart PM2 (fork)"
cd whatsapp-bridge
rm -rf node_modules
npm install --omit=dev
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"
php artisan config:cache

echo "==> Waiting for bridge"
for i in $(seq 1 30); do
  if curl -sf -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/health" >/dev/null; then
    echo "    healthy after ${i}s"
    break
  fi
  sleep 1
  if [ "$i" -eq 30 ]; then
    echo "ERROR: bridge not healthy"
    pm2 logs connectpulse-bridge --lines 40 --nostream || true
    exit 1
  fi
done

# Give kept orgs time to restore from creds.json
echo "==> Allowing kept sessions to restore..."
sleep 8

KEEP_CSV=$(echo "$KEEP_ORG_IDS" | tr -d ' ')
echo "==> Organizations"
php scripts/list-organizations.php || true
echo "==> Keeping orgs: ${KEEP_CSV}"

: > /tmp/cp-migrate-reset-ids.txt
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$keep = array_filter(array_map('intval', explode(',', '${KEEP_CSV}')));
foreach (App\Models\Organization::query()->orderBy('id')->get(['id','company_name']) as \$o) {
  if (in_array((int) \$o->id, \$keep, true)) {
    echo \"KEEP #{\$o->id} {\$o->company_name}\n\";
    continue;
  }
  echo \"RESET #{\$o->id} {\$o->company_name}\n\";
  file_put_contents('/tmp/cp-migrate-reset-ids.txt', \$o->id . PHP_EOL, FILE_APPEND);
}
"

while read -r ORG_ID; do
  [ -z "$ORG_ID" ] && continue
  echo "---- org ${ORG_ID} ----"
  curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
    -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/reset-crypto" || true
  echo
  rm -rf "storage/app/whatsapp/${ORG_ID}"
  mkdir -p "storage/app/whatsapp/${ORG_ID}"
  php -r "
  require 'vendor/autoload.php';
  \$app = require 'bootstrap/app.php';
  \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  \$c = App\Models\WhatsappConnection::where('organization_id', ${ORG_ID})->first();
  if (\$c) { \$c->update(['status'=>'disconnected','phone_number'=>null,'disconnected_at'=>now()]); }
  "
done < /tmp/cp-migrate-reset-ids.txt

chown -R www-data:www-data storage/app/whatsapp
chmod -R 775 storage/app/whatsapp

echo ""
echo "DONE"
echo "- Kept orgs (${KEEP_CSV}): should auto-restore — verify Connected in admin"
echo "- Other orgs: /admin/organizations/{id}/whatsapp → Connect → scan NEW QR"
echo "- Per-org helper: bash scripts/fix-org-whatsapp.sh <id>"
