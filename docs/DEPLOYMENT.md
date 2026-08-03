# ConnectPulse Deployment Guide

Targets:
- **Bluehost VPS** (Ubuntu 22.04) co-hosted with OnlyOffice — preferred for `connectpulse.cloud` after Hostinger VPS loss
- **Hostinger VPS** (legacy Nginx layout below)

---

## Bluehost VPS + existing OnlyOffice (do not disturb Online Office)

**Server:** `50.6.44.102` (`edt.lci.mybluehost.me`)  
**Already running:** OnlyOffice Document Server in Docker (`onlyoffice`) behind Caddy at `https://docs.sreekaridiagnostix.com` → `127.0.0.1:8080`

### Do not touch

- Docker container `onlyoffice` and volumes `onlyoffice_data` / `onlyoffice_logs`
- Caddy site block for `docs.sreekaridiagnostix.com`
- Port **8080**
- Hostinger shared hosting for `sreekaridiagnostix.com`

### Architecture

```
Caddy :80/:443
├── docs.sreekaridiagnostix.com → 127.0.0.1:8080 (OnlyOffice)  ← KEEP
└── connectpulse.cloud → /var/www/connectpulse (PHP-FPM)       ← NEW

PM2: connectpulse-bridge @ 127.0.0.1:3001
Supervisor: connectpulse queue worker
MySQL: database connectpulse (separate)
```

### DNS

Point `connectpulse.cloud` **A record** → `50.6.44.102` (same IP as `docs.sreekaridiagnostix.com`).

### One-shot bootstrap (SSH as root)

```bash
# Confirm OnlyOffice first
docker ps | grep onlyoffice
curl -I https://docs.sreekaridiagnostix.com

# Clone then run bootstrap (keeps OnlyOffice / Caddy docs block intact)
cd /tmp
git clone -b master https://github.com/yishaanvarmaa/connectpulse.git connectpulse-src
bash connectpulse-src/deploy/bluehost-setup.sh
```

The script:

1. Installs PHP/MySQL/Node/PM2/Supervisor if missing
2. Creates MySQL DB/user `connectpulse`
3. Clones/updates `/var/www/connectpulse`
4. Builds app + WhatsApp bridge
5. **Appends** `connectpulse.cloud` to `/etc/caddy/Caddyfile` (does not replace OnlyOffice block)
6. Reloads Caddy, starts Supervisor + PM2

Caddy snippet alone: [`deploy/Caddyfile.connectpulse`](../deploy/Caddyfile.connectpulse)

### Subsequent deploys

```bash
cd /var/www/connectpulse
DEPLOY_BRANCH=master ./deploy/deploy.sh
```

### After first deploy

```bash
php scripts/setup-sreekari-centers.php
# Re-scan WhatsApp QR for each org; regenerate API keys if DB was fresh
```

Verify:

```bash
docker ps | grep onlyoffice          # still Up
curl -I https://docs.sreekaridiagnostix.com
curl -I https://connectpulse.cloud
ss -lntp | grep 3001                 # should be 127.0.0.1:3001 only
```

---

## Hostinger VPS (legacy Nginx)

Target: **Hostinger VPS** (Ubuntu 22.04+)

## Prerequisites

- PHP 8.4 + extensions: `mbstring`, `xml`, `curl`, `mysql`, `zip`, `bcmath`, `redis` (optional)
- MySQL 8.0+
- Node.js 20+
- Nginx **or** Caddy (Bluehost uses Caddy)
- Composer
- PM2 (`npm install -g pm2`)
- Supervisor

## Initial Server Setup

```bash
# System packages
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.4-fpm php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-redis \
  supervisor git unzip curl

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Application Setup

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone <your-repo-url> connectpulse
cd connectpulse
sudo chown -R www-data:www-data /var/www/connectpulse

# Environment
cp .env.example .env
php artisan key:generate
```

### Configure `.env`

```env
APP_NAME=ConnectPulse
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=connectpulse
DB_USERNAME=connectpulse
DB_PASSWORD=your-secure-password

QUEUE_CONNECTION=database
# Or with Redis:
# QUEUE_CONNECTION=redis
# REDIS_HOST=127.0.0.1

WHATSAPP_BRIDGE_URL=http://127.0.0.1:3001
WHATSAPP_BRIDGE_SECRET=generate-a-long-random-secret
MESSAGE_RATE_LIMIT_SECONDS=2
MESSAGE_QUEUE_RETRIES=3

CONNECTPULSE_LEGAL_NAME="Ishnex Solutions Private Limited"
CONNECTPULSE_PRODUCT_NAME=ConnectPulse
CONNECTPULSE_PAYMENT_GATEWAY=Cashfree
CONNECTPULSE_SUPPORT_EMAIL=support@connectpulse.cloud
```

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE connectpulse; CREATE USER 'connectpulse'@'localhost' IDENTIFIED BY 'your-secure-password'; GRANT ALL ON connectpulse.* TO 'connectpulse'@'localhost';"

# Install and build
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cd whatsapp-bridge && npm ci --omit=dev && cd ..

# Migrate and seed
php artisan migrate --force
php artisan db:seed --force

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
mkdir -p storage/app/whatsapp
```

## Nginx

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/connectpulse
sudo ln -s /etc/nginx/sites-available/connectpulse /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

On Bluehost with Caddy, SSL is automatic — do not run certbot against Nginx if Caddy owns 80/443.

## Queue Worker (Supervisor)

```bash
sudo cp deploy/supervisor-queue.conf /etc/supervisor/conf.d/connectpulse-queue.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start connectpulse-queue:*
```

## WhatsApp Bridge (PM2)

```bash
cd whatsapp-bridge
# Set BRIDGE_SECRET in ecosystem.config.cjs to match .env
pm2 start ecosystem.config.cjs
pm2 save
pm2 startup
```

## Subsequent Deployments

```bash
chmod +x deploy/deploy.sh
DEPLOY_BRANCH=master ./deploy/deploy.sh
```

## Default Credentials (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@connectpulse.app | password |
| Surabhi Diagnostics | admin@surabhidiagnostics.com | password |
| Navocab | admin@navocab.com | password |
| Sreekari Diagnostix | admin@sreekaridiagnostix.com | password |
| Sri Narayani Imaging | imaging@sreekaridiagnostix.com | password |

**Change all passwords immediately in production.**

## API Quick Reference

```bash
# Send message
curl -X POST https://connectpulse.cloud/api/v1/messages/send \
  -H "X-API-Key: cp_live_..." \
  -H "X-API-Secret: ..." \
  -H "Content-Type: application/json" \
  -d '{"mobile":"919876543210","message":"Your report is ready."}'

# Check balance
curl https://connectpulse.cloud/api/v1/credits/balance \
  -H "X-API-Key: cp_live_..." \
  -H "X-API-Secret: ..."

# Connection status
curl https://connectpulse.cloud/api/v1/connection \
  -H "X-API-Key: cp_live_..." \
  -H "X-API-Secret: ..."
```

Organization portal URLs for client app redirects:
- WhatsApp: `https://connectpulse.cloud/whatsapp`
- Recharge: `https://connectpulse.cloud/recharge`
