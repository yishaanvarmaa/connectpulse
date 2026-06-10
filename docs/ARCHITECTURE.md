# ConnectPulse Architecture

## Overview

ConnectPulse is a multi-tenant SaaS messaging platform. Each organization connects a WhatsApp Business number once and sends messages through a secure REST API. Credits are deducted only on successful delivery.

## System Components

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│  Client Apps    │────▶│  Laravel API     │────▶│  Queue Workers      │
│  (REST API)     │     │  (PHP 8.4)       │     │  (Supervisor)       │
└─────────────────┘     └────────┬─────────┘     └──────────┬──────────┘
                                 │                          │
┌─────────────────┐              │                          │
│  Admin Dashboard│──────────────┘                          │
│  (Blade/Tailwind)│                                         │
└─────────────────┘                                         ▼
                    ┌──────────────────┐     ┌─────────────────────┐
                    │  WhatsApp Bridge │◀────│  Baileys Sessions   │
                    │  (Node.js/PM2)   │     │  per Organization   │
                    └──────────────────┘     └─────────────────────┘
```

## Multi-Tenancy

- **Organizations** are the tenant boundary
- Each organization has: API keys, credit wallet, WhatsApp session, message logs
- Sessions stored in `storage/app/whatsapp/{organization_id}/`
- Super Admin manages all organizations globally

## Authentication

| Context | Method |
|---------|--------|
| Dashboard | Session-based login (Super Admin / Org Admin) |
| REST API | `X-API-KEY` + `X-API-SECRET` headers |

## Messaging Flow

1. API receives `POST /api/v1/send-message`
2. Middleware validates API key, organization status
3. Controller validates WhatsApp connection and credit balance
4. Message log created with status `queued`
5. `SendWhatsAppMessage` job dispatched to `messages` queue
6. Rate limiter enforces ~1 message per 2 seconds
7. Job calls `WhatsAppWebProvider` → `WhatsAppBridgeService` → Node bridge
8. On success: deduct 1 credit, mark log `sent`
9. On failure: no credit deduction, mark log `failed`, retry up to 3 times

## Provider Interface

```php
interface MessagingProviderInterface {
    send(Organization, mobile, message): array
    getStatus(Organization): array
    disconnect(Organization): bool
    getQr(Organization): ?string
}
```

| Provider | Status |
|----------|--------|
| `WhatsAppWebProvider` | Active (Baileys) |
| `MetaCloudProvider` | Placeholder |
| `SmsProvider` | Placeholder |
| `EmailProvider` | Placeholder |

## Database Schema

- `users` — role (`super_admin`, `organization_admin`), optional `organization_id`
- `organizations` — tenant records
- `api_keys` — encrypted secrets per organization
- `whatsapp_connections` — connection status and phone number
- `credit_wallets` — balance per organization
- `credit_transactions` — audit trail
- `message_logs` — all outbound messages

## Queue Strategy

- Default: database queue (Redis optional via `QUEUE_CONNECTION=redis`)
- Dedicated `messages` queue for outbound WhatsApp
- 3 retry attempts with 5-second backoff
- Failed jobs logged; message status updated to `failed`

## Security

- API secrets encrypted at rest (Laravel `encrypted` cast)
- Bridge API protected by `X-Bridge-Secret` header
- Bridge binds to `127.0.0.1` only (not exposed publicly)
- Client-facing errors are sanitized (no stack traces in API responses)
