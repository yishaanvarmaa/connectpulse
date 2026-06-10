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

        $status = $this->messageService->provider()->getStatus($organization);

        if (! ($status['connected'] ?? false)) {
            return response()->json([
                'connected' => false,
            ]);
        }

        return response()->json([
            'connected' => true,
            'phone' => $status['phone'] ?? $organization->whatsappConnection?->phone_number,
            'display_name' => $organization->company_name,
        ]);
    }
}
