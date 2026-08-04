#!/bin/bash
# Reset WhatsApp for one organization (Baileys 7). Does not restart PM2 unless needed.
# Usage: bash scripts/fix-org-whatsapp.sh <organization_id>
set -euo pipefail

ORG_ID="${1:?Usage: bash scripts/fix-org-whatsapp.sh <organization_id>}"
APP_DIR=/var/www/connectpulse
cd "$APP_DIR"

SECRET=$(grep '^WHATSAPP_BRIDGE_SECRET=' .env | cut -d= -f2- | tr -d '"' | tr -d "'")
if [ -z "$SECRET" ]; then
  echo "ERROR: WHATSAPP_BRIDGE_SECRET missing in .env"
  exit 1
fi

echo "==> Org lookup"
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$o = App\Models\Organization::find(${ORG_ID});
if (!\$o) { fwrite(STDERR, \"Org ${ORG_ID} not found\n\"); exit(1); }
echo \"#{\$o->id}\t{\$o->company_name}\t{\$o->email}\n\";
"

echo "==> Waiting for bridge"
for i in $(seq 1 20); do
  if curl -sf -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/health" >/dev/null; then
    break
  fi
  sleep 1
  if [ "$i" -eq 20 ]; then
    echo "ERROR: bridge not healthy — run: bash scripts/fix-whatsapp-bridge.sh"
    exit 1
  fi
done

echo "==> Reset crypto + wipe session files for org ${ORG_ID}"
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
if (\$c) {
  \$c->update(['status'=>'disconnected','phone_number'=>null,'disconnected_at'=>now()]);
  echo \"DB connection cleared\n\";
}
"

sleep 1
curl -sS -H "X-Bridge-Secret: ${SECRET}" -H "Content-Type: application/json" \
  -d "{\"organization_id\":${ORG_ID}}" "http://127.0.0.1:3001/init"
echo
sleep 4
echo "==> Status:"; curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/status?organization_id=${ORG_ID}"; echo
QR=$(curl -sS -H "X-Bridge-Secret: ${SECRET}" "http://127.0.0.1:3001/qr?organization_id=${ORG_ID}")
if echo "$QR" | grep -q 'data:image'; then echo "QR: ready"; else echo "QR: (open portal)"; fi

echo ""
echo "NEXT:"
echo "1) Phone: Linked devices → remove old ConnectPulse / web sessions"
echo "2) https://connectpulse.cloud/admin/organizations/${ORG_ID}/whatsapp"
echo "3) Ctrl+F5 → Connect → scan → wait Connected + 30s → test send"
echo "DONE"
