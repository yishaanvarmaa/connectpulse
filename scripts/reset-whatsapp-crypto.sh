#!/bin/bash
# Full crypto reset for one org (Baileys 7). Alias of fix-org-whatsapp.sh
# Usage: bash scripts/reset-whatsapp-crypto.sh <organization_id>
set -euo pipefail
APP_DIR=/var/www/connectpulse
ORG_ID="${1:?Usage: bash scripts/reset-whatsapp-crypto.sh <organization_id>}"
cd "$APP_DIR"
chmod +x scripts/fix-org-whatsapp.sh
exec bash scripts/fix-org-whatsapp.sh "$ORG_ID"
