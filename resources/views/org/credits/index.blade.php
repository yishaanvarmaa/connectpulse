@extends('layouts.app')

@section('title', 'Credits')

@section('nav')
    <x-nav-link :href="route('org.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('org.whatsapp.index')">WhatsApp</x-nav-link>
    <x-nav-link :href="route('org.api-keys.index')">API Keys</x-nav-link>
    <x-nav-link :href="route('org.credits.index')" :active="true">Credits</x-nav-link>
    <x-nav-link :href="route('org.logs.index')">Logs</x-nav-link>
    <x-nav-link :href="route('org.settings.index')">Settings</x-nav-link>
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Credits</h1>
    <p class="mt-2 text-3xl font-semibold text-brand-600">{{ number_format($organization->creditWallet?->balance ?? 0) }} credits</p>
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-900">Recharge History</h2>
    </div>
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance After</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Remarks</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($transactions as $tx)
                <tr>
                    <td class="px-6 py-4 text-sm {{ $tx->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($tx->type) }}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">{{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->amount }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ number_format($tx->balance_after) }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $tx->remarks }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No transactions yet</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-200">{{ $transactions->links() }}</div>
</div>
@endsection
