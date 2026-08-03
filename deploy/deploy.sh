#!/bin/bash
set -euo pipefail

APP_DIR="/var/www/connectpulse"
BRANCH="${DEPLOY_BRANCH:-master}"

echo "==> Deploying ConnectPulse from branch: $BRANCH"

cd "$APP_DIR"

echo "==> Pulling latest code"
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node dependencies (Laravel assets)"
npm ci
npm run build

echo "==> Installing WhatsApp bridge dependencies"
cd whatsapp-bridge
npm ci --omit=dev
cd ..

echo "==> Running migrations"
php artisan migrate --force

echo "==> Optimizing Laravel"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Restarting services"
sudo supervisorctl restart connectpulse-queue:* || true
pm2 restart connectpulse-bridge || pm2 start whatsapp-bridge/ecosystem.config.cjs
sudo systemctl reload php8.4-fpm 2>/dev/null || sudo systemctl reload php-fpm 2>/dev/null || true
# Prefer Caddy (Bluehost + OnlyOffice). Fall back to nginx if present.
if systemctl is-active --quiet caddy 2>/dev/null; then
  sudo systemctl reload caddy
elif systemctl is-active --quiet nginx 2>/dev/null; then
  sudo systemctl reload nginx
fi

echo "==> Deployment complete"
