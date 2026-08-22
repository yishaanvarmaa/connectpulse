<?php

namespace App\Services\Payment;

use App\Models\Organization;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class RazorpayService
{
    private const API_BASE = 'https://api.razorpay.com/v1';

    public function __construct(
        private CreditService $creditService,
    ) {}

    public function isConfigured(): bool
    {
        return filled(config('connectpulse.razorpay.key_id'))
            && filled(config('connectpulse.razorpay.key_secret'));
    }

    /**
     * @return array{price: int, credits: int, label: string, popular?: bool}
     */
    public function getPlan(int $planIndex): array
    {
        $plans = config('connectpulse.pricing', []);

        if (! isset($plans[$planIndex])) {
            throw new RuntimeException('Invalid recharge plan selected.');
        }

        return $plans[$planIndex];
    }

    public function createCheckoutOrder(Organization $organization, User $user, int $planIndex): PaymentOrder
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Payment gateway is not configured. Please contact support.');
        }

        $plan = $this->getPlan($planIndex);
        $amountPaise = (int) $plan['price'] * 100;

        if ($amountPaise < 100) {
            throw new RuntimeException('Minimum payment amount is ₹1.', 400);
        }

        $receipt = 'cp_'.Str::lower(Str::random(16));

        $response = Http::withBasicAuth(
            config('connectpulse.razorpay.key_id'),
            config('connectpulse.razorpay.key_secret'),
        )->post(self::API_BASE.'/orders', [
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
            'notes' => [
                'organization_id' => (string) $organization->id,
                'user_id' => (string) $user->id,
                'plan_index' => (string) $planIndex,
                'credits' => (string) $plan['credits'],
            ],
        ]);

        if ($response->status() === 401) {
            throw new RuntimeException('Payment gateway authentication failed.', 401);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Unable to create payment order. Please try again.', 500);
        }

        $orderData = $response->json();

        return PaymentOrder::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'plan_index' => $planIndex,
            'amount_paise' => $amountPaise,
            'credits' => (int) $plan['credits'],
            'plan_label' => $plan['label'],
            'razorpay_order_id' => $orderData['id'],
            'status' => PaymentOrder::STATUS_PENDING,
        ]);
    }

    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = config('connectpulse.razorpay.key_secret');

        if (! filled($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = config('connectpulse.razorpay.webhook_secret');

        if (! filled($secret) || ! filled($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function fulfillFromCheckout(string $orderId, string $paymentId, string $signature): PaymentOrder
    {
        if (! $this->verifyPaymentSignature($orderId, $paymentId, $signature)) {
            throw new RuntimeException('Payment verification failed.');
        }

        return $this->markOrderPaid($orderId, $paymentId);
    }

    public function fulfillFromWebhook(string $orderId, string $paymentId): ?PaymentOrder
    {
        $paymentOrder = PaymentOrder::where('razorpay_order_id', $orderId)->first();

        if (! $paymentOrder) {
            return null;
        }

        if ($paymentOrder->isPaid()) {
            return $paymentOrder;
        }

        return $this->markOrderPaid($orderId, $paymentId);
    }

    public function markOrderFailed(string $orderId, ?string $reason = null): ?PaymentOrder
    {
        $paymentOrder = PaymentOrder::where('razorpay_order_id', $orderId)->first();

        if (! $paymentOrder || $paymentOrder->isPaid()) {
            return $paymentOrder;
        }

        $paymentOrder->update([
            'status' => PaymentOrder::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);

        return $paymentOrder->fresh();
    }

    private function markOrderPaid(string $orderId, string $paymentId): PaymentOrder
    {
        return DB::transaction(function () use ($orderId, $paymentId) {
            $paymentOrder = PaymentOrder::where('razorpay_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $paymentOrder) {
                throw new RuntimeException('Payment order not found.');
            }

            if ($paymentOrder->isPaid()) {
                return $paymentOrder;
            }

            $existingPayment = PaymentOrder::where('razorpay_payment_id', $paymentId)
                ->where('id', '!=', $paymentOrder->id)
                ->exists();

            if ($existingPayment) {
                throw new RuntimeException('This payment has already been processed.');
            }

            $transaction = $this->creditService->addCredits(
                $paymentOrder->organization,
                $paymentOrder->credits,
                "Razorpay payment {$paymentId} ({$paymentOrder->plan_label})",
                $paymentOrder->user_id,
            );

            $paymentOrder->update([
                'status' => PaymentOrder::STATUS_PAID,
                'razorpay_payment_id' => $paymentId,
                'credit_transaction_id' => $transaction->id,
                'paid_at' => now(),
            ]);

            return $paymentOrder->fresh(['creditTransaction']);
        });
    }
}
