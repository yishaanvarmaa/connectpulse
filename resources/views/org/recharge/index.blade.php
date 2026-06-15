@extends('layouts.app')

@section('title', 'Recharge')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Recharge Credits</h1>
    <p class="mt-1 text-sm text-slate-500">Manage your messaging credits for {{ $organization->company_name }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-1 rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Available Credits</p>
        <p class="mt-2 text-4xl font-bold text-brand-600">{{ number_format($organization->creditWallet?->balance ?? 0) }}</p>
        <p class="mt-4 text-xs text-slate-400">1 credit = 1 WhatsApp message</p>
    </div>

    <div class="lg:col-span-2 rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-2">Add Credits</h2>
        <p class="text-sm text-slate-500 mb-4">Recharge credits online from our <a href="{{ route('pricing') }}" class="text-brand-600 hover:underline">pricing page</a>. Razorpay payment integration coming soon — contact support for manual recharge in the meantime.</p>
        <div class="flex flex-wrap gap-3">
            <a href="mailto:support@connectpulse.cloud?subject=Credit%20Recharge%20-%20{{ urlencode($organization->company_name) }}"
               class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                Request Recharge
            </a>
            <button type="button" disabled
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-400 cursor-not-allowed"
                    title="Coming soon">
                Pay Online (Coming Soon)
            </button>
        </div>
        <p class="mt-4 text-xs text-slate-400">Invoice downloads will be available here in a future update.</p>
    </div>
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="font-semibold text-slate-900">Credit History</h2>
        <span class="text-xs text-slate-400">Invoices — coming soon</span>
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
