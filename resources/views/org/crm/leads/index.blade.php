@extends('layouts.org')

@section('title', 'Leads')

@php
    $pageTitle = 'Leads';
    $pageSubtitle = $leads->total() . ' total';
@endphp

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex flex-1 flex-wrap gap-2">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search leads..." class="cp-input max-w-xs">
        <select name="status" class="cp-select w-auto" onchange="this.form.submit()">
            <option value="">All stages</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="source" class="cp-select w-auto" onchange="this.form.submit()">
            <option value="">All sources</option>
            @foreach($sources as $key => $label)
                <option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </form>
    <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary shrink-0">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Lead
    </button>
</div>

@if($leads->isEmpty())
    <x-ui.empty-state
        title="No leads yet"
        description="Start building your sales pipeline by adding your first lead."
    >
        <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary mt-4">+ New Lead</button>
    </x-ui.empty-state>
@else
    <div class="cp-table-wrap hidden md:block">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Lead</th>
                    <th>Product</th>
                    <th>Source</th>
                    <th>Stage</th>
                    <th>Value</th>
                    <th>Next follow-up</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($leads as $lead)
                    <tr class="group hover:bg-slate-50/80">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <x-ui.avatar :name="$lead->name" />
                                <div>
                                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $lead->phone }}@if($lead->company) · {{ $lead->company }}@endif</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-slate-600">{{ $lead->interested_product ?? '—' }}</td>
                        <td class="text-slate-600">{{ $lead->sourceLabel() }}</td>
                        <td><x-ui.badge type="brand">{{ $lead->statusLabel() }}</x-ui.badge></td>
                        <td class="font-medium">{{ $lead->estimated_value ? '₹'.number_format($lead->estimated_value, 0) : '—' }}</td>
                        <td class="text-slate-600">{{ $lead->next_follow_up_at?->format('M d, h:i A') ?? '—' }}</td>
                        <td>
                            <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('org.crm.leads.show', $lead) }}#whatsapp-form" class="cp-btn-success !px-2 !py-1 text-xs">WA</a>
                                <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-btn-secondary !px-2 !py-1 text-xs">View</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="space-y-2 md:hidden">
        @foreach($leads as $lead)
            <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-card block cp-card-body">
                <div class="flex items-start gap-3">
                    <x-ui.avatar :name="$lead->name" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium text-slate-900">{{ $lead->name }}</p>
                            @if($lead->estimated_value)<span class="text-sm font-semibold">₹{{ number_format($lead->estimated_value, 0) }}</span>@endif
                        </div>
                        <p class="text-xs text-slate-500">{{ $lead->interested_product ?? $lead->phone }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <x-ui.badge type="brand">{{ $lead->statusLabel() }}</x-ui.badge>
                            <x-ui.badge>{{ $lead->sourceLabel() }}</x-ui.badge>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
@endif

<x-crm.lead-slideover />
@endsection
