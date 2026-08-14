@extends('layouts.app')

@section('title', 'CRM Dashboard')

@section('nav')
    <x-crm-nav />
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">CRM Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $organization->company_name }}</p>
    </div>
    <a href="{{ route('org.crm.leads.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
        + New Lead
    </a>
</div>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 mb-6">
    <x-stat-card label="Total Leads" :value="number_format($stats['total_leads'])" />
    <x-stat-card label="Open Leads" :value="number_format($stats['open_leads'])" />
    <x-stat-card label="Follow-ups Today" :value="number_format($stats['follow_ups_today'])" />
    <x-stat-card label="Overdue" :value="number_format($stats['overdue_follow_ups'])" hint="{{ $stats['overdue_follow_ups'] > 0 ? 'Needs attention' : null }}" />
    <x-stat-card label="Conversion" :value="$stats['conversion_rate'].'%'" />
</div>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-8">
    <x-stat-card label="Won" :value="number_format($stats['won'])" />
    <x-stat-card label="Lost" :value="number_format($stats['lost'])" />
    <x-stat-card label="Pipeline Value" :value="'₹'.number_format($stats['pipeline_value'], 0)" />
    <x-stat-card label="Won Revenue" :value="'₹'.number_format($stats['won_revenue'], 0)" />
</div>

@if($stats['overdue_follow_ups'] > 0 || $stats['follow_ups_today'] > 0)
<div class="mb-8 rounded-xl bg-amber-50 border border-amber-200 p-4">
    <p class="text-sm font-medium text-amber-900">
        @if($stats['overdue_follow_ups'] > 0)
            {{ $stats['overdue_follow_ups'] }} overdue follow-up{{ $stats['overdue_follow_ups'] > 1 ? 's' : '' }}
        @endif
        @if($stats['overdue_follow_ups'] > 0 && $stats['follow_ups_today'] > 0) · @endif
        @if($stats['follow_ups_today'] > 0)
            {{ $stats['follow_ups_today'] }} due today
        @endif
    </p>
    <a href="{{ route('org.crm.follow-ups.index') }}" class="mt-2 inline-block text-sm font-medium text-brand-700 underline">View follow-ups →</a>
</div>
@endif

<div class="rounded-xl bg-white border border-slate-200 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="font-semibold text-slate-900">Leads by Source</h2>
        <a href="{{ route('org.crm.reports.index') }}" class="text-sm text-brand-600 hover:text-brand-700">Full report →</a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($sourceAnalytics as $row)
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $row['source_label'] }}</p>
                    <p class="text-xs text-slate-500">{{ $row['total'] }} leads · {{ $row['won'] }} won · {{ $row['conversion_rate'] }}% conversion</p>
                </div>
                <p class="text-sm font-semibold text-slate-900">₹{{ number_format($row['revenue'], 0) }}</p>
            </div>
        @empty
            <p class="px-6 py-8 text-sm text-slate-500 text-center">No leads yet. <a href="{{ route('org.crm.leads.create') }}" class="text-brand-600 underline">Add your first lead</a></p>
        @endforelse
    </div>
</div>

<x-crm-fab />
@endsection
