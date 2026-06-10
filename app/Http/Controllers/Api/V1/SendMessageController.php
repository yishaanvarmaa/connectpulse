<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\CreditService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendMessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private CreditService $creditService,
    ) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'message' => ['required', 'string', 'max:4096'],
        ]);

        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        $connection = $organization->whatsappConnection;
        if (! $connection?->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp is not connected. Please connect your WhatsApp account.',
            ], 422);
        }

        if (! $this->creditService->hasCredits($organization)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits.',
            ], 402);
        }

        $log = $this->messageService->queueMessage(
            $organization,
            $validated['mobile'],
            $validated['message']
        );

        return response()->json([
            'success' => true,
            'message_id' => (string) $log->id,
        ]);
    }

    public function sendBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:1000'],
            'messages.*.mobile' => ['required', 'string', 'min:10', 'max:15'],
            'messages.*.message' => ['required', 'string', 'max:4096'],
        ]);

        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        $connection = $organization->whatsappConnection;
        if (! $connection?->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp is not connected. Please connect your WhatsApp account.',
            ], 422);
        }

        $requiredCredits = count($validated['messages']);
        if (! $this->creditService->hasCredits($organization, $requiredCredits)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits.',
            ], 402);
        }

        $batchId = $this->messageService->queueBulk($organization, $validated['messages']);

        return response()->json([
            'success' => true,
            'batch_id' => $batchId,
            'queued' => count($validated['messages']),
        ]);
    }
}
