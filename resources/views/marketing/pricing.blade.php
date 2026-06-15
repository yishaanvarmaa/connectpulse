@extends('layouts.marketing')

@section('title', 'Pricing')
@section('meta_description', 'ConnectPulse pricing — prepaid WhatsApp message credits. Pay only for messages you send.')

@section('content')
<section class="py-16 lg:py-20 bg-gradient-to-b from-brand-50 to-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold text-slate-900">Simple, pay-as-you-go pricing</h1>
        <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
            <strong>1 credit = 1 WhatsApp message.</strong> Recharge credits from your dashboard when you need them. No monthly subscription.
        </p>
    </div>
</section>

<section class="pb-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border-2 border-brand-500 bg-white p-8 lg:p-10 shadow-xl text-center">
            <p class="text-sm font-semibold text-brand-600 uppercase tracking-wide">Messaging credits</p>
            @if($creditPrice)
                <p class="mt-6 text-5xl font-extrabold text-slate-900">₹{{ number_format((float) $creditPrice, 2) }}</p>
                <p class="mt-2 text-slate-600">per credit (per WhatsApp message)</p>
            @else
                <p class="mt-6 text-2xl font-bold text-slate-900">Contact us for current rates</p>
                <p class="mt-2 text-slate-600">Pricing is set when your account is created. Existing customers see rates in the recharge portal.</p>
            @endif

            <ul class="mt-8 space-y-3 text-sm text-slate-600 text-left max-w-md mx-auto">
                <li class="flex gap-2"><span class="text-green-500">✓</span> Prepaid credits — pay only for what you send</li>
                <li class="flex gap-2"><span class="text-green-500">✓</span> Recharge anytime from your organization dashboard</li>
                <li class="flex gap-2"><span class="text-green-500">✓</span> REST API access included</li>
                <li class="flex gap-2"><span class="text-green-500">✓</span> Message logs & connection dashboard</li>
                <li class="flex gap-2"><span class="text-green-500">✓</span> WhatsApp session management included</li>
            </ul>

            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('contact') }}" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 transition">
                    Get an account
                </a>
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Customer login
                </a>
            </div>
        </div>

        <div class="mt-10 rounded-2xl bg-slate-50 border border-slate-200 p-8">
            <h2 class="font-semibold text-slate-900 text-lg">How billing works</h2>
            <ol class="mt-4 space-y-3 text-sm text-slate-600 list-decimal list-inside">
                <li>We create your organization account and set your credit rate.</li>
                <li>You recharge credits (online via Razorpay once enabled, or via admin until then).</li>
                <li>Each message accepted into the queue uses 1 credit.</li>
                <li>View balance and history anytime under <strong>Recharge</strong> in your portal.</li>
            </ol>
            <p class="mt-6 text-xs text-slate-500">
                GST invoice available on request where applicable.
                Volume or multi-centre pricing? <a href="{{ route('contact') }}" class="text-brand-600 hover:underline">Contact us</a>.
            </p>
        </div>
    </div>
</section>
@endsection
