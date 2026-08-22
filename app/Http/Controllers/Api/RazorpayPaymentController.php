<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayPaymentController extends Controller
{
    public function __construct(
        private RazorpayService $razorpayService,
    ) {}

    public function createOrder(Request $request): JsonResponse
    {
        if (! $this->razorpayService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured.',
            ], 500);
        }

        $validated = $request->validate([
            'plan_index' => ['required', 'integer', 'min:0', 'max:'.max(count(config('connectpulse.pricing', [])) - 1, 0)],
        ]);

        try {
            $paymentOrder = $this->razorpayService->createCheckoutOrder(
                $request->user()->organization,
                $request->user(),
                (int) $validated['plan_index'],
            );
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }

        $organization = $request->user()->organization;

        return response()->json([
            'success' => true,
            'order_id' => $paymentOrder->razorpay_order_id,
            'amount' => $paymentOrder->amount_paise,
            'currency' => 'INR',
            'key' => config('connectpulse.razorpay.key_id'),
            'name' => config('connectpulse.business.product_name'),
            'description' => $paymentOrder->plan_label.' — '.number_format($paymentOrder->credits).' credits',
            'prefill' => [
                'name' => $organization->contact_person,
                'email' => $organization->email,
                'contact' => $organization->mobile,
            ],
        ]);
    }

    public function verifyPayment(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if (! $this->razorpayService->verifyPaymentSignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        )) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed. Signature mismatch.',
                ], 400);
            }

            return redirect()
                ->route('org.recharge.index')
                ->with('error', 'Payment verification failed. Please contact support if amount was deducted.');
        }

        try {
            $paymentOrder = $this->razorpayService->fulfillFromCheckout(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature'],
            );
        } catch (\RuntimeException $e) {
            Log::warning('Razorpay payment fulfillment failed', [
                'order_id' => $validated['razorpay_order_id'],
                'payment_id' => $validated['razorpay_payment_id'],
                'message' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()
                ->route('org.recharge.index')
                ->with('error', $e->getMessage());
        }

        $message = number_format($paymentOrder->credits).' credits added to your wallet.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'credits_added' => $paymentOrder->credits,
            ]);
        }

        return redirect()
            ->route('org.recharge.index')
            ->with('success', $message);
    }
}
