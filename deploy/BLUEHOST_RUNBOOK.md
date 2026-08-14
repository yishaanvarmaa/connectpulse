# Bluehost Open Terminal — ConnectPulse install (OnlyOffice-safe)

## Status checklist

| Step | Status |
|------|--------|
| Repo deploy scripts pushed (`229407c`) | Done |
| `docs.sreekaridiagnostix.com` → `50.6.44.102` | OK |
| `connectpulse.cloud` → `50.6.44.102` | **YOU MUST UPDATE** (currently `187.127.171.113` old VPS) |
| SSH from Cursor | Blocked — needs Bluehost root password / **Open Terminal** |

## 1. DNS (do this first)

Wherever `connectpulse.cloud` is managed, set:

```
A    connectpulse.cloud    50.6.44.102
```

Wait until:

```powershell
nslookup connectpulse.cloud
```

shows `50.6.44.102`.

## 2. Install on VPS (Bluehost → Hosting → Open Terminal)

Paste **one block** and press Enter:

```bash
set -e
docker ps | grep -E 'NAMES|onlyoffice' || true
curl -sI https://docs.sreekaridiagnostix.com | head -5 || true
cd /tmp
rm -rf connectpulse-src
git clone -b master https://github.com/yishaanvarmaa/connectpulse.git connectpulse-src
chmod +x connectpulse-src/deploy/bluehost-setup.sh
bash connectpulse-src/deploy/bluehost-setup.sh
```

This script **does not** stop OnlyOffice or replace the `docs.sreekaridiagnostix.com` Caddy block. It only appends `connectpulse.cloud`.

## 3. Verify (same terminal)

```bash
docker ps | grep onlyoffice
curl -sI https://docs.sreekaridiagnostix.com | head -5
curl -sI https://connectpulse.cloud | head -5
ss -lntp | grep -E ':8080|:3001|:443'
```

Expected: OnlyOffice still **Up**, docs site HTTPS OK, ConnectPulse HTTPS OK, bridge on `127.0.0.1:3001`.

## 4. After success

```bash
cd /var/www/connectpulse
php scripts/setup-sreekari-centers.php
```

### Before reconnecting WhatsApp (clear stale queue)

If WhatsApp was disconnected, old messages may still sit in the queue and will send as soon as the bridge reconnects. Audit and purge first:

```bash
cd /var/www/connectpulse
php scripts/purge-queued-messages.php          # list queued logs + pending jobs
php scripts/purge-queued-messages.php --purge  # cancel + refund credits + delete jobs
# or: php artisan connectpulse:purge-queued --dry-run
#     php artisan connectpulse:purge-queued
```

Log in: https://connectpulse.cloud/login  
Super admin: `admin@connectpulse.app` / `password` (change immediately)

Paste the full terminal output back into Cursor if anything fails.
