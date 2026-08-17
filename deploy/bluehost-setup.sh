#!/bin/bash
# Safe Bluehost VPS bootstrap for ConnectPulse alongside OnlyOffice.
# Does NOT stop/remove Docker OnlyOffice, does NOT overwrite Caddy docs block.
set -euo pipefail

DOMAIN="${CONNECTPULSE_DOMAIN:-connectpulse.cloud}"
APP_DIR="/var/www/connectpulse"
REPO_URL="${CONNECTPULSE_REPO:-https://github.com/yishaanvarmaa/connectpulse.git}"
BRANCH="${DEPLOY_BRANCH:-master}"

echo "==> Safety check: OnlyOffice container must stay running"
if command -v docker >/dev/null 2>&1; then
  if docker ps --format '{{.Names}}' | grep -qx 'onlyoffice'; then
    echo "    onlyoffice container: UP (good)"
  else
    echo "WARNING: onlyoffice container not found in docker ps."
    echo "         Continue only if OnlyOffice is intentionaly elsewhere."
  fi
fi

echo "==> Installing packages (PHP, MySQL client tools, Node, Supervisor, Composer deps)"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y

# PHP 8.4 if available via ondrej, else install default php-fpm and document version
if ! command -v php8.4 >/dev/null 2>&1 && ! php -v 2>/dev/null | grep -q '8\.4'; then
  apt-get install -y software-properties-common
  add-apt-repository -y ppa:ondrej/php || true
  apt-get update -y
fi

apt-get install -y \
  git unzip curl supervisor \
  mysql-server \
  php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath \
  || apt-get install -y \
  git unzip curl supervisor \
  mysql-server \
  php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath

# Node 20
if ! command -v node >/dev/null 2>&1 || [[ "$(node -v | sed 's/v//' | cut -d. -f1)" -lt 20 ]]; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

npm install -g pm2

if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

echo "==> Creating MySQL database/user if missing"
DB_PASS="${CONNECTPULSE_DB_PASSWORD:-$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)}"
mysql -e "CREATE DATABASE IF NOT EXISTS connectpulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'connectpulse'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "ALTER USER 'connectpulse'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON connectpulse.* TO 'connectpulse'@'localhost'; FLUSH PRIVILEGES;"
echo "    DB password saved to /root/connectpulse-db-password.txt"
echo "$DB_PASS" > /root/connectpulse-db-password.txt
chmod 600 /root/connectpulse-db-password.txt

echo "==> Cloning / updating application"
mkdir -p /var/www
if [[ -d "$APP_DIR/.git" ]]; then
  cd "$APP_DIR"
  git fetch origin
  git checkout "$BRANCH"
  git pull origin "$BRANCH"
else
  if [[ -d "$APP_DIR" ]] && [[ ! -d "$APP_DIR/.git" ]]; then
    echo "ERROR: $APP_DIR exists but is not a git repo. Move it aside manually."
    exit 1
  fi
  git clone -b "$BRANCH" "$REPO_URL" "$APP_DIR"
  cd "$APP_DIR"
fi

BRIDGE_SECRET="${WHATSAPP_BRIDGE_SECRET:-$(openssl rand -hex 32)}"

if [[ ! -f "$APP_DIR/.env" ]]; then
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  php artisan key:generate --force
fi

# Patch .env core values (idempotent-ish via sed)
set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "$APP_DIR/.env"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$APP_DIR/.env"
  else
    echo "${key}=${val}" >> "$APP_DIR/.env"
  fi
}

set_env APP_NAME ConnectPulse
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://${DOMAIN}"
set_env DB_CONNECTION mysql
set_env DB_HOST 127.0.0.1
set_env DB_PORT 3306
set_env DB_DATABASE connectpulse
set_env DB_USERNAME connectpulse
set_env DB_PASSWORD "$DB_PASS"
set_env QUEUE_CONNECTION database
set_env WHATSAPP_BRIDGE_URL http://127.0.0.1:3001
set_env WHATSAPP_BRIDGE_SECRET "$BRIDGE_SECRET"
set_env CONNECTPULSE_LEGAL_NAME ConnectPulse
set_env CONNECTPULSE_PRODUCT_NAME ConnectPulse
set_env CONNECTPULSE_PAYMENT_GATEWAY Cashfree
set_env CONNECTPULSE_SUPPORT_EMAIL support@connectpulse.cloud

# Sync bridge secret into ecosystem.config.cjs
sed -i "s|BRIDGE_SECRET: '.*'|BRIDGE_SECRET: '${BRIDGE_SECRET}'|" "$APP_DIR/whatsapp-bridge/ecosystem.config.cjs"

echo "==> Composer / npm build"
cd "$APP_DIR"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
cd whatsapp-bridge && npm ci --omit=dev && cd ..

echo "==> Migrate + seed"
php artisan migrate --force
php artisan db:seed --force || true

mkdir -p storage/app/whatsapp
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Supervisor queue worker"
cp "$APP_DIR/deploy/supervisor-queue.conf" /etc/supervisor/conf.d/connectpulse-queue.conf
supervisorctl reread
supervisorctl update
supervisorctl start connectpulse-queue:* || supervisorctl restart connectpulse-queue:*

echo "==> PM2 WhatsApp bridge (127.0.0.1:3001 only)"
cd "$APP_DIR/whatsapp-bridge"
pm2 delete connectpulse-bridge >/dev/null 2>&1 || true
pm2 start ecosystem.config.cjs
pm2 save
pm2 startup systemd -u root --hp /root >/dev/null 2>&1 || true

echo "==> Extend Caddy for ${DOMAIN} (preserve OnlyOffice block)"
CADDYFILE="/etc/caddy/Caddyfile"
if [[ ! -f "$CADDYFILE" ]]; then
  echo "ERROR: $CADDYFILE not found. Create Caddyfile manually using deploy/Caddyfile.connectpulse"
  exit 1
fi

if grep -q "connectpulse.cloud" "$CADDYFILE"; then
  echo "    connectpulse.cloud already present in Caddyfile"
else
  # Detect PHP-FPM socket
  PHP_SOCK="/run/php/php8.4-fpm.sock"
  if [[ ! -S "$PHP_SOCK" ]]; then
    PHP_SOCK="$(ls /run/php/php*-fpm.sock 2>/dev/null | head -n1 || true)"
  fi
  if [[ -z "${PHP_SOCK}" ]]; then
    echo "ERROR: PHP-FPM socket not found"
    exit 1
  fi

  cat >> "$CADDYFILE" <<EOF

# ConnectPulse — added by deploy/bluehost-setup.sh (do not remove OnlyOffice block above)
${DOMAIN} {
	root * ${APP_DIR}/public
	encode gzip
	php_fastcgi unix/${PHP_SOCK}
	try_files {path} /index.php?{query}
	file_server
	header {
		X-Frame-Options SAMEORIGIN
		X-Content-Type-Options nosniff
	}
}
EOF
fi

caddy validate --config "$CADDYFILE"
systemctl reload caddy

echo "==> Post-setup: Sreekari centers"
cd "$APP_DIR"
php scripts/setup-sreekari-centers.php || true

echo "==> Final safety check"
docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' 2>/dev/null || true
ss -lntp | grep -E ':80|:443|:8080|:3001' || true

echo ""
echo "========================================"
echo " ConnectPulse bootstrap complete"
echo " Domain: https://${DOMAIN}"
echo " DB password: /root/connectpulse-db-password.txt"
echo " Bridge secret written to .env + ecosystem.config.cjs"
echo " VERIFY: https://docs.sreekaridiagnostix.com still works"
echo "========================================"
