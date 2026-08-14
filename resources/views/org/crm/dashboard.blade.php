@extends('layouts.org')

@section('title', 'CRM')

@php
    $pageTitle = 'CRM Overview';
    $pageSubtitle = $organization->company_name;
@endphp

@section('content')
<div class="mb-5 flex justify-end">
    <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Lead
    </button>
</div>

@if($stats['total_leads'] === 0)
    <x-ui.empty-state
        title="No leads yet"
        description="Your sales pipeline will appear here once you start adding leads from Facebook, Instagram, WhatsApp, or manual entry."
        actionLabel="+ Add your first lead"
        :actionHref="route('org.crm.leads.create')"
    >
        <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary mt-3">Quick add lead</button>
    </x-ui.empty-state>
@else
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5 mb-5">
        <x-ui.stat label="Total leads" :value="number_format($stats['total_leads'])" />
        <x-ui.stat label="Follow-ups today" :value="number_format($stats['follow_ups_today'])" />
        <x-ui.stat label="Overdue" :value="number_format($stats['overdue_follow_ups'])" />
        <x-ui.stat label="Pipeline value" :value="'₹'.number_format($stats['pipeline_value'], 0)" />
        <x-ui.stat label="Conversion" :value="$stats['conversion_rate'].'%'" />
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-5">
            @foreach(['overdue' => ['Overdue', 'danger'], 'today' => ['Today', 'warning'], 'upcoming' => ['Upcoming', 'brand']] as $key => [$label, $type])
                @if($followUpGroups[$key]->count() > 0)
                    <div class="cp-card">
                        <div class="cp-card-header">
                            <h2 class="text-sm font-semibold text-slate-900">{{ $label }}</h2>
                            <x-ui.badge :type="$type">{{ $followUpGroups[$key]->count() }}</x-ui.badge>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach($followUpGroups[$key]->take(5) as $followUp)
                                @php $lead = $followUp->lead; @endphp
                                <div class="flex items-center gap-3 px-4 py-3 sm:px-5">
                                    <span class="w-16 text-xs text-slate-500">{{ $followUp->scheduled_at->format('M d, h:i A') }}</span>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('org.crm.leads.show', $lead) }}" class="text-sm font-medium text-slate-900">{{ $lead->name }}</a>
                                        <p class="text-xs text-slate-500">{{ $lead->interested_product }}</p>
                                    </div>
                                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-btn-secondary !py-1.5 text-xs">Open</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="cp-card">
            <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">By source</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($sourceAnalytics as $row)
                    <div class="flex items-center justify-between px-4 py-3 sm:px-5">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $row['source_label'] }}</p>
                            <p class="text-xs text-slate-500">{{ $row['won'] }} won · {{ $row['conversion_rate'] }}%</p>
                        </div>
                        <span class="text-sm font-semibold">₹{{ number_format($row['revenue'], 0) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No source data yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endif

<x-crm.lead-slideover />
@endsection
