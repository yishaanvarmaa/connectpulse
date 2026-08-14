<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        $organization->loadMissing('whatsappConnection');

        $status = $this->messageService->provider()->getStatus($organization);

        $connected = (bool) ($status['connected'] ?? false);

        return response()->json([
            'connected' => $connected,
            'phone' => $connected ? ($status['phone'] ?? null) : null,
            'display_name' => $connected ? ($organization->company_name ?? null) : null,
            'status' => $status['status'] ?? ($connected ? 'connected' : 'disconnected'),
        ]);
    }
}
