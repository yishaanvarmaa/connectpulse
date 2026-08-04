#!/bin/bash
# Surabhi Diagnostics (org 1) — full crypto wipe + Baileys 6.7.24 + fresh QR
set -euo pipefail
ORG_ID=1
APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

git config --global --add safe.directory "$APP_DIR" || true
git pull origin master

cd whatsapp-bridge
npm install @whiskeysockets/baileys@6.7.24 --omit=dev
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"

SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")

echo "==> Reset Surabhi org ${ORG_ID}"
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/reset-crypto" || true
echo
rm -rf "storage/app/whatsapp/${ORG_ID}"
mkdir -p "storage/app/whatsapp/${ORG_ID}"
chown -R www-data:www-data storage/app/whatsapp
chmod -R 775 storage/app/whatsapp

php artisan tinker --execute="
\$c = App\Models\WhatsappConnection::where('organization_id', ${ORG_ID})->first();
if (\$c) { \$c->update(['status'=>'disconnected','phone_number'=>null,'disconnected_at'=>now()]); }
echo 'DB connection cleared\n';
" 2>/dev/null || true

sleep 2
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/init"
echo
sleep 5
echo "==> Status:"
curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/status?organization_id=${ORG_ID}"
echo
echo "==> QR present?"
curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/qr?organization_id=${ORG_ID}" | head -c 80
echo
echo "DONE — Surabhi: open /admin/organizations/1/whatsapp , scan NEW QR, then test send"
