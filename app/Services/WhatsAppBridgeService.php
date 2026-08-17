<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBridgeService
{
    private string $baseUrl;

    private string $secret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('connectpulse.whatsapp_bridge_url'), '/');
        $this->secret = config('connectpulse.whatsapp_bridge_secret', '');
    }

    public function sendMessage(int $organizationId, string $mobile, string $message, ?string $mediaUrl = null): array
    {
        $payload = [
            'organization_id' => $organizationId,
            'mobile' => $mobile,
            'message' => $message,
        ];

        if ($mediaUrl) {
            $payload['media_url'] = $mediaUrl;
        }

        return $this->request('POST', '/send', $payload);
    }

    public function getStatus(int $organizationId): array
    {
        $organizationId = (int) $organizationId;

        $result = $this->request('GET', '/status', [
            'organization_id' => $organizationId,
        ]);

        if (! array_key_exists('connected', $result)) {
            Log::warning('WhatsApp bridge status could not be retrieved', [
                'organization_id' => $organizationId,
                'bridge_url' => $this->baseUrl.'/status',
                'response' => $result,
            ]);

            return [
                'connected' => false,
                'phone' => null,
                'status' => 'disconnected',
            ];
        }

        $connected = $this->normalizeConnected($result['connected']);

        return [
            'connected' => $connected,
            'phone' => $connected ? ($result['phone'] ?? null) : null,
            'status' => $result['status'] ?? ($connected ? 'connected' : 'disconnected'),
        ];
    }

    public function getQr(int $organizationId): array
    {
        return $this->request('GET', '/qr', [
            'organization_id' => $organizationId,
        ]);
    }

    public function disconnect(int $organizationId): array
    {
        return $this->request('POST', '/disconnect', [
            'organization_id' => $organizationId,
        ]);
    }

    public function resetCrypto(int $organizationId): array
    {
        return $this->request('POST', '/reset-crypto', [
            'organization_id' => $organizationId,
        ]);
    }

    public function initSession(int $organizationId): array
    {
        return $this->request('POST', '/init', [
            'organization_id' => $organizationId,
        ]);
    }

    private function request(string $method, string $path, array $data = []): array
    {
        try {
            $http = Http::timeout(30)
                ->withHeaders(['X-Bridge-Secret' => $this->secret]);

            $response = match (strtoupper($method)) {
                'GET' => $http->get($this->baseUrl.$path, $data),
                'POST' => $http->post($this->baseUrl.$path, $data),
                default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
            };

            if ($response->successful()) {
                $json = $response->json();

                if (! is_array($json)) {
                    Log::warning('WhatsApp bridge returned invalid JSON', [
                        'path' => $path,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Invalid bridge response.',
                    ];
                }

                return $json;
            }

            Log::warning('WhatsApp bridge request failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'WhatsApp service is temporarily unavailable.',
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp bridge connection error', [
                'path' => $path,
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'WhatsApp service is temporarily unavailable.',
            ];
        }
    }

    private function normalizeConnected(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'connected'], true);
        }

        return false;
    }
}
