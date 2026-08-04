#!/bin/bash
# Surabhi Diagnostics (org 1) — full bridge upgrade + Surabhi reset.
# Prefer migrate-orgs-baileys7.sh for multi-org; this remains for Surabhi-only emergencies.
set -euo pipefail
APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

git config --global --add safe.directory "$APP_DIR" || true
git checkout -- scripts/fix-surabhi-whatsapp.sh whatsapp-bridge/ecosystem.config.cjs \
  whatsapp-bridge/package.json whatsapp-bridge/package-lock.json 2>/dev/null || true
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

cd whatsapp-bridge
rm -rf node_modules
npm install --omit=dev
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
cd "$APP_DIR"
php artisan config:cache

chmod +x scripts/fix-org-whatsapp.sh
bash scripts/fix-org-whatsapp.sh 1
