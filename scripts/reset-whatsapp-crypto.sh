#!/bin/bash
# Full crypto reset for one org + bridge upgrade.
# Usage: bash scripts/reset-whatsapp-crypto.sh 3
set -euo pipefail

ORG_ID="${1:?Usage: bash scripts/reset-whatsapp-crypto.sh <organization_id>}"
APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")

echo "==> Pull + upgrade Baileys"
git config --global --add safe.directory "$APP_DIR" || true
git pull origin master
cd whatsapp-bridge
npm install @whiskeysockets/baileys@^6.7.21 --omit=dev
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"

sleep 2

echo "==> Reset crypto for org ${ORG_ID}"
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" \
  "http://127.0.0.1:3001/reset-crypto"
echo
rm -rf "storage/app/whatsapp/${ORG_ID}"
mkdir -p "storage/app/whatsapp/${ORG_ID}"
chmod -R 775 storage/app/whatsapp

echo "==> Init fresh QR session"
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" \
  "http://127.0.0.1:3001/init"
echo
sleep 4
curl -sS -H "X-Bridge-Secret: ${SECRET}" \
  "http://127.0.0.1:3001/status?organization_id=${ORG_ID}"
echo
echo "DONE — open portal WhatsApp page, scan NEW QR, then retest send"
