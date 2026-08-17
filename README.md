# ConnectPulse

Centralized messaging platform for businesses. Connect WhatsApp once, send messages through a secure API.

## Tech Stack

- Laravel 13 / PHP 8.4
- MySQL
- Node.js 20+ / Baileys (WhatsApp Web bridge)
- Tailwind CSS 4
- Redis (optional) or database queue
- PM2 + Nginx + Supervisor

## Quick Start (Development)

```bash
# Install dependencies
composer install
npm install
cd whatsapp-bridge && npm install && cd ..

# Configure environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build assets
npm run build

# Start services (separate terminals)
php artisan serve
php artisan queue:work --queue=campaigns,messages,default
cd whatsapp-bridge && npm start
```

## Default Logins

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@connectpulse.app | password |
| Surabhi Diagnostics | admin@surabhidiagnostics.com | password |
| Navocab | admin@navocab.com | password |

## Public API

**Base URL:** `https://connectpulse.cloud/api/v1`

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/messages/send` | Send single message |
| POST | `/messages/bulk` | Send bulk messages |
| GET | `/connection` | WhatsApp connection status |
| GET | `/credits/balance` | Credit balance |

Authentication: `X-API-Key` and `X-API-Secret` headers.

**Organization portal:** `/whatsapp`, `/recharge`, `/api-keys`

See [API Documentation](docs/API.md).

## Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Deployment](docs/DEPLOYMENT.md)

## Project Structure

```
connectpulse/
├── app/                  # Laravel application
├── database/             # Migrations & seeders
├── resources/            # Views & assets
├── routes/               # Web & API routes
├── whatsapp-bridge/      # Node.js Baileys bridge
├── deploy/               # Nginx, Supervisor, deploy script
└── docs/                 # Architecture & deployment docs
```
