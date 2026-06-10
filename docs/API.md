# ConnectPulse Public API

**Base URL:** `https://connectpulse.cloud/api/v1`

All responses are JSON. Client applications (Surabhi Diagnostics, Navocab, HMS/ERP systems) integrate only via this API. WhatsApp, Baileys, sessions, and credits are managed entirely inside ConnectPulse.

## Authentication

Every request must include:

| Header | Description |
|--------|-------------|
| `X-API-Key` | Organization API key (`cp_live_...`) |
| `X-API-Secret` | Organization API secret |
| `Content-Type` | `application/json` (for POST requests) |

Invalid credentials return **HTTP 401**:

```json
{
  "success": false,
  "message": "Invalid API credentials."
}
```

## Endpoints

### Send Message

`POST /messages/send`

```json
{
  "mobile": "919876543210",
  "message": "Your report is ready."
}
```

**Success (200):**

```json
{
  "success": true,
  "message_id": "123"
}
```

**Insufficient credits (402):**

```json
{
  "success": false,
  "message": "Insufficient credits."
}
```

**WhatsApp not connected (422):**

```json
{
  "success": false,
  "message": "WhatsApp not connected."
}
```

Phone numbers are normalized automatically (spaces, dashes, and `+` removed).

Credits are deducted when the message is accepted into the queue (1 credit per message).

---

### Send Bulk Messages

`POST /messages/bulk`

```json
{
  "messages": [
    { "mobile": "919876543210", "message": "Patient A report ready" },
    { "mobile": "919876543211", "message": "Patient B report ready" }
  ]
}
```

**Success (200):**

```json
{
  "success": true,
  "batch_id": "uuid",
  "queued": 2
}
```

---

### Connection Status

`GET /connection`

**Connected:**

```json
{
  "connected": true,
  "phone": "919876543210",
  "display_name": "Surabhi Diagnostics"
}
```

**Disconnected:**

```json
{
  "connected": false
}
```

---

### Credit Balance

`GET /credits/balance`

```json
{
  "balance": 500,
  "credits_remaining": 500
}
```

## Organization Portal (not API)

These URLs are for organization admins in a browser — client apps should redirect users here, not embed WhatsApp QR:

| Purpose | URL |
|---------|-----|
| Connect WhatsApp | `https://connectpulse.cloud/whatsapp` |
| Recharge credits | `https://connectpulse.cloud/recharge` |
| API credentials | `https://connectpulse.cloud/api-keys` |
| Dashboard | `https://connectpulse.cloud/dashboard` |

## Legacy Endpoints

The following remain available for backward compatibility:

- `POST /send-message` → `/messages/send`
- `POST /send-bulk` → `/messages/bulk`
- `GET /status` → `/connection`
- `GET /balance` → `/credits/balance`

## Example (cURL)

```bash
curl -X POST https://connectpulse.cloud/api/v1/messages/send \
  -H "X-API-Key: cp_live_xxxxx" \
  -H "X-API-Secret: xxxxx" \
  -H "Content-Type: application/json" \
  -d '{"mobile":"919876543210","message":"Your report is ready."}'
```
