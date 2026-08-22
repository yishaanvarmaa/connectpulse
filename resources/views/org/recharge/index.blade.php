@extends('layouts.org')

@section('title', 'Credits')

@section('page-title', 'Credits')
@section('page-subtitle', number_format($organization->creditWallet?->balance ?? 0).' available')

@section('content')
@if($razorpayEnabled)
<div id="recharge-payment-error" class="hidden mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
@endif
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 mb-5">
    <div class="cp-card cp-card-body lg:col-span-1">
        <p class="text-xs font-medium text-slate-500">Available credits</p>
        <p class="mt-2 text-4xl font-semibold tracking-tight text-brand-600">{{ number_format($organization->creditWallet?->balance ?? 0) }}</p>
        <p class="mt-2 text-xs text-slate-400">1 credit = 1 WhatsApp message</p>
        @if($razorpayEnabled)
            <p class="mt-4 text-xs text-slate-500">Choose a plan below to pay securely via Razorpay.</p>
        @else
            <a href="mailto:{{ config('connectpulse.business.support_email') }}?subject=Credit%20Recharge%20-%20{{ urlencode($organization->company_name) }}" class="cp-btn-primary mt-5 w-full">Contact support to recharge</a>
        @endif
    </div>

    <div class="cp-card cp-card-body lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Usage</h2>
        <div class="grid grid-cols-3 gap-3 mb-5">
            <div class="rounded-xl bg-slate-50 p-3 text-center">
                <p class="text-[10px] font-medium uppercase text-slate-500">Today</p>
                <p class="text-xl font-bold text-slate-900">{{ number_format($messagesToday) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 text-center">
                <p class="text-[10px] font-medium uppercase text-slate-500">This month</p>
                <p class="text-xl font-bold text-slate-900">{{ number_format($messagesMonth) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 text-center">
                <p class="text-[10px] font-medium uppercase text-slate-500">Credits used</p>
                <p class="text-xl font-bold text-slate-900">{{ number_format($usedThisMonth) }}</p>
            </div>
        </div>

        <h2 class="text-sm font-semibold text-slate-900 mb-1">Recharge plans</h2>
        <p class="text-xs text-slate-500 mb-4">
            @if($razorpayEnabled)
                Pay online — credits are added instantly after payment.
            @else
                Contact <a href="mailto:{{ config('connectpulse.business.support_email') }}" class="text-brand-600 hover:underline">{{ config('connectpulse.business.support_email') }}</a> or visit <a href="{{ route('pricing') }}" class="text-brand-600 hover:underline">pricing</a>.
            @endif
        </p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach(config('connectpulse.pricing') as $index => $plan)
                <div class="rounded-xl border {{ ($plan['popular'] ?? false) ? 'border-brand-300 bg-brand-50/50 ring-1 ring-brand-200' : 'border-slate-200 bg-white' }} p-4 text-center flex flex-col">
                    @if($plan['popular'] ?? false)<p class="text-[10px] font-semibold uppercase text-brand-600 mb-1">Popular</p>@endif
                    <p class="text-xs font-medium text-slate-500">{{ $plan['label'] }}</p>
                    <p class="text-lg font-bold text-slate-900 mt-1">₹{{ number_format($plan['price']) }}</p>
                    <p class="text-xs text-slate-500 mb-3">{{ number_format($plan['credits']) }} credits</p>
                    @if($razorpayEnabled)
                        <button
                            type="button"
                            class="cp-btn-primary mt-auto w-full text-xs py-2 js-recharge-plan"
                            data-plan-index="{{ $index }}"
                            data-plan-label="{{ $plan['label'] }}"
                        >
                            Pay ₹{{ number_format($plan['price']) }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="cp-table-wrap">
    <div class="cp-card-header border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-900">Credit history</h2>
    </div>
    <table class="cp-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance after</th>
                <th>Remarks</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($transactions as $tx)
                <tr class="hover:bg-slate-50/80">
                    <td><x-ui.badge :type="$tx->type === 'credit' ? 'success' : 'danger'">{{ ucfirst($tx->type) }}</x-ui.badge></td>
                    <td class="font-medium">{{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->amount }}</td>
                    <td>{{ number_format($tx->balance_after) }}</td>
                    <td class="text-slate-500 max-w-xs truncate">{{ $tx->remarks }}</td>
                    <td class="text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-sm text-slate-500">No transactions yet</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="border-t border-slate-100 px-4 py-3">{{ $transactions->links() }}</div>
</div>
@endsection

@if($razorpayEnabled)
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const createOrderUrl = @json(route('org.api.create-order'));
    const verifyPaymentUrl = @json(route('org.api.verify-payment'));
    const errorBox = document.getElementById('recharge-payment-error');
    let paying = false;

    function showError(message) {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function resetButton(button, originalText) {
        paying = false;
        button.disabled = false;
        button.textContent = originalText;
    }

    document.querySelectorAll('.js-recharge-plan').forEach(function (button) {
        button.addEventListener('click', async function () {
            if (paying) return;

            const planIndex = this.dataset.planIndex;
            paying = true;
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = 'Opening…';
            errorBox?.classList.add('hidden');

            try {
                const response = await fetch(createOrderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ plan_index: Number(planIndex) }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to start payment.');
                }

                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    description: data.description,
                    order_id: data.order_id,
                    prefill: data.prefill,
                    theme: { color: '#2563eb' },
                    handler: async function (paymentResponse) {
                        try {
                            const verifyResponse = await fetch(verifyPaymentUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    razorpay_order_id: paymentResponse.razorpay_order_id,
                                    razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                    razorpay_signature: paymentResponse.razorpay_signature,
                                }),
                            });

                            const verifyData = await verifyResponse.json();

                            if (!verifyResponse.ok || !verifyData.success) {
                                throw new Error(verifyData.message || 'Payment verification failed.');
                            }

                            window.location.reload();
                        } catch (verifyError) {
                            showError(verifyError.message || 'Payment verification failed.');
                            resetButton(button, originalText);
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            resetButton(button, originalText);
                        },
                    },
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (event) {
                    const reason = event.error?.description || event.error?.reason || 'Payment failed. Please try again.';
                    showError(reason);
                    resetButton(button, originalText);
                });
                rzp.open();
            } catch (error) {
                showError(error.message || 'Payment could not be started. Please try again.');
                resetButton(button, originalText);
            }
        });
    });
});
</script>
@endpush
@endif
