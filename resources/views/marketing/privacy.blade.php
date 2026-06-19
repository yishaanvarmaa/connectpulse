@php
    $business = config('connectpulse.business.legal_name');
    $product = config('connectpulse.business.product_name');
    $email = config('connectpulse.business.support_email');
    $gateway = config('connectpulse.business.payment_gateway');
    $website = config('connectpulse.business.website');
@endphp

@extends('layouts.marketing')

@section('title', 'Privacy Policy')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 prose prose-slate">
        <h1 class="text-3xl font-bold text-slate-900 not-prose">Privacy Policy</h1>
        <p class="text-sm text-slate-500 not-prose mt-2">Last updated: {{ date('F j, Y') }}</p>

        <div class="mt-8 space-y-6 text-sm text-slate-600 leading-relaxed">
            <p>{{ $business }} ("we", "us") operates {{ $product }} at {{ $website }}. {{ $product }} is a product of {{ $business }}. This Privacy Policy explains how we collect, use, and protect your information.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">1. Information we collect</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Account information:</strong> Company name, contact person, email, mobile number, and login credentials.</li>
                <li><strong>Payment information:</strong> Billing details processed securely through {{ $gateway }}. We do not store full card or UPI credentials on our servers.</li>
                <li><strong>Usage data:</strong> API requests, message logs (recipient numbers and message content sent via our platform), credit transactions, and WhatsApp connection status.</li>
                <li><strong>Technical data:</strong> IP address, browser type, and access logs for security and service improvement.</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">2. How we use your information</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>To provide and maintain the ConnectPulse messaging service</li>
                <li>To process credit recharges and generate invoices</li>
                <li>To deliver WhatsApp messages on your behalf through connected numbers</li>
                <li>To provide customer support and respond to enquiries</li>
                <li>To detect fraud, abuse, and ensure platform security</li>
                <li>To comply with applicable laws and regulations</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">3. WhatsApp and message data</h2>
            <p>When you connect your WhatsApp Business number, session data is stored securely on our servers solely to maintain your connection. Message content sent through our API is processed to deliver notifications to your customers. You are responsible for obtaining consent from recipients as required by applicable law and WhatsApp policies.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">4. Data sharing</h2>
            <p>We do not sell your personal data. We may share data with:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong>Payment processors</strong> (e.g. {{ $gateway }}) to process transactions</li>
                <li><strong>Infrastructure providers</strong> hosting our servers</li>
                <li><strong>Legal authorities</strong> when required by law</li>
            </ul>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">5. Data retention</h2>
            <p>Account and message log data is retained while your account is active and for a reasonable period thereafter for legal and audit purposes. You may request deletion of your account by contacting {{ $email }}.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">6. Security</h2>
            <p>We implement industry-standard security measures including encrypted API authentication, isolated WhatsApp sessions per organization, and secure server infrastructure. No method of transmission over the Internet is 100% secure.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">7. Your rights</h2>
            <p>You may request access, correction, or deletion of your personal data by emailing {{ $email }}. Indian users may have additional rights under applicable data protection laws.</p>

            <h2 class="text-lg font-semibold text-slate-900 pt-4">8. Contact</h2>
            <p>For privacy-related questions, contact us at {{ $email }}.</p>
        </div>
    </div>
</section>
@endsection
