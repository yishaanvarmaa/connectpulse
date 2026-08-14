@extends('layouts.marketing')

@section('title', 'Pricing | ConnectPulse CRM + WhatsApp')
@section('meta_description', 'ConnectPulse CRM plans and WhatsApp messaging credits. Pay-as-you-go message packs from ₹1,000. 1 credit = 1 WhatsApp message.')

@section('content')
<section class="bg-gradient-to-b from-slate-50 to-white py-16 lg:py-20">
    <div class="mkt-container text-center">
        <p class="mkt-eyebrow">Pricing</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-slate-900">Simple pricing for sales teams.</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
            ConnectPulse CRM for lead management and follow-ups, plus pay-as-you-go WhatsApp messaging credits.
        </p>
    </div>
</section>

{{-- CRM PLANS --}}
<section class="pb-16">
    <div class="mkt-container">
        <div class="mx-auto max-w-3xl">
            <h2 class="text-2xl font-bold text-slate-900 text-center">ConnectPulse CRM</h2>
            <p class="mt-2 text-center text-slate-600">Leads, follow-ups, pipeline, inbox and reports — in one workspace.</p>

            <div class="mt-8 rounded-2xl border-2 border-brand-200 bg-gradient-to-br from-brand-50 to-white p-8 sm:p-10 text-center shadow-sm">
                <span class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold text-brand-700">Coming soon</span>
                <p class="mt-4 text-3xl font-bold text-slate-900">CRM subscription plans</p>
                <p class="mt-3 text-slate-600 max-w-md mx-auto">
                    We're finalizing CRM subscription pricing. Contact us to get early access or discuss your team's needs.
                </p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('contact') }}" class="mkt-btn-primary">Talk to Sales</a>
                    <a href="{{ route('login') }}" class="mkt-btn-secondary">Customer Login</a>
                </div>
                <ul class="mt-8 grid gap-2 text-left text-sm text-slate-700 sm:grid-cols-2">
                    @foreach(['Unlimited leads', 'Follow-up workspace', 'Sales pipeline', 'WhatsApp inbox', 'Activity timeline', 'Reports & search', 'Mobile CRM', 'Team workspace'] as $f)
                        <li class="flex gap-2"><span class="text-emerald-600">✓</span>{{ $f }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- WHATSAPP CREDITS --}}
<section id="whatsapp-messaging" class="pb-20 bg-slate-50 pt-4">
    <div class="mkt-container">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-slate-900">WhatsApp Messaging</h2>
            <p class="mt-2 text-slate-600">
                <strong>1 credit = 1 WhatsApp message.</strong> Recharge credits and send from your CRM or via REST API.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach($plans as $plan)
                <div class="relative flex flex-col rounded-2xl border {{ !empty($plan['popular']) ? 'border-brand-500 ring-2 ring-brand-500 shadow-xl bg-white' : 'border-slate-200 bg-white shadow-sm' }} p-6">
                    @if(!empty($plan['popular']))
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-brand-600 px-3 py-0.5 text-xs font-semibold text-white">Most Popular</span>
                    @endif
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">{{ $plan['label'] }}</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900">₹{{ number_format($plan['price']) }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-700">{{ number_format($plan['credits']) }} credits</p>
                    <p class="mt-1 text-xs text-slate-400">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }} per message</p>

                    <ul class="mt-6 flex-1 space-y-2 text-sm text-slate-600">
                        <li class="flex gap-2"><span class="text-emerald-500">✓</span> {{ number_format($plan['credits']) }} WhatsApp messages</li>
                        <li class="flex gap-2"><span class="text-emerald-500">✓</span> CRM + REST API access</li>
                        <li class="flex gap-2"><span class="text-emerald-500">✓</span> Message logs & inbox</li>
                        <li class="flex gap-2"><span class="text-emerald-500">✓</span> Credits never expire</li>
                    </ul>

                    <a href="{{ route('login') }}" class="mt-6 block rounded-lg px-4 py-2.5 text-center text-sm font-semibold transition {{ !empty($plan['popular']) ? 'bg-brand-600 text-white hover:bg-brand-700' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                        Recharge in Portal
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mx-auto mt-12 max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="font-semibold text-slate-900">Credit packs at a glance</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-slate-500">
                        <th class="px-6 py-3 font-medium">Amount</th>
                        <th class="px-6 py-3 font-medium">Credits</th>
                        <th class="hidden px-6 py-3 font-medium sm:table-cell">Per message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($plans as $plan)
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-900">₹{{ number_format($plan['price']) }}</td>
                            <td class="px-6 py-3">{{ number_format($plan['credits']) }}</td>
                            <td class="hidden px-6 py-3 text-slate-500 sm:table-cell">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mx-auto mt-10 max-w-3xl rounded-2xl border border-slate-200 bg-white p-8">
            <h3 class="text-lg font-semibold text-slate-900">What's included with messaging</h3>
            <ul class="mt-4 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                <li>✓ WhatsApp connection & session management</li>
                <li>✓ Send from CRM inbox or REST API</li>
                <li>✓ Single & bulk message API</li>
                <li>✓ Credit wallet & transaction history</li>
                <li>✓ Organization dashboard</li>
                <li>✓ Email support</li>
            </ul>
            <p class="mt-6 text-xs text-slate-500">
                Prices in Indian Rupees (INR). GST invoice available on request.
                New account? <a href="{{ route('contact') }}" class="text-brand-600 hover:underline">Contact us</a> to get started.
            </p>
        </div>
    </div>
</section>
@endsection
