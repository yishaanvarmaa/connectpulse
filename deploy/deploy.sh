#!/bin/bash
set -euo pipefail

APP_DIR="/var/www/connectpulse"
BRANCH="${DEPLOY_BRANCH:-main}"

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
sudo supervisorctl restart connectpulse-queue:*
pm2 restart connectpulse-bridge || pm2 start whatsapp-bridge/ecosystem.config.cjs
sudo systemctl reload php8.4-fpm
sudo systemctl reload nginx

echo "==> Deployment complete"
