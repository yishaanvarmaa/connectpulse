#!/bin/bash
# Diagnose + repair WhatsApp bridge so QR scanning works.
# Safe for OnlyOffice — does not touch Docker/Caddy docs block.
set -euo pipefail

APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

echo "==> Bridge process"
pm2 describe connectpulse-bridge 2>/dev/null | head -40 || pm2 list

echo "==> Secrets (Laravel vs PM2)"
ENV_SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")
PM2_SECRET=$(grep -o "BRIDGE_SECRET: *'[^']*'" whatsapp-bridge/ecosystem.config.cjs | head -1 | sed "s/.*'\\(.*\\)'/\\1/")
echo "    .env WHATSAPP_BRIDGE_SECRET length: ${#ENV_SECRET}"
echo "    PM2  BRIDGE_SECRET length: ${#PM2_SECRET}"

if [ -z "$ENV_SECRET" ] || [ "$ENV_SECRET" = "change-me-in-production" ]; then
  ENV_SECRET=$(openssl rand -hex 32)
  if grep -q '^WHATSAPP_BRIDGE_SECRET=' .env; then
    sed -i "s|^WHATSAPP_BRIDGE_SECRET=.*|WHATSAPP_BRIDGE_SECRET=${ENV_SECRET}|" .env
  else
    echo "WHATSAPP_BRIDGE_SECRET=${ENV_SECRET}" >> .env
  fi
  echo "    generated new WHATSAPP_BRIDGE_SECRET"
fi

sed -i "s|BRIDGE_SECRET: '.*'|BRIDGE_SECRET: '${ENV_SECRET}'|" whatsapp-bridge/ecosystem.config.cjs
echo "    synced PM2 secret to match .env"

if ! grep -q '^WHATSAPP_BRIDGE_URL=' .env; then
  echo 'WHATSAPP_BRIDGE_URL=http://127.0.0.1:3001' >> .env
fi
sed -i 's|^WHATSAPP_BRIDGE_URL=.*|WHATSAPP_BRIDGE_URL=http://127.0.0.1:3001|' .env

echo "==> Session directory permissions"
mkdir -p storage/app/whatsapp
chown -R www-data:www-data storage bootstrap/cache
# Bridge (PM2 as root) must write sessions; keep group www-data
chmod -R 775 storage/app/whatsapp

echo "==> Restart bridge + clear Laravel config cache"
cd whatsapp-bridge
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"
php artisan config:clear
php artisan config:cache

sleep 2

echo "==> Health / auth test"
SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")
curl -sS -o /tmp/cp-bridge-health.json -w "HTTP %{http_code}\n" \
  -H "X-Bridge-Secret: ${SECRET}" \
  "http://127.0.0.1:3001/health" || true
cat /tmp/cp-bridge-health.json 2>/dev/null; echo

echo "==> Init session for org 1 (QR probe)"
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d '{"organization_id":1}' \
  "http://127.0.0.1:3001/init" || true
echo
echo "Waiting 5s for QR..."
sleep 5
curl -sS -H "X-Bridge-Secret: ${SECRET}" \
  "http://127.0.0.1:3001/qr?organization_id=1" | head -c 200
echo
curl -sS -H "X-Bridge-Secret: ${SECRET}" \
  "http://127.0.0.1:3001/status?organization_id=1"
echo
echo "==> Recent bridge logs"
pm2 logs connectpulse-bridge --lines 30 --nostream || true
echo "DONE — open /whatsapp in the portal and click Connect WhatsApp"
