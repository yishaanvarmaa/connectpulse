@extends('layouts.org')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Good '. (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')) .', ' . explode(' ', auth()->user()->name)[0];
    $pageSubtitle = "Here's what's happening with your business today.";
@endphp

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div></div>
    @if(auth()->user()->isOrganizationAdmin())
        <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Lead
        </button>
    @endif
</div>

@if($stats['connection_status'] !== 'Connected')
    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        WhatsApp is not connected. <a href="{{ route('org.whatsapp.index') }}" class="font-medium underline">Connect now</a> to send messages.
    </div>
@endif

@if(auth()->user()->isOrganizationAdmin() && isset($crmStats))
    {{-- CRM Today strip --}}
    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Follow-ups today" :value="number_format($crmStats['follow_ups_today'])" />
        <x-ui.stat label="Overdue" :value="number_format($crmStats['overdue_follow_ups'])" :hint="$crmStats['overdue_follow_ups'] > 0 ? 'Needs attention' : null" />
        <x-ui.stat label="Open leads" :value="number_format($crmStats['open_leads'])" />
        <x-ui.stat label="Won revenue" :value="'₹'.number_format($crmStats['won_revenue'], 0)" />
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Follow-ups today --}}
        <div class="xl:col-span-2 cp-card">
            <div class="cp-card-header">
                <h2 class="text-sm font-semibold text-slate-900">Follow-ups today</h2>
                <a href="{{ route('org.crm.follow-ups.index') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">View all →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($followUpsToday ?? [] as $followUp)
                    @php $lead = $followUp->lead; @endphp
                    <div class="flex items-center gap-3 px-4 py-3 sm:px-5 hover:bg-slate-50/80">
                        <div class="w-12 shrink-0 text-xs font-medium text-slate-500">{{ $followUp->scheduled_at->format('h:i A') }}</div>
                        <x-ui.avatar :name="$lead->name" />
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('org.crm.leads.show', $lead) }}" class="truncate text-sm font-medium text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                            <p class="truncate text-xs text-slate-500">{{ $lead->interested_product ?? 'No product' }} · {{ $followUp->typeLabel() }}</p>
                        </div>
                        @if($lead->estimated_value)
                            <span class="hidden sm:block text-sm font-medium text-slate-700">₹{{ number_format($lead->estimated_value, 0) }}</span>
                        @endif
                        <div class="flex shrink-0 gap-1">
                            <a href="{{ route('org.crm.leads.show', $lead) }}#whatsapp-form" class="cp-btn-success !px-2 !py-1.5 text-xs">WA</a>
                            <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary !px-2 !py-1.5 text-xs">Call</a>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-500">No follow-ups scheduled for today.</div>
                @endforelse
            </div>
        </div>

        {{-- Messaging stats --}}
        <div class="space-y-5">
            <div class="cp-card cp-card-body">
                <p class="text-xs font-medium text-slate-500">Messaging</p>
                <div class="mt-3 space-y-2.5">
                    <div class="flex justify-between text-sm"><span class="text-slate-600">Credits</span><span class="font-semibold">{{ number_format($stats['balance']) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-600">Messages today</span><span class="font-semibold">{{ number_format($stats['messages_today']) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-600">Success rate</span><span class="font-semibold">{{ $stats['success_rate'] }}%</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-600">WhatsApp</span><x-ui.badge :type="$stats['connection_status'] === 'Connected' ? 'success' : 'neutral'">{{ $stats['connection_status'] }}</x-ui.badge></div>
                </div>
            </div>

            @if(($followUpsOverdue ?? collect())->count() > 0)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">{{ $followUpsOverdue->count() }} overdue follow-up{{ $followUpsOverdue->count() > 1 ? 's' : '' }}</p>
                    <a href="{{ route('org.crm.follow-ups.index') }}" class="mt-1 inline-block text-xs font-medium text-red-700 underline">Review now →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Lead sources --}}
    @if(count($sourceAnalytics ?? []) > 0)
        <div class="mt-5 cp-card">
            <div class="cp-card-header">
                <h2 class="text-sm font-semibold text-slate-900">Lead sources</h2>
                <a href="{{ route('org.crm.reports.index') }}" class="text-xs font-medium text-brand-600">Reports →</a>
            </div>
            <div class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(array_slice($sourceAnalytics, 0, 4) as $row)
                    <div class="bg-white px-4 py-3">
                        <p class="text-xs font-medium text-slate-500">{{ $row['source_label'] }}</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $row['total'] }}</p>
                        <p class="text-xs text-slate-500">{{ $row['won'] }} won · {{ $row['conversion_rate'] }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-5">
        <x-ui.stat label="Credits" :value="number_format($stats['balance'])" />
        <x-ui.stat label="Messages today" :value="number_format($stats['messages_today'])" />
        <x-ui.stat label="This month" :value="number_format($stats['messages_this_month'])" />
        <x-ui.stat label="Success rate" :value="$stats['success_rate'].'%'" />
    </div>
@endif

{{-- Recent messages --}}
<div class="mt-5 cp-card">
    <div class="cp-card-header">
        <h2 class="text-sm font-semibold text-slate-900">Recent messages</h2>
        <a href="{{ route('org.logs.index') }}" class="text-xs font-medium text-brand-600">View logs →</a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($recentLogs as $log)
            <div class="flex items-start gap-3 px-4 py-3 sm:px-5">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900">{{ $log->mobile }}</p>
                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ Str::limit($log->message, 90) }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <x-ui.badge :type="$log->status">{{ ucfirst($log->status) }}</x-ui.badge>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $log->created_at->format('M d, H:i') }}</p>
                </div>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-sm text-slate-500">No messages sent yet.</div>
        @endforelse
    </div>
</div>

@if(auth()->user()->isOrganizationAdmin())
    <x-crm.lead-slideover />
@endif
@endsection
