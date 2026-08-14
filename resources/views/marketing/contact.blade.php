@extends('layouts.marketing')

@section('title', 'Contact | ConnectPulse')
@section('meta_description', 'Contact ConnectPulse for CRM access, sales workspace setup, API integration and billing.')

@section('content')
<section class="mkt-hero !pb-12">
    <div class="mkt-hero__glow"></div>
    <div class="mkt-wrap relative mkt-reveal">
        <p class="mkt-eyebrow">Contact</p>
        <h1 class="mt-3 mkt-display text-white">Let's get you<br><span class="mkt-gradient-text">started.</span></h1>
    </div>
</section>

<section class="mkt-section mkt-section--soft pt-0" data-nav-light>
    <div class="mkt-wrap">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16 mkt-reveal">
            <div>
                <p class="text-lg leading-relaxed text-slate-600">
                    Start with ConnectPulse CRM — manage leads, follow-ups, WhatsApp and your pipeline. We'll help you get set up.
                </p>
                <div class="mt-10 space-y-6">
                    <div class="flex gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#635bff]/10 text-[#635bff]">✉</div>
                        <div>
                            <p class="font-semibold text-slate-900">Email</p>
                            <a href="mailto:{{ config('connectpulse.business.support_email') }}" class="text-[#635bff] hover:underline">{{ config('connectpulse.business.support_email') }}</a>
                        </div>
                    </div>
                    @if(config('connectpulse.business.support_phone'))
                    <div class="flex gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#635bff]/10 text-[#635bff]">📞</div>
                        <div>
                            <p class="font-semibold text-slate-900">Phone</p>
                            <p class="text-slate-600">{{ config('connectpulse.business.support_phone') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="mt-10 rounded-2xl border border-[#635bff]/20 bg-[#635bff]/5 p-6">
                    <p class="font-semibold text-slate-900">Already a customer?</p>
                    <p class="mt-1 text-sm text-slate-600">Sign in to manage leads, follow-ups and WhatsApp.</p>
                    <a href="{{ route('login') }}" class="mkt-btn mkt-btn--primary mt-4 !px-5 !py-2">Customer Login</a>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg">
                <h2 class="text-xl font-bold text-slate-900">Send us a message</h2>
                <p class="mt-2 text-sm text-slate-500">We'll respond within 1–2 business days.</p>
                <form action="mailto:{{ config('connectpulse.business.support_email') }}" method="GET" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Your name</label>
                        <input type="text" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#635bff] focus:ring-2 focus:ring-[#635bff]/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Business name</label>
                        <input type="text" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#635bff] focus:ring-2 focus:ring-[#635bff]/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">What do you need?</label>
                        <select class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-[#635bff] focus:ring-2 focus:ring-[#635bff]/20">
                            <option>New CRM account</option>
                            <option>Follow-up & sales workflow</option>
                            <option>API integration</option>
                            <option>Billing / recharge</option>
                            <option>Technical support</option>
                        </select>
                    </div>
                    <a href="mailto:{{ config('connectpulse.business.support_email') }}?subject=ConnectPulse%20Enquiry" class="mkt-btn mkt-btn--primary block w-full text-center">Email us</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
