@extends('layouts.org')

@section('title', $lead->name)

@section('page-title', $lead->name)
@section('page-subtitle')
    {{ $lead->phone }} · {{ $lead->statusLabel() }}
@endsection

@section('content')
<div class="mb-5 cp-card cp-card-body">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-4">
            <x-ui.avatar :name="$lead->name" class="!h-12 !w-12 !text-base" />
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-lg">{{ $lead->temperatureIcon() }}</span>
                    <h2 class="text-xl font-semibold text-slate-900">{{ $lead->name }}</h2>
                    <x-ui.badge type="brand">{{ $lead->statusLabel() }}</x-ui.badge>
                    @if($lead->isClosed())
                        <x-ui.badge type="{{ $lead->status === 'won' ? 'success' : 'neutral' }}">{{ $lead->status === 'won' ? 'Closed — Won' : 'Closed — Lost' }}</x-ui.badge>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $lead->phone }}@if($lead->email) · {{ $lead->email }}@endif</p>
                @if($lead->estimated_value)
                    <p class="mt-2 text-lg font-semibold text-slate-900">₹{{ number_format($lead->estimated_value, 0) }} <span class="text-sm font-normal text-slate-500">opportunity</span></p>
                @endif
                <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                    <div><span class="text-slate-400">Product</span> <span class="font-medium text-slate-700">{{ $lead->interested_product ?? '—' }}</span></div>
                    <div><span class="text-slate-400">Source</span> <span class="font-medium text-slate-700">{{ $lead->sourceLabel() }}</span></div>
                    <div><span class="text-slate-400">Last contact</span> <span class="font-medium text-slate-700">{{ $lead->last_contacted_at?->format('M d, h:i A') ?? 'Never' }}</span></div>
                </dl>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-whatsapp-open data-lead-name="{{ $lead->name }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success flex-1 sm:flex-none">WhatsApp</button>
            <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary flex-1 text-center sm:flex-none">Call</a>
            <a href="#log-interaction" class="cp-btn-primary flex-1 text-center sm:flex-none">Log call result</a>
            <a href="{{ route('org.crm.leads.edit', $lead) }}" class="cp-btn-ghost w-full text-center sm:w-auto">Edit</a>
        </div>
    </div>
</div>

@if($pendingFollowUp && !$lead->isClosed())
<div class="mb-5 cp-card border-amber-200 bg-amber-50/60">
    <div class="cp-card-body flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase text-amber-700">Follow-up due</p>
            <p class="text-sm font-semibold text-slate-900">{{ $pendingFollowUp->typeLabel() }} · {{ $pendingFollowUp->scheduled_at->format('M d, h:i A') }}
                @if($pendingFollowUp->isOverdue())<span class="text-red-600">(overdue)</span>@endif
            </p>
            <p class="mt-0.5 text-xs text-slate-600">Log the result below — stage updates in one step.</p>
        </div>
        <a href="#log-interaction" class="cp-btn-primary !py-2 text-xs shrink-0">Log result</a>
    </div>
</div>
<x-crm.complete-followup-sheet :follow-up="$pendingFollowUp" :lead="$lead" />
@endif

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 pb-20 lg:pb-0">
    <div class="lg:col-span-2 space-y-5 order-2 lg:order-1">
        <div class="cp-card">
            <div class="cp-card-header"><h3 class="text-sm font-semibold text-slate-900">Activity timeline</h3></div>
            <div class="px-5 py-4 space-y-0">
                @forelse($timeline as $activity)
                    <div class="relative flex gap-3 pb-5 last:pb-0">
                        @if(!$loop->last)<div class="absolute left-[7px] top-4 h-full w-px bg-slate-200"></div>@endif
                        <div class="relative z-10 mt-1 h-3.5 w-3.5 shrink-0 rounded-full border-2 border-white bg-brand-500 ring-2 ring-brand-100"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-400">{{ $activity->created_at->format('M d, h:i A') }}@if($activity->user) · {{ $activity->user->name }}@endif</p>
                            <p class="text-sm font-medium text-slate-900">{{ $activity->title }}</p>
                            @if($activity->description)
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $activity->description }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No activity yet. Log your first call on the right.</p>
                @endforelse
            </div>
        </div>

        <div class="cp-card">
            <div class="cp-card-header"><h3 class="text-sm font-semibold text-slate-900">WhatsApp messages</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($communications as $log)
                    <div class="px-5 py-3">
                        <div class="flex justify-between gap-2">
                            <p class="text-xs text-slate-400">{{ $log->created_at->format('M d, h:i A') }}</p>
                            <x-ui.badge :type="$log->status">{{ ucfirst($log->status) }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-sm text-slate-700">{{ $log->message }}</p>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No messages sent yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-5 order-1 lg:order-2">
        <div id="log-interaction" class="cp-card ring-2 ring-brand-500/20">
            <div class="cp-card-header bg-brand-50/50">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Log call result</h3>
                    <p class="text-xs text-slate-500">One step — records outcome, updates stage, schedules next follow-up</p>
                </div>
            </div>
            <div class="cp-card-body">
                @if($pendingFollowUp && !$lead->isClosed())
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        Completes follow-up due {{ $pendingFollowUp->scheduled_at->format('M d, h:i A') }}
                    </div>
                @endif
                <x-crm.interaction-result-form
                    :lead="$lead"
                    :follow-up="$pendingFollowUp"
                    :follow-up-types="$followUpTypes"
                />
            </div>
        </div>

        <div class="cp-card cp-card-body space-y-3 text-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Lead details</p>
            <dl class="space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Next follow-up</dt><dd class="font-medium text-right">{{ $lead->next_follow_up_at?->format('M d, h:i A') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Company</dt><dd class="font-medium text-right">{{ $lead->company ?? '—' }}</dd></div>
                @if($lead->lost_reason)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Lost reason</dt><dd class="font-medium text-right text-red-700">{{ $lead->lost_reason }}</dd></div>
                @endif
            </dl>
        </div>

        @if(!$lead->isClosed())
        <div class="cp-card cp-card-body">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Manual stage change</p>
            <p class="text-xs text-slate-500 mb-3">Only if you need to override without logging a call.</p>
            <form method="POST" action="{{ route('org.crm.leads.status', $lead) }}" class="flex flex-wrap gap-1.5">@csrf
                @foreach($statuses as $key => $label)
                    <button type="submit" name="status" value="{{ $key }}" class="rounded-lg px-2.5 py-1 text-xs font-medium border {{ $lead->status === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $label }}</button>
                @endforeach
            </form>
        </div>
        @endif

        <div class="cp-card">
            <div class="cp-card-header"><h3 class="text-sm font-semibold text-slate-900">Notes</h3></div>
            <div class="cp-card-body">
                @if($lead->notes)<div class="mb-3 whitespace-pre-line text-sm text-slate-700">{{ $lead->notes }}</div>@endif
                <form method="POST" action="{{ route('org.crm.leads.notes', $lead) }}" class="flex gap-2">@csrf
                    <input type="text" name="note" required placeholder="Quick note..." class="cp-input">
                    <button class="cp-btn-secondary">Add</button>
                </form>
            </div>
        </div>

        <div class="cp-card">
            <div class="cp-card-header"><h3 class="text-sm font-semibold text-slate-900">Follow-up history</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($lead->followUps as $followUp)
                    <div class="px-5 py-3 text-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $followUp->typeLabel() }}</p>
                                <p class="text-xs text-slate-500">{{ $followUp->scheduled_at->format('M d, h:i A') }}</p>
                            </div>
                            <x-ui.badge :type="$followUp->status === 'completed' ? 'success' : ($followUp->isOverdue() ? 'danger' : 'neutral')">{{ $followUp->displayStatus() }}</x-ui.badge>
                        </div>
                        @if($followUp->notes)
                            <p class="mt-2 whitespace-pre-line rounded-md bg-slate-50 px-2 py-1.5 text-xs text-slate-600">{{ $followUp->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500">None scheduled yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<x-crm.lead-action-bar :lead="$lead" :pending-follow-up="$pendingFollowUp ?? null" />
@endsection
