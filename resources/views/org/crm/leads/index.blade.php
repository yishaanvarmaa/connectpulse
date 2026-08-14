@extends('layouts.app')

@section('title', 'Leads')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Leads</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $leads->total() }} total</p>
    </div>
    <a href="{{ route('org.crm.leads.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
        + New Lead
    </a>
</div>

<form method="GET" class="mb-6 rounded-xl bg-white border border-slate-200 p-4 shadow-sm space-y-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, phone, company..."
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="source" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">All sources</option>
            @foreach($sources as $key => $label)
                <option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="sort" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Latest first</option>
            <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest first</option>
            <option value="follow_up" @selected(($filters['sort'] ?? '') === 'follow_up')>Follow-up date</option>
            <option value="value" @selected(($filters['sort'] ?? '') === 'value')>Highest value</option>
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Apply filters</button>
</form>

<div class="space-y-3">
    @forelse($leads as $lead)
        <a href="{{ route('org.crm.leads.show', $lead) }}" class="block rounded-xl bg-white border border-slate-200 p-4 shadow-sm hover:border-brand-200 transition-colors">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $lead->name }}</p>
                    <p class="text-sm text-slate-500">{{ $lead->phone }}</p>
                    @if($lead->interested_product)
                        <p class="text-sm text-slate-600 mt-1">{{ $lead->interested_product }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    @if($lead->estimated_value)
                        <p class="text-sm font-semibold text-slate-900">₹{{ number_format($lead->estimated_value, 0) }}</p>
                    @endif
                    <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $lead->status === 'won' ? 'bg-green-100 text-green-700' : ($lead->status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-brand-50 text-brand-700') }}">
                        {{ $lead->statusLabel() }}
                    </span>
                </div>
            </div>
            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                <span>{{ $lead->sourceLabel() }}</span>
                @if($lead->next_follow_up_at)
                    <span>· Follow-up {{ $lead->next_follow_up_at->format('d M, h:i A') }}</span>
                @endif
                @if($lead->priority === 'high')
                    <span class="text-red-600 font-medium">· High priority</span>
                @endif
            </div>
        </a>
    @empty
        <div class="rounded-xl bg-white border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-500">No leads found.</p>
            <a href="{{ route('org.crm.leads.create') }}" class="mt-2 inline-block text-sm text-brand-600 underline">Add your first lead</a>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $leads->links() }}</div>

<x-crm-fab />
@endsection
