@php
    $business = config('connectpulse.business.legal_name');
    $email = config('connectpulse.business.support_email');
@endphp

@extends('layouts.marketing')

@section('title', 'Refund Policy')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">Refund & Cancellation Policy</h1>
        <p class="text-sm text-slate-500 mt-2">Last updated: {{ date('F j, Y') }}</p>

        <div class="mt-8 space-y-6 text-sm text-slate-600 leading-relaxed">
            <p>This Refund Policy applies to credit purchases made on the ConnectPulse platform operated by {{ $business }}.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">1. Nature of product</h2>
            <p>ConnectPulse sells prepaid digital messaging credits. Credits are consumed when messages are accepted into our delivery queue. This is a digital service delivered immediately upon successful payment.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">2. Refund eligibility</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Unused credits:</strong> Refund requests for unused credits may be considered within 7 days of purchase, subject to a processing fee and verification of account status.</li>
                <li><strong>Duplicate payments:</strong> Accidental duplicate transactions will be refunded in full after verification.</li>
                <li><strong>Service failure:</strong> If a platform outage prevents message delivery for an extended period, we may offer credit compensation or partial refund at our discretion.</li>
                <li><strong>Used credits:</strong> Credits that have been used to send messages are non-refundable.</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">3. Non-refundable situations</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Credits used for sent or queued messages</li>
                <li>Account suspension or termination due to Terms of Service violations</li>
                <li>WhatsApp number bans or restrictions caused by customer messaging practices</li>
                <li>Change of mind after credits have been partially or fully used</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">4. How to request a refund</h2>
            <p>Email {{ $email }} with your organization name, transaction ID / payment reference, date of purchase, and reason for the request. We will respond within 5 business days.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">5. Refund processing</h2>
            <p>Approved refunds are processed to the original payment method within 7–10 business days. Razorpay / bank processing times may apply.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">6. Cancellation</h2>
            <p>You may close your account at any time by contacting support. Unused credits at the time of closure will be handled per this policy. No automatic recurring subscriptions apply — all purchases are one-time credit top-ups unless otherwise agreed in writing.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">7. Contact</h2>
            <p>{{ $email }} | {{ config('connectpulse.business.support_phone') }}</p>
        </div>
    </div>
</section>
@endsection
