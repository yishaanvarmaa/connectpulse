<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function __construct(
        private WhatsAppConnectionService $whatsappService
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
        $result = $this->whatsappService->connect($organization);
        $status = $this->whatsappService->getStatus($organization);

        return response()->json([
            'success' => (bool) ($result['success'] ?? true),
            'error' => $result['error'] ?? null,
            'qr' => $result['qr'] ?? null,
            'status' => $status['status'] ?? null,
            'display_status' => $status['display_status'] ?? null,
        ]);
    }

    public function qr(Request $request): JsonResponse
    {
        $organization = $request->user()->organization;
        $qr = $this->whatsappService->getQr($organization);
        $status = $this->whatsappService->getStatus($organization);

        return response()->json([
            'qr' => $qr,
            'status' => $status['status'],
            'display_status' => $status['display_status'] ?? null,
            'error' => $qr ? null : 'QR not ready yet — keep this page open.',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json($this->whatsappService->getStatus($request->user()->organization));
    }

    public function disconnect(Request $request): JsonResponse
    {
        $this->whatsappService->disconnect($request->user()->organization);

        return response()->json(['success' => true]);
    }
}
