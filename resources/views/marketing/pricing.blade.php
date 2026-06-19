@extends('layouts.marketing')

@section('title', 'Pricing')
@section('meta_description', 'ConnectPulse recharge plans — prepaid WhatsApp message credits from ₹1,000. 1 credit = 1 message.')

@section('content')
<section class="py-16 lg:py-20 bg-gradient-to-b from-brand-50 to-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-slate-900">Recharge plans</h1>
        <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
            <strong>1 credit = 1 WhatsApp message.</strong> Choose a recharge pack, pay once, and use credits from your dashboard. Payments processed securely via Cashfree.
        </p>
    </div>
</section>

<section class="pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($plans as $plan)
                <div class="relative rounded-2xl border {{ !empty($plan['popular']) ? 'border-brand-500 ring-2 ring-brand-500 shadow-xl' : 'border-slate-200 shadow-sm' }} bg-white p-6 flex flex-col">
                    @if(!empty($plan['popular']))
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand-600 px-3 py-0.5 text-xs font-semibold text-white whitespace-nowrap">Most Popular</span>
                    @endif
                    <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">{{ $plan['label'] }}</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900">₹{{ number_format($plan['price']) }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-700">{{ number_format($plan['credits']) }} credits</p>
                    <p class="mt-1 text-xs text-slate-400">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }} per message</p>

                    <ul class="mt-6 space-y-2 text-sm text-slate-600 flex-1">
                        <li class="flex gap-2"><span class="text-green-500">✓</span> {{ number_format($plan['credits']) }} WhatsApp messages</li>
                        <li class="flex gap-2"><span class="text-green-500">✓</span> REST API access</li>
                        <li class="flex gap-2"><span class="text-green-500">✓</span> Message logs & dashboard</li>
                        <li class="flex gap-2"><span class="text-green-500">✓</span> Credits never expire</li>
                    </ul>

                    <a href="{{ route('login') }}"
                       class="mt-6 block text-center rounded-lg {{ !empty($plan['popular']) ? 'bg-brand-600 text-white hover:bg-brand-700' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }} px-4 py-2.5 text-sm font-semibold transition">
                        Recharge in Portal
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-12 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm max-w-2xl mx-auto">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="font-semibold text-slate-900">All plans at a glance</h2>
            </div>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-slate-500">
                        <th class="px-6 py-3 font-medium">Recharge amount</th>
                        <th class="px-6 py-3 font-medium">Credits</th>
                        <th class="px-6 py-3 font-medium hidden sm:table-cell">Per message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($plans as $plan)
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-900">₹{{ number_format($plan['price']) }}</td>
                            <td class="px-6 py-3">{{ number_format($plan['credits']) }} credits</td>
                            <td class="px-6 py-3 hidden sm:table-cell text-slate-500">≈ ₹{{ number_format($plan['price'] / $plan['credits'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-10 rounded-2xl bg-slate-50 border border-slate-200 p-8 max-w-3xl mx-auto">
            <h2 class="font-semibold text-slate-900 text-lg">What's included</h2>
            <ul class="mt-4 grid sm:grid-cols-2 gap-3 text-sm text-slate-600">
                <li>✓ WhatsApp connection & session management</li>
                <li>✓ Single & bulk message API</li>
                <li>✓ Credit wallet & transaction history</li>
                <li>✓ Organization dashboard</li>
                <li>✓ API key management</li>
                <li>✓ Email support</li>
            </ul>
            <p class="mt-6 text-xs text-slate-500">
                Prices in Indian Rupees (INR). GST invoice available on request where applicable.
                New account? <a href="{{ route('contact') }}" class="text-brand-600 hover:underline">Contact us</a> to get started.
            </p>
        </div>
    </div>
</section>
@endsection
