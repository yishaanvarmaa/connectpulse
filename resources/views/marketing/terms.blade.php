@php
    $business = config('connectpulse.business.legal_name');
    $product = config('connectpulse.business.product_name');
    $email = config('connectpulse.business.support_email');
    $gateway = config('connectpulse.business.payment_gateway');
    $website = config('connectpulse.business.website');
@endphp

@extends('layouts.marketing')

@section('title', 'Terms of Service')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">Terms of Service</h1>
        <p class="text-sm text-slate-500 mt-2">Last updated: {{ date('F j, Y') }}</p>

        <div class="mt-8 space-y-6 text-sm text-slate-600 leading-relaxed">
            <p>These Terms of Service ("Terms") govern your use of {{ $website }} and the {{ $product }} platform operated by {{ $business }}. By creating an account or using our services, you agree to these Terms.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">1. Service description</h2>
            <p>{{ $product }} provides a cloud-based platform that allows businesses to connect a WhatsApp Business number and send messages through a REST API using prepaid credits. The service includes a customer portal for WhatsApp connection, credit management, API keys, and message logs.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">2. Account registration</h2>
            <p>You must provide accurate business information when registering. You are responsible for maintaining the confidentiality of your login credentials and API keys. You must notify us immediately of any unauthorized access.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">3. Credits and payments</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Credits are prepaid and required to send messages. One (1) credit equals one (1) WhatsApp message accepted into our delivery queue.</li>
                <li>Prices are displayed on our <a href="{{ route('pricing') }}" class="text-brand-600 hover:underline">Pricing page</a> in Indian Rupees (INR).</li>
                <li>Payments are processed through {{ $gateway }} or other authorized payment gateways.</li>
                <li>Credits are non-transferable between organizations unless approved by {{ $business }}.</li>
                <li>Refunds are governed by our <a href="{{ route('refund') }}" class="text-brand-600 hover:underline">Refund Policy</a>.</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">4. Acceptable use</h2>
            <p>You agree NOT to use ConnectPulse for:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>Spam, unsolicited bulk messaging, or messages without recipient consent</li>
                <li>Illegal, fraudulent, harassing, or abusive content</li>
                <li>Content that violates WhatsApp's terms of service or Meta policies</li>
                <li>Attempting to reverse-engineer, disrupt, or overload our systems</li>
                <li>Sharing API credentials with unauthorized third parties</li>
            </ul>
            <p>We reserve the right to suspend or terminate accounts that violate these rules without refund.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">5. WhatsApp connection</h2>
            <p>You are responsible for connecting a WhatsApp number you own or are authorized to use. {{ $product }} is not affiliated with WhatsApp or Meta. Service availability depends on WhatsApp's platform and your connection status. We are not liable for disruptions caused by WhatsApp policy changes or account bans resulting from your messaging practices.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">6. Service availability</h2>
            <p>We strive for high uptime but do not guarantee uninterrupted service. Scheduled maintenance will be communicated where possible. Message delivery depends on WhatsApp network conditions and recipient availability.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">7. Limitation of liability</h2>
            <p>To the maximum extent permitted by law, {{ $business }} shall not be liable for indirect, incidental, or consequential damages. Our total liability for any claim shall not exceed the amount you paid to {{ $business }} in the three (3) months preceding the claim.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">8. Termination</h2>
            <p>Either party may terminate the account with written notice. Upon termination, unused credits may be handled per our Refund Policy. We may immediately suspend accounts for Terms violations.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">9. Governing law</h2>
            <p>These Terms are governed by the laws of India. Disputes shall be subject to the exclusive jurisdiction of courts in India.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">10. Contact</h2>
            <p>Questions about these Terms: {{ $email }}</p>
        </div>
    </div>
</section>
@endsection
