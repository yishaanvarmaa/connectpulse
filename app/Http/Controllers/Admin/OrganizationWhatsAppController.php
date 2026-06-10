<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\WhatsAppConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrganizationWhatsAppController extends Controller
{
    public function __construct(
        private WhatsAppConnectionService $whatsappService
    ) {}

    public function show(Organization $organization): View
    {
        $organization->load('whatsappConnection');

        return view('admin.organizations.whatsapp', [
            'organization' => $organization,
            'connection' => $organization->whatsappConnection,
        ]);
    }

    public function connect(Organization $organization): JsonResponse
    {
        $this->whatsappService->connect($organization);

        return response()->json(['success' => true]);
    }

    public function qr(Organization $organization): JsonResponse
    {
        $qr = $this->whatsappService->getQr($organization);
        $status = $this->whatsappService->getStatus($organization);

        return response()->json([
            'qr' => $qr,
            'status' => $status['status'],
        ]);
    }

    public function status(Organization $organization): JsonResponse
    {
        return response()->json($this->whatsappService->getStatus($organization));
    }

    public function disconnect(Organization $organization): JsonResponse
    {
        $this->whatsappService->disconnect($organization);

        return response()->json(['success' => true]);
    }
}
