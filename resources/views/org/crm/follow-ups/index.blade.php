@extends('layouts.org')

@section('title', 'Follow-ups')

@section('page-title', 'Follow-ups')
@section('page-subtitle')
    {{ $groups['overdue']->count() }} overdue · {{ $groups['today']->count() }} today
@endsection

@section('content')
<div class="mb-4 flex gap-2">
    <a href="{{ route('org.crm.follow-ups.index', ['view' => 'list']) }}" class="cp-filter-chip {{ ($view ?? 'list') === 'list' ? 'cp-filter-chip-active' : '' }}">List</a>
    <a href="{{ route('org.crm.follow-ups.index', ['view' => 'calendar']) }}" class="cp-filter-chip {{ ($view ?? 'list') === 'calendar' ? 'cp-filter-chip-active' : '' }}">Calendar</a>
</div>

@if(($view ?? 'list') === 'calendar')
    <div class="space-y-4">
        @foreach($calendar as $day)
            @if($day['items']->isNotEmpty() || $day['date']->isToday())
                <div class="cp-card">
                    <div class="cp-card-header">
                        <h2 class="text-sm font-semibold text-slate-900">{{ $day['label'] }}</h2>
                        <span class="text-xs text-slate-500">{{ $day['items']->count() }} scheduled</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($day['items'] as $item)
                            @php $lead = $item->lead; @endphp
                            <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="font-medium text-slate-900">{{ $lead->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $item->scheduled_at->format('h:i A') }} · {{ $item->typeLabel() }} · {{ $lead->interested_product }}</p>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" data-sheet-open="complete-{{ $item->id }}" class="cp-btn-primary !py-1.5 text-xs">Complete</button>
                                    <button type="button" data-sheet-open="reschedule-{{ $item->id }}" class="cp-btn-secondary !py-1.5 text-xs">Reschedule</button>
                                </div>
                            </div>
                            <x-crm.complete-followup-sheet :follow-up="$item" :lead="$lead" :follow-up-types="$followUpTypes" />
                            <x-crm.reschedule-followup-sheet :follow-up="$item" :lead="$lead" />
                        @empty
                            @if($day['date']->isToday())
                                <p class="px-4 py-4 text-sm text-slate-500">Nothing scheduled today.</p>
                            @endif
                        @endforelse
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-medium text-red-600">Overdue</p>
            <p class="mt-1 text-2xl font-semibold text-red-900">{{ $groups['overdue']->count() }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-medium text-amber-700">Today</p>
            <p class="mt-1 text-2xl font-semibold text-amber-900">{{ $groups['today']->count() }}</p>
        </div>
        <div class="rounded-xl border border-brand-200 bg-brand-50 p-4">
            <p class="text-xs font-medium text-brand-700">Upcoming</p>
            <p class="mt-1 text-2xl font-semibold text-brand-900">{{ $groups['upcoming']->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">No follow-up</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $groups['no_follow_up']->count() }}</p>
        </div>
    </div>

    @php
        $sections = [
            'overdue' => ['Overdue', 'border-red-200 bg-red-50/30'],
            'today' => ['Today', 'border-amber-200 bg-amber-50/20'],
            'upcoming' => ['Upcoming', ''],
            'no_follow_up' => ['No follow-up scheduled', ''],
        ];
    @endphp

    @foreach($sections as $key => [$label, $accent])
        @if($groups[$key]->count() > 0 || in_array($key, ['overdue', 'today']))
            <div class="mb-6">
                <h2 class="mb-3 text-sm font-semibold text-slate-900">{{ $label }} <span class="font-normal text-slate-400">({{ $groups[$key]->count() }})</span></h2>
                <div class="space-y-2">
                    @forelse($groups[$key] as $item)
                        @if($key !== 'no_follow_up')
                            @php $lead = $item->lead; @endphp
                            <div class="cp-card cp-card-body flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between {{ $accent }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-20 shrink-0 text-xs font-medium text-slate-500">{{ $item->scheduled_at->format('h:i A') }}</span>
                                    <x-ui.avatar :name="$lead->name" />
                                    <div class="min-w-0">
                                        <a href="{{ route('org.crm.leads.show', $lead) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                                        <p class="text-xs text-slate-500">{{ $lead->interested_product }} · {{ $item->typeLabel() }}</p>
                                    </div>
                                    @if($lead->estimated_value)<span class="hidden sm:block text-sm font-semibold">₹{{ number_format($lead->estimated_value, 0) }}</span>@endif
                                </div>
                                <div class="flex flex-wrap gap-1.5 sm:shrink-0">
                                    <button type="button" data-whatsapp-open data-lead-name="{{ $lead->name }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success !py-1.5 text-xs">WhatsApp</button>
                                    <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary !py-1.5 text-xs">Call</a>
                                    <button type="button" data-sheet-open="complete-{{ $item->id }}" class="cp-btn-primary !py-1.5 text-xs">Complete</button>
                                    <button type="button" data-sheet-open="reschedule-{{ $item->id }}" class="cp-btn-ghost !py-1.5 text-xs">Reschedule</button>
                                </div>
                            </div>
                            <x-crm.complete-followup-sheet :follow-up="$item" :lead="$lead" :follow-up-types="$followUpTypes" />
                            <x-crm.reschedule-followup-sheet :follow-up="$item" :lead="$lead" />
                        @else
                            <div class="cp-card cp-card-body flex items-center justify-between">
                                <div>
                                    <a href="{{ route('org.crm.leads.show', $item) }}" class="font-medium text-slate-900">{{ $item->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $item->statusLabel() }} · {{ $item->sourceLabel() }}</p>
                                </div>
                                <a href="{{ route('org.crm.leads.show', $item) }}" class="cp-btn-secondary !py-1.5 text-xs">Schedule</a>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-slate-500 py-2">None</p>
                    @endforelse
                </div>
            </div>
        @endif
    @endforeach
@endif
@endsection
