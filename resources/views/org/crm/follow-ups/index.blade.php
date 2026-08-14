@extends('layouts.org')

@section('title', 'Follow-ups')

@php
    $pageTitle = 'Follow-ups';
    $pageSubtitle = ($groups['overdue']->count()).' overdue · '.($groups['today']->count()).' today';
@endphp

@section('content')
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
                                <button type="button" onclick="togglePanel('wa-{{ $item->id }}')" class="cp-btn-success !py-1.5 text-xs">WhatsApp</button>
                                <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary !py-1.5 text-xs">Call</a>
                                <form method="POST" action="{{ route('org.crm.follow-ups.complete', $item) }}">@csrf<button class="cp-btn-primary !py-1.5 text-xs">Complete</button></form>
                                <button type="button" onclick="togglePanel('rs-{{ $item->id }}')" class="cp-btn-ghost !py-1.5 text-xs">Reschedule</button>
                            </div>
                            <form id="wa-{{ $item->id }}" method="POST" action="{{ route('org.crm.follow-ups.whatsapp', $item) }}" class="hidden w-full flex gap-2 sm:col-span-full">@csrf
                                <input type="text" name="message" required class="cp-input" placeholder="Message...">
                                <button class="cp-btn-success">Send</button>
                            </form>
                            <form id="rs-{{ $item->id }}" method="POST" action="{{ route('org.crm.follow-ups.reschedule', $item) }}" class="hidden w-full flex gap-2">@csrf
                                <input type="datetime-local" name="scheduled_at" required class="cp-input">
                                <button class="cp-btn-primary">Save</button>
                            </form>
                        </div>
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
@endsection

@push('scripts')
<script>function togglePanel(id){document.getElementById(id)?.classList.toggle('hidden');document.getElementById(id)?.classList.toggle('flex');}</script>
@endpush
