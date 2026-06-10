<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\CreditService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
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

        if (! $organization->whatsappConnection?->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp not connected.',
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

        Log::info('ConnectPulse API message queued', [
            'organization_id' => $organization->id,
            'message_log_id' => $log->id,
            'mobile' => $log->mobile,
        ]);

        return response()->json([
            'success' => true,
            'message_id' => (string) $log->id,
        ]);
    }

    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:1000'],
            'messages.*.mobile' => ['required', 'string', 'min:10', 'max:15'],
            'messages.*.message' => ['required', 'string', 'max:4096'],
        ]);

        /** @var Organization $organization */
        $organization = $request->attributes->get('organization');

        if (! $organization->whatsappConnection?->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp not connected.',
            ], 422);
        }

        $messageCount = count($validated['messages']);

        if (! $this->creditService->hasCredits($organization, $messageCount)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits.',
            ], 402);
        }

        $result = $this->messageService->queueBulk($organization, $validated['messages']);

        Log::info('ConnectPulse API bulk messages queued', [
            'organization_id' => $organization->id,
            'batch_id' => $result['batch_id'],
            'queued' => $result['queued'],
        ]);

        return response()->json([
            'success' => true,
            'batch_id' => $result['batch_id'],
            'queued' => $result['queued'],
        ]);
    }
}
