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
php artisan queue:work --queue=messages,default
cd whatsapp-bridge && npm start
```

## Default Logins

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@connectpulse.app | password |
| Surabhi Diagnostics | admin@surabhidiagnostics.com | password |
| Navocab | admin@navocab.com | password |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/send-message` | Send single message |
| POST | `/api/v1/send-bulk` | Send bulk messages |
| GET | `/api/v1/balance` | Credit balance |
| GET | `/api/v1/status` | WhatsApp connection status |

Authentication: `X-API-KEY` and `X-API-SECRET` headers.

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
