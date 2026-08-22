<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        private RazorpayService $razorpayService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (! $this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Razorpay webhook rejected: invalid signature');

            return response('Invalid signature', 400);
        }

        $event = $request->json()->all();
        $eventType = $event['event'] ?? null;
        $entity = $event['payload']['payment']['entity'] ?? null;

        if (! is_array($entity)) {
            return response('OK', 200);
        }

        $orderId = $entity['order_id'] ?? null;
        $paymentId = $entity['id'] ?? null;

        if (! filled($orderId) || ! filled($paymentId)) {
            return response('OK', 200);
        }

        try {
            if ($eventType === 'payment.captured') {
                $this->razorpayService->fulfillFromWebhook($orderId, $paymentId);
            } elseif (in_array($eventType, ['payment.failed', 'payment.authorized'], true)) {
                if ($eventType === 'payment.failed') {
                    $reason = $entity['error_description'] ?? $entity['error_reason'] ?? 'Payment failed';
                    $this->razorpayService->markOrderFailed($orderId, $reason);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Razorpay webhook processing failed', [
                'event' => $eventType,
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
            ]);

            return response('Processing failed', 500);
        }

        return response('OK', 200);
    }
}
