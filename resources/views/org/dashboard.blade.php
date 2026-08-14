@extends('layouts.org')

@section('title', 'Home')

@section('page-title')
    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
@endsection
@section('page-subtitle', "Here's what needs your attention.")

@section('content')
@if(($messagingStats['connection_status'] ?? $stats['connection_status'] ?? '') !== 'Connected')
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        WhatsApp offline. <a href="{{ route('org.whatsapp.index') }}" class="font-medium underline">Connect</a> to send messages.
    </div>
@endif

@if(isset($crmStats))
    {{-- Next action first --}}
    <div class="mb-5">
        <x-crm.next-action-card :action="$nextAction ?? null" />
    </div>

    {{-- Today strip --}}
    <div class="mb-5">
        <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Today</h2>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <a href="{{ route('org.crm.follow-ups.index') }}" class="rounded-xl border border-red-200 bg-red-50 px-3 py-3 text-center hover:border-red-300">
                <p class="text-[10px] font-semibold uppercase text-red-600">Overdue</p>
                <p class="text-xl font-bold text-red-900">{{ $crmStats['overdue_follow_ups'] }}</p>
            </a>
            <a href="{{ route('org.crm.follow-ups.index') }}" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-center hover:border-amber-300">
                <p class="text-[10px] font-semibold uppercase text-amber-700">Follow-ups</p>
                <p class="text-xl font-bold text-amber-900">{{ $crmStats['follow_ups_today'] }}</p>
            </a>
            <a href="{{ route('org.crm.leads.index', ['view' => 'new']) }}" class="rounded-xl border border-brand-200 bg-brand-50 px-3 py-3 text-center hover:border-brand-300">
                <p class="text-[10px] font-semibold uppercase text-brand-700">New leads</p>
                <p class="text-xl font-bold text-brand-900">{{ $newLeadsCount ?? $crmStats['new_leads'] }}</p>
            </a>
            <a href="{{ route('org.crm.pipeline.index') }}" class="rounded-xl border border-violet-200 bg-violet-50 px-3 py-3 text-center hover:border-violet-300">
                <p class="text-[10px] font-semibold uppercase text-violet-700">Demos</p>
                <p class="text-xl font-bold text-violet-900">{{ $demosToday ?? 0 }}</p>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        {{-- Today's follow-ups --}}
        <div class="cp-card">
            <div class="cp-card-header">
                <h2 class="text-sm font-semibold text-slate-900">Today's follow-ups</h2>
                <a href="{{ route('org.crm.follow-ups.index') }}" class="text-xs font-medium text-brand-600">View all →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse(($followUpGroups['today'] ?? collect()) as $followUp)
                    @php $lead = $followUp->lead; @endphp
                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/80">
                        <span class="w-14 shrink-0 text-xs font-semibold text-slate-500">{{ $followUp->scheduled_at->format('h:i A') }}</span>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('org.crm.leads.show', $lead) }}" class="block truncate text-sm font-medium text-slate-900">{{ $lead->name }}</a>
                            <p class="truncate text-xs text-slate-500">{{ $followUp->typeLabel() }}</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <button type="button" data-whatsapp-open data-lead-name="{{ $lead->name }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success !px-2 !py-1 text-xs">WA</button>
                            <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary !px-2 !py-1 text-xs">Call</a>
                            <button type="button" data-sheet-open="complete-{{ $followUp->id }}" class="cp-btn-primary !px-2 !py-1 text-xs">Done</button>
                        </div>
                    </div>
                    <x-crm.complete-followup-sheet :follow-up="$followUp" :lead="$lead" />
                @empty
                    <div class="px-5 py-6 text-center text-sm text-slate-500">No follow-ups today. You're caught up.</div>
                @endforelse
            </div>
        </div>

        {{-- Pipeline --}}
        <div class="cp-card">
            <div class="cp-card-header">
                <h2 class="text-sm font-semibold text-slate-900">Pipeline</h2>
                <a href="{{ route('org.crm.pipeline.index') }}" class="text-xs font-medium text-brand-600">Open →</a>
            </div>
            <div class="cp-card-body pt-0">
                <div class="mb-4 flex items-baseline justify-between">
                    <div>
                        <p class="text-2xl font-bold text-slate-900">₹{{ number_format($pipelineSummary['total_value'] ?? 0, 0) }}</p>
                        <p class="text-xs text-slate-500">{{ $pipelineSummary['open_count'] ?? 0 }} open leads</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(array_slice($pipelineSummary['stages'] ?? [], 0, 6) as $stage)
                        @if($stage['count'] > 0)
                            <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                <p class="text-[10px] font-medium uppercase text-slate-500">{{ $stage['label'] }}</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $stage['count'] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Performance --}}
    <div class="mt-5">
        <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Performance</h2>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.stat label="Won revenue" :value="'₹'.number_format($crmStats['won_revenue'], 0)" />
            <x-ui.stat label="Conversion" :value="$crmStats['conversion_rate'].'%'" />
            <x-ui.stat label="Open leads" :value="number_format($crmStats['open_leads'])" />
            <x-ui.stat label="Total leads" :value="number_format($crmStats['total_leads'])" />
        </div>
    </div>

    {{-- Recent leads --}}
    @if(($recentLeads ?? collect())->isNotEmpty())
        <div class="mt-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Recent leads</h2>
                <a href="{{ route('org.crm.leads.index') }}" class="text-xs text-brand-600">View all →</a>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($recentLeads as $lead)
                    <x-crm.lead-card :lead="$lead" />
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Credits" :value="number_format($stats['balance'])" />
        <x-ui.stat label="Messages today" :value="number_format($stats['messages_today'])" />
        <x-ui.stat label="This month" :value="number_format($stats['messages_this_month'])" />
        <x-ui.stat label="Success rate" :value="$stats['success_rate'].'%'" />
    </div>
@endif
@endsection
