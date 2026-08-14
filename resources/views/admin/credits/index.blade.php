@extends('layouts.admin')

@section('title', 'Credits')

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-6">Credit Management</h1>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Add Credits</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($organizations as $org)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $org->company_name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">{{ number_format($org->creditWallet?->balance ?? 0) }}</td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('admin.credits.store', $org) }}" class="inline-flex gap-2">
                            @csrf
                            <input name="amount" type="number" min="1" placeholder="Amount" required class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm w-28">
                            <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-medium text-white">Add</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
