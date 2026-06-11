<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\WhatsappConnection;

class WhatsAppConnectionService
{
    public function __construct(
        private MessageService $messageService,
        private WhatsAppBridgeService $bridgeService,
    ) {}

    public function ensureConnection(Organization $organization): WhatsappConnection
    {
        return $organization->whatsappConnection ?? WhatsappConnection::create([
            'organization_id' => $organization->id,
            'status' => WhatsappConnection::STATUS_DISCONNECTED,
            'session_path' => storage_path("app/whatsapp/{$organization->id}"),
        ]);
    }

    public function connect(Organization $organization): void
    {
        $liveStatus = $this->messageService->provider()->getStatus($organization);

        if (in_array($liveStatus['status'] ?? '', ['reconnecting', 'qr_required'], true) && ! ($liveStatus['connected'] ?? false)) {
            $this->messageService->provider()->disconnect($organization);
        }

        $connection = $this->ensureConnection($organization);

        $this->bridgeService->initSession((int) $organization->id);
        $connection->update(['status' => WhatsappConnection::STATUS_QR_REQUIRED]);
    }

    public function getQr(Organization $organization): ?string
    {
        return $this->messageService->provider()->getQr($organization);
    }

    public function getStatus(Organization $organization): array
    {
        $status = $this->messageService->provider()->getStatus($organization);
        $connected = (bool) ($status['connected'] ?? false);
        $bridgeStatus = $status['status'] ?? WhatsappConnection::STATUS_DISCONNECTED;

        return [
            'connected' => $connected,
            'phone' => $connected ? ($status['phone'] ?? null) : null,
            'status' => $bridgeStatus,
            'display_status' => WhatsappConnection::formatBridgeStatus($bridgeStatus, $connected),
            'needs_qr' => in_array($bridgeStatus, ['qr_required', 'reconnecting', 'disconnected'], true) && ! $connected,
            'connected_at' => $organization->fresh()->whatsappConnection?->connected_at?->toIso8601String(),
            'disconnected_at' => $organization->fresh()->whatsappConnection?->disconnected_at?->toIso8601String(),
        ];
    }

    public function disconnect(Organization $organization): void
    {
        $this->messageService->provider()->disconnect($organization);
    }

    public function isConnected(Organization $organization): bool
    {
        $status = $this->getStatus($organization);

        return (bool) ($status['connected'] ?? false);
    }
}
