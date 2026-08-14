@extends('layouts.marketing')

@section('title', 'Pricing | ConnectPulse CRM + WhatsApp')
@section('meta_description', 'ConnectPulse CRM plans and WhatsApp messaging credits. Pay-as-you-go credit packs from ₹1,000.')

@section('content')
<section class="mkt-hero !pb-12">
    <div class="mkt-hero__glow"></div>
    <div class="mkt-wrap relative text-center mkt-reveal">
        <p class="mkt-eyebrow">Pricing</p>
        <h1 class="mkt-display mt-4 text-white">CRM + messaging.<br><span class="mkt-gradient-text">Simple pricing.</span></h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-400">
            A sales workspace for your team, plus pay-as-you-go WhatsApp credits when you need them.
        </p>
    </div>
</section>

{{-- CRM --}}
<section class="mkt-section mkt-section--soft pt-0" data-nav-light>
    <div class="mkt-wrap">
        <div class="mkt-reveal mx-auto max-w-3xl">
            <p class="mkt-eyebrow mkt-eyebrow--light text-center">ConnectPulse CRM</p>
            <div class="mkt-pricing-dark mt-6 !border-slate-200 !bg-white text-slate-900 shadow-xl">
                <div class="text-center">
                    <span class="inline-flex rounded-full bg-[#635bff]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-[#635bff]">Plans coming soon</span>
                    <h2 class="mt-6 text-3xl font-extrabold text-slate-900">Full sales workspace</h2>
                    <p class="mx-auto mt-3 max-w-md text-slate-600">Leads, follow-ups, pipeline, WhatsApp inbox, reports and mobile CRM — everything in one place.</p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('contact') }}" class="mkt-btn mkt-btn--primary">Talk to Sales</a>
                        <a href="{{ route('login') }}" class="mkt-btn mkt-btn--ghost-dark">Customer Login</a>
                    </div>
                </div>
                <ul class="mt-10 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                    @foreach(['Unlimited leads', 'Follow-up workspace', 'Sales pipeline', 'WhatsApp inbox', 'Activity timeline', 'Reports & search', 'Mobile CRM', 'Team workspace'] as $f)
                        <li class="flex gap-2"><span class="text-[#635bff]">✓</span>{{ $f }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- WHATSAPP CREDITS --}}
<section id="whatsapp-messaging" class="mkt-section mkt-section--dark">
    <div class="mkt-wrap">
        <div class="mkt-reveal text-center">
            <p class="mkt-eyebrow">WhatsApp Messaging</p>
            <h2 class="mt-3 mkt-h2 text-white">Pay-as-you-go credits.</h2>
            <p class="mx-auto mt-4 max-w-xl mkt-body"><strong class="text-white">1 credit = 1 WhatsApp message.</strong> Recharge and send from your CRM or REST API.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4 mkt-reveal">
            @foreach($plans as $plan)
                <div class="mkt-pricing-card {{ !empty($plan['popular']) ? 'mkt-pricing-card--featured !bg-white' : '!border-white/10 !bg-[#12162a] !text-white' }}">
                    @if(!empty($plan['popular']))
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-[#635bff] px-3 py-0.5 text-xs font-bold text-white">Most Popular</span>
                    @endif
                    <p class="text-xs font-bold uppercase tracking-wider {{ !empty($plan['popular']) ? 'text-[#635bff]' : 'text-[#8b7cff]' }}">{{ $plan['label'] }}</p>
                    <p class="mt-4 text-4xl font-extrabold">₹{{ number_format($plan['price']) }}</p>
                    <p class="mt-1 text-lg font-semibold {{ !empty($plan['popular']) ? 'text-slate-700' : 'text-slate-300' }}">{{ number_format($plan['credits']) }} credits</p>
                    <p class="mt-1 text-xs {{ !empty($plan['popular']) ? 'text-slate-400' : 'text-slate-500' }}">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }}/msg</p>
                    <ul class="mt-6 flex-1 space-y-2 text-sm {{ !empty($plan['popular']) ? 'text-slate-600' : 'text-slate-400' }}">
                        <li>✓ {{ number_format($plan['credits']) }} messages</li>
                        <li>✓ CRM + API access</li>
                        <li>✓ Never expires</li>
                    </ul>
                    <a href="{{ route('login') }}" class="mt-6 block rounded-xl px-4 py-3 text-center text-sm font-semibold transition {{ !empty($plan['popular']) ? 'bg-[#635bff] text-white hover:bg-[#5b4bff]' : 'border border-white/20 text-white hover:bg-white/5' }}">
                        Recharge in Portal
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mx-auto mt-12 max-w-2xl overflow-hidden rounded-2xl border border-white/10 bg-[#12162a] mkt-reveal">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 text-left text-slate-500">
                        <th class="px-6 py-3 font-medium">Amount</th>
                        <th class="px-6 py-3 font-medium">Credits</th>
                        <th class="hidden px-6 py-3 font-medium sm:table-cell">Per message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @foreach($plans as $plan)
                        <tr>
                            <td class="px-6 py-3 font-semibold text-white">₹{{ number_format($plan['price']) }}</td>
                            <td class="px-6 py-3">{{ number_format($plan['credits']) }}</td>
                            <td class="hidden px-6 py-3 text-slate-500 sm:table-cell">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="mkt-cta-section !py-16">
    <div class="mkt-cta-glow"></div>
    <div class="mkt-wrap relative text-center mkt-reveal">
        <h2 class="text-2xl font-bold text-white sm:text-3xl">Ready to get started?</h2>
        <a href="{{ route('contact') }}" class="mkt-btn mkt-btn--primary mt-6">Contact Sales</a>
    </div>
</section>
@endsection
