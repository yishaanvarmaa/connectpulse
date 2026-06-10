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

    public function sendMessage(int $organizationId, string $mobile, string $message): array
    {
        return $this->request('POST', '/send', [
            'organization_id' => $organizationId,
            'mobile' => $mobile,
            'message' => $message,
        ]);
    }

    public function getStatus(int $organizationId): array
    {
        return $this->request('GET', '/status', [
            'organization_id' => $organizationId,
        ]);
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
                return $response->json() ?? ['success' => true];
            }

            Log::warning('WhatsApp bridge request failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'WhatsApp service is temporarily unavailable.',
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp bridge connection error', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'WhatsApp service is temporarily unavailable.',
            ];
        }
    }
}
