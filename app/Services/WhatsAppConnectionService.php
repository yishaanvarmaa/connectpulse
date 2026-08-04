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

    public function connect(Organization $organization): array
    {
        $liveStatus = $this->messageService->provider()->getStatus($organization);

        if (in_array($liveStatus['status'] ?? '', ['reconnecting', 'qr_required'], true) && ! ($liveStatus['connected'] ?? false)) {
            $this->messageService->provider()->disconnect($organization);
        }

        $connection = $this->ensureConnection($organization);

        $init = $this->bridgeService->initSession((int) $organization->id);

        if (($init['success'] ?? false) === false) {
            $connection->update(['status' => WhatsappConnection::STATUS_DISCONNECTED]);

            return [
                'success' => false,
                'error' => $init['error'] ?? 'WhatsApp bridge failed to start. Contact support.',
                'qr' => null,
            ];
        }

        $qr = null;
        for ($attempt = 0; $attempt < 10; $attempt++) {
            usleep(500000);
            $qrResult = $this->bridgeService->getQr((int) $organization->id);
            if (! empty($qrResult['qr'])) {
                $qr = $qrResult['qr'];
                break;
            }
        }

        $connection->update([
            'status' => $qr
                ? WhatsappConnection::STATUS_QR_REQUIRED
                : WhatsappConnection::STATUS_QR_REQUIRED,
        ]);

        return [
            'success' => true,
            'error' => $qr ? null : 'QR is generating — wait a few seconds and keep this page open.',
            'qr' => $qr,
        ];
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

    public function resetCrypto(Organization $organization): array
    {
        $result = $this->bridgeService->resetCrypto((int) $organization->id);

        if ($connection = $organization->whatsappConnection) {
            $connection->update([
                'status' => WhatsappConnection::STATUS_DISCONNECTED,
                'phone_number' => null,
                'disconnected_at' => now(),
            ]);
        }

        return [
            'success' => (bool) ($result['success'] ?? false),
            'error' => $result['error'] ?? null,
        ];
    }

    public function isConnected(Organization $organization): bool
    {
        $status = $this->getStatus($organization);

        return (bool) ($status['connected'] ?? false);
    }
}
