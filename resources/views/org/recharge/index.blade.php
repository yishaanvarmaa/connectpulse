@extends('layouts.org')

@section('title', 'Credits')

@php
    $balance = $organization->creditWallet?->balance ?? 0;
    $pageTitle = 'Credits';
    $pageSubtitle = number_format($balance).' available';
@endphp

@section('content')
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 mb-5">
    <div class="cp-card cp-card-body lg:col-span-1">
        <p class="text-xs font-medium text-slate-500">Available credits</p>
        <p class="mt-2 text-4xl font-semibold tracking-tight text-brand-600">{{ number_format($balance) }}</p>
        <p class="mt-2 text-xs text-slate-400">1 credit = 1 WhatsApp message</p>
        <a href="mailto:support@connectpulse.cloud?subject=Credit%20Recharge%20-%20{{ urlencode($organization->company_name) }}" class="cp-btn-primary mt-5 w-full">Request recharge</a>
    </div>

    <div class="cp-card cp-card-body lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-900 mb-1">Recharge plans</h2>
        <p class="text-xs text-slate-500 mb-4">Contact support or visit <a href="{{ route('pricing') }}" class="text-brand-600 hover:underline">pricing</a> to add credits.</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach(config('connectpulse.pricing') as $plan)
                <div class="rounded-xl border {{ ($plan['popular'] ?? false) ? 'border-brand-300 bg-brand-50/50 ring-1 ring-brand-200' : 'border-slate-200 bg-white' }} p-4 text-center">
                    @if($plan['popular'] ?? false)<p class="text-[10px] font-semibold uppercase text-brand-600 mb-1">Popular</p>@endif
                    <p class="text-lg font-bold text-slate-900">₹{{ number_format($plan['price']) }}</p>
                    <p class="text-xs text-slate-500">{{ number_format($plan['credits']) }} credits</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="cp-table-wrap">
    <div class="cp-card-header border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-900">Transaction history</h2>
    </div>
    <table class="cp-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance after</th>
                <th>Remarks</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($transactions as $tx)
                <tr class="hover:bg-slate-50/80">
                    <td><x-ui.badge :type="$tx->type === 'credit' ? 'success' : 'danger'">{{ ucfirst($tx->type) }}</x-ui.badge></td>
                    <td class="font-medium">{{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->amount }}</td>
                    <td>{{ number_format($tx->balance_after) }}</td>
                    <td class="text-slate-500 max-w-xs truncate">{{ $tx->remarks }}</td>
                    <td class="text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-sm text-slate-500">No transactions yet</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="border-t border-slate-100 px-4 py-3">{{ $transactions->links() }}</div>
</div>
@endsection
