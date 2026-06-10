<?php

namespace App\Services\Messaging;

use App\Contracts\MessagingProviderInterface;
use App\Models\Organization;
use App\Models\WhatsappConnection;
use App\Services\WhatsAppBridgeService;

class WhatsAppWebProvider implements MessagingProviderInterface
{
    public function __construct(
        private WhatsAppBridgeService $bridge
    ) {}

    public function send(Organization $organization, string $mobile, string $message): array
    {
        $result = $this->bridge->sendMessage($organization->id, $mobile, $message);

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
        $bridgeStatus = $this->bridge->getStatus($organization->id);
        $connection = $organization->whatsappConnection;

        if ($connection) {
            $this->syncConnectionStatus($connection, $bridgeStatus);
        }

        return [
            'connected' => (bool) ($bridgeStatus['connected'] ?? false),
            'phone' => $bridgeStatus['phone'] ?? $connection?->phone_number,
            'status' => $bridgeStatus['status'] ?? $connection?->status ?? WhatsappConnection::STATUS_DISCONNECTED,
        ];
    }

    public function disconnect(Organization $organization): bool
    {
        $result = $this->bridge->disconnect($organization->id);

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
        $result = $this->bridge->getQr($organization->id);

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
                : ($bridgeStatus['status'] ?? $connection->status),
            'phone_number' => $bridgeStatus['phone'] ?? $connection->phone_number,
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
