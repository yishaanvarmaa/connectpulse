@extends('layouts.marketing')

@section('title', 'Home')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-white to-white">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-brand-100 rounded-full blur-3xl opacity-60 translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-indigo-100 rounded-full blur-3xl opacity-50 -translate-x-1/2 translate-y-1/2"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-700 mb-6">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                    WhatsApp API for Indian Businesses
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Connect WhatsApp once.<br>
                    <span class="text-brand-600">Send messages everywhere.</span>
                </h1>
                <p class="mt-6 text-lg text-slate-600 leading-relaxed max-w-xl">
                    ConnectPulse is a centralized messaging platform. Link your WhatsApp Business number, recharge credits, and send report alerts, appointment reminders, and notifications through a simple REST API.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('contact') }}" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 shadow-lg shadow-brand-600/25 transition">
                        Request Account
                    </a>
                    <a href="{{ route('pricing') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        View Pricing
                    </a>
                </div>
                <p class="mt-6 text-sm text-slate-500">Trusted by diagnostics centres, clinics, and SaaS platforms across India.</p>
            </div>

            <div class="relative">
                <div class="rounded-2xl bg-white border border-slate-200 shadow-2xl shadow-slate-200/50 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-lg">✓</div>
                            <div>
                                <p class="font-semibold text-slate-900">WhatsApp Connected</p>
                                <p class="text-xs text-slate-500">+91 98765 43210</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Live</span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 font-mono text-xs text-slate-700 overflow-x-auto">
                        <p class="text-slate-400 mb-2">POST /api/v1/messages/send</p>
                        <p>{</p>
                        <p class="pl-4">"mobile": "9876543210",</p>
                        <p class="pl-4">"message": "Your report is ready."</p>
                        <p>}</p>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-green-600 font-medium">✓ Message queued</span>
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-500">1 credit used</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-slate-900">Everything you need to message at scale</h2>
            <p class="mt-4 text-slate-600">No Baileys setup, no server management. ConnectPulse handles WhatsApp infrastructure so your team can focus on your product.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['icon' => '🔗', 'title' => 'One-time WhatsApp setup', 'desc' => 'Scan a QR code in your dashboard. Your WhatsApp Business number stays connected 24/7 on our servers.'],
                ['icon' => '⚡', 'title' => 'Simple REST API', 'desc' => 'Send single or bulk messages with API key authentication. Integrate with Laravel, PHP, Node, or any HTTP client.'],
                ['icon' => '💳', 'title' => 'Pay-as-you-go credits', 'desc' => 'Recharge credits online. One credit equals one WhatsApp message. Transparent pricing with full transaction history.'],
                ['icon' => '🏢', 'title' => 'Multi-organization', 'desc' => 'Run multiple businesses or centres — each with its own WhatsApp number, API keys, and credit wallet.'],
                ['icon' => '📊', 'title' => 'Message logs & dashboard', 'desc' => 'Track sent messages, delivery status, credit usage, and connection health from a clean admin portal.'],
                ['icon' => '🔒', 'title' => 'Secure by design', 'desc' => 'API key + secret authentication, encrypted credentials, and isolated sessions per organization.'],
            ] as $feature)
                <div class="rounded-2xl border border-slate-200 p-6 hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50 transition">
                    <div class="text-3xl mb-4">{{ $feature['icon'] }}</div>
                    <h3 class="font-semibold text-slate-900 text-lg">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="py-20 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-bold text-slate-900">Up and running in minutes</h2>
            <p class="mt-4 text-slate-600">Three steps from signup to sending your first WhatsApp message.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['step' => '01', 'title' => 'Create your account', 'desc' => 'Contact us or get onboarded by our team. You receive portal access, API keys, and initial credits.'],
                ['step' => '02', 'title' => 'Connect WhatsApp', 'desc' => 'Log in to your dashboard, open WhatsApp Connection, and scan the QR code with your business number.'],
                ['step' => '03', 'title' => 'Integrate & send', 'desc' => 'Add our API to your app. Send report alerts, reminders, and notifications programmatically.'],
            ] as $item)
                <div class="relative rounded-2xl bg-white border border-slate-200 p-8">
                    <span class="text-4xl font-extrabold text-brand-100">{{ $item['step'] }}</span>
                    <h3 class="mt-4 font-semibold text-slate-900 text-lg">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Use cases --}}
<section class="py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-br from-brand-600 to-indigo-700 p-10 lg:p-16 text-white">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <h2 class="text-3xl font-bold">Built for businesses that message customers daily</h2>
                    <p class="mt-4 text-brand-100 leading-relaxed">Diagnostics labs, imaging centres, clinics, coaching institutes, and SaaS platforms use ConnectPulse to automate customer communication.</p>
                </div>
                <ul class="space-y-3 text-sm">
                    @foreach(['Lab report ready notifications', 'Appointment confirmations & reminders', 'Payment receipts', 'Registration welcome messages', 'Bulk campaign messaging via API'] as $use)
                        <li class="flex items-center gap-3">
                            <span class="h-6 w-6 rounded-full bg-white/20 flex items-center justify-center text-xs">✓</span>
                            {{ $use }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-slate-50">
    <div class="mx-auto max-w-3xl px-4 text-center">
        <h2 class="text-3xl font-bold text-slate-900">Ready to get started?</h2>
        <p class="mt-4 text-slate-600">Create your account, recharge credits, and start sending WhatsApp messages through our API today.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('contact') }}" class="rounded-xl bg-brand-600 px-8 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition">Contact Sales</a>
            <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-8 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Customer Login</a>
        </div>
    </div>
</section>
@endsection
