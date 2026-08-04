#!/bin/bash
# Surabhi (org 1) — wipe session, restart bridge in fork mode, wait until healthy, fresh QR
set -euo pipefail
ORG_ID=1
APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

git config --global --add safe.directory "$APP_DIR" || true
# Discard local edits that block pull (script + PM2 secret rewrite, npm lock drift)
git checkout -- scripts/fix-surabhi-whatsapp.sh whatsapp-bridge/ecosystem.config.cjs \
  whatsapp-bridge/package.json whatsapp-bridge/package-lock.json 2>/dev/null || true
git pull origin master

# Keep PM2 secret in sync with Laravel .env
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

cd whatsapp-bridge
# Force clean install so Baileys 7 upgrades are not skipped
rm -rf node_modules
npm install --omit=dev
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"
php artisan config:cache

echo "==> Waiting for bridge on :3001"
for i in $(seq 1 30); do
  if curl -sf -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/health" >/dev/null; then
    echo "    bridge healthy after ${i}s"
    break
  fi
  sleep 1
  if [ "$i" -eq 30 ]; then
    echo "ERROR: bridge did not become healthy"
    pm2 logs connectpulse-bridge --lines 40 --nostream || true
    exit 1
  fi
done

echo "==> Reset Surabhi crypto"
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/reset-crypto"
echo
rm -rf "storage/app/whatsapp/${ORG_ID}"
mkdir -p "storage/app/whatsapp/${ORG_ID}"
chown -R www-data:www-data storage/app/whatsapp
chmod -R 775 storage/app/whatsapp

php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$c = App\Models\WhatsappConnection::where('organization_id', ${ORG_ID})->first();
if (\$c) { \$c->update(['status'=>'disconnected','phone_number'=>null,'disconnected_at'=>now()]); echo \"DB cleared\n\"; }
" || true

sleep 1
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/init"
echo
sleep 5
echo "==> Status:"; curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/status?organization_id=${ORG_ID}"; echo
QR=$(curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/qr?organization_id=${ORG_ID}")
if echo "$QR" | grep -q 'data:image'; then echo "QR: ready"; else echo "QR: $QR"; fi

echo ""
echo "NEXT (Baileys 7 — MUST rescan QR, old sessions invalid):"
echo "1) Phone: WhatsApp → Linked devices → remove ConnectPulse / all web sessions"
echo "2) https://connectpulse.cloud/admin/organizations/1/whatsapp"
echo "3) Ctrl+F5 → Connect → scan with Surabhi phone ONLY"
echo "4) Wait until Connected + 30s, then send test to a DIFFERENT phone"
echo "5) If still broken: ensure this number is NOT also linked to Meta Cloud API"
echo "DONE"
