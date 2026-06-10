<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConnection;
use App\Services\MessageService;
use App\Services\WhatsAppBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private WhatsAppBridgeService $bridgeService,
    ) {}

    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $organization->load('whatsappConnection');

        return view('org.whatsapp.index', [
            'organization' => $organization,
            'connection' => $organization->whatsappConnection,
        ]);
    }

    public function connect(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;

        $connection = $organization->whatsappConnection ?? WhatsappConnection::create([
            'organization_id' => $organization->id,
            'status' => WhatsappConnection::STATUS_QR_REQUIRED,
            'session_path' => storage_path("app/whatsapp/{$organization->id}"),
        ]);

        $this->bridgeService->initSession($organization->id);
        $connection->update(['status' => WhatsappConnection::STATUS_QR_REQUIRED]);

        return response()->json(['success' => true]);
    }

    public function qr(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $qr = $this->messageService->provider()->getQr($organization);

        return response()->json([
            'qr' => $qr,
            'status' => $organization->whatsappConnection?->getClientStatus() ?? 'Disconnected',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $status = $this->messageService->provider()->getStatus($organization);

        return response()->json([
            'connected' => $status['connected'] ?? false,
            'phone' => $status['phone'] ?? null,
            'status' => $organization->fresh()->whatsappConnection?->getClientStatus() ?? 'Disconnected',
        ]);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $this->messageService->provider()->disconnect($organization);

        return response()->json(['success' => true]);
    }
}
