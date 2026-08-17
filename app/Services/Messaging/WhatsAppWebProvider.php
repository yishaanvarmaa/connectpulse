<?php

namespace App\Services\Messaging;

use App\Contracts\MessagingProviderInterface;
use App\Models\Organization;
use App\Models\WhatsappConnection;
use App\Services\WhatsAppBridgeService;
use Illuminate\Support\Facades\Log;

class WhatsAppWebProvider implements MessagingProviderInterface
{
    public function __construct(
        private WhatsAppBridgeService $bridge
    ) {}

    public function send(Organization $organization, string $mobile, string $message, ?string $mediaUrl = null): array
    {
        $result = $this->bridge->sendMessage((int) $organization->getKey(), $mobile, $message, $mediaUrl);

        if (! ($result['success'] ?? false)) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => $result['error'] ?? 'Message delivery failed.',
            ];
        }

        return [
            'success' => true,
            'message_id' => $result['message_id'] ?? null,
            'error' => null,
        ];
    }

    public function getStatus(Organization $organization): array
    {
        $organizationId = (int) $organization->getKey();

        if ($organizationId <= 0) {
            Log::error('WhatsApp status check missing organization id', [
                'organization' => $organization->only(['id', 'company_name']),
            ]);

            return [
                'connected' => false,
                'phone' => null,
                'status' => 'disconnected',
            ];
        }

        $bridgeStatus = $this->bridge->getStatus($organizationId);

        $connection = $organization->relationLoaded('whatsappConnection')
            ? $organization->whatsappConnection
            : $organization->whatsappConnection()->first();

        if ($connection) {
            $this->syncConnectionStatus($connection, $bridgeStatus);
        }

        return $bridgeStatus;
    }

    public function disconnect(Organization $organization): bool
    {
        $result = $this->bridge->disconnect((int) $organization->getKey());

        if ($connection = $organization->whatsappConnection) {
            $connection->update([
                'status' => WhatsappConnection::STATUS_DISCONNECTED,
                'phone_number' => null,
                'disconnected_at' => now(),
            ]);
        }

        return (bool) ($result['success'] ?? false);
    }

    public function getQr(Organization $organization): ?string
    {
        $result = $this->bridge->getQr((int) $organization->getKey());

        if ($connection = $organization->whatsappConnection) {
            $status = ($result['qr'] ?? null) ? WhatsappConnection::STATUS_QR_REQUIRED : $connection->status;
            $connection->update(['status' => $status]);
        }

        return $result['qr'] ?? null;
    }

    private function syncConnectionStatus(WhatsappConnection $connection, array $bridgeStatus): void
    {
        $isConnected = (bool) ($bridgeStatus['connected'] ?? false);
        $wasConnected = $connection->isConnected();

        $data = [
            'status' => $isConnected
                ? WhatsappConnection::STATUS_CONNECTED
                : ($bridgeStatus['status'] ?? WhatsappConnection::STATUS_DISCONNECTED),
            'phone_number' => $isConnected ? ($bridgeStatus['phone'] ?? $connection->phone_number) : null,
        ];

        if ($isConnected) {
            $data['connected_at'] = $connection->connected_at ?? now();
            $data['disconnected_at'] = null;
        } elseif ($wasConnected) {
            $data['disconnected_at'] = now();
        }

        $connection->update($data);
    }
}
