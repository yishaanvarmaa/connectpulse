@extends('layouts.marketing')

@section('title', 'Contact')
@section('meta_description', 'Contact ConnectPulse for account setup, API integration support, and billing enquiries.')

@section('content')
<section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
            <div>
                <h1 class="text-4xl font-bold text-slate-900">Get in touch</h1>
                <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                    Need a new account, help with API integration, or have a billing question? {{ config('connectpulse.business.product_name') }} is operated by {{ config('connectpulse.business.legal_name') }}.
                </p>

                <div class="mt-10 space-y-6">
                    <div class="flex gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">🏢</div>
                        <div>
                            <p class="font-semibold text-slate-900">Company</p>
                            <p class="text-slate-600">{{ config('connectpulse.business.legal_name') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">✉</div>
                        <div>
                            <p class="font-semibold text-slate-900">Email</p>
                            <a href="mailto:{{ config('connectpulse.business.support_email') }}" class="text-brand-600 hover:underline">
                                {{ config('connectpulse.business.support_email') }}
                            </a>
                        </div>
                    </div>
                    @if(config('connectpulse.business.support_phone'))
                    <div class="flex gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">📞</div>
                        <div>
                            <p class="font-semibold text-slate-900">Phone</p>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('connectpulse.business.support_phone')) }}" class="text-slate-600">
                                {{ config('connectpulse.business.support_phone') }}
                            </a>
                        </div>
                    </div>
                    @endif
                    <div class="flex gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">🌐</div>
                        <div>
                            <p class="font-semibold text-slate-900">Website</p>
                            <p class="text-slate-600">{{ config('connectpulse.business.website') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">📍</div>
                        <div>
                            <p class="font-semibold text-slate-900">Address</p>
                            <p class="text-slate-600">{{ config('connectpulse.business.address') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 rounded-xl bg-brand-50 border border-brand-100 p-6">
                    <p class="font-semibold text-brand-900">Already a customer?</p>
                    <p class="mt-1 text-sm text-brand-700">Sign in to your dashboard to connect WhatsApp, recharge credits, and manage API keys.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Customer Login
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-slate-900">Send us a message</h2>
                <p class="mt-2 text-sm text-slate-500">We'll respond within 1–2 business days.</p>

                <form action="mailto:{{ config('connectpulse.business.support_email') }}" method="GET" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Your name</label>
                        <input type="text" name="subject" placeholder="John from ABC Diagnostics"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Business name</label>
                        <input type="text" placeholder="Your company name"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">What do you need?</label>
                        <select class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option>New account setup</option>
                            <option>API integration help</option>
                            <option>Billing / recharge</option>
                            <option>Technical support</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <a href="mailto:{{ config('connectpulse.business.support_email') }}?subject=ConnectPulse%20Enquiry"
                       class="block w-full text-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                        Email {{ config('connectpulse.business.support_email') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
