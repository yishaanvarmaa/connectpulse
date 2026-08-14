@php
    $sections = [
        'overdue' => ['label' => 'Overdue', 'color' => 'red', 'items' => $groups['overdue'], 'type' => 'follow_up'],
        'today' => ['label' => 'Today', 'color' => 'amber', 'items' => $groups['today'], 'type' => 'follow_up'],
        'upcoming' => ['label' => 'Upcoming', 'color' => 'brand', 'items' => $groups['upcoming'], 'type' => 'follow_up'],
        'no_follow_up' => ['label' => 'No Follow-up Scheduled', 'color' => 'slate', 'items' => $groups['no_follow_up'], 'type' => 'lead'],
    ];
@endphp

@extends('layouts.app')

@section('title', 'Follow-ups')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Follow-ups</h1>
    <p class="mt-1 text-sm text-slate-500">
        {{ $groups['overdue']->count() }} overdue · {{ $groups['today']->count() }} today · {{ $groups['upcoming']->count() }} upcoming
    </p>
</div>

@foreach($sections as $key => $section)
    @if($section['items']->count() > 0 || $key === 'overdue' || $key === 'today')
        <div class="mb-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
                {{ $section['label'] }}
                <span class="text-slate-400">({{ $section['items']->count() }})</span>
            </h2>

            <div class="space-y-3">
                @forelse($section['items'] as $item)
                    @if($section['type'] === 'follow_up')
                        @php $lead = $item->lead; @endphp
                        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="font-semibold text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                                    @if($lead->interested_product)
                                        <p class="text-sm text-slate-600">{{ $lead->interested_product }}</p>
                                    @endif
                                    @if($lead->estimated_value)
                                        <p class="text-sm font-semibold text-slate-900">₹{{ number_format($lead->estimated_value, 0) }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500 mt-1">{{ $item->typeLabel() }} · {{ $item->scheduled_at->format('d M, h:i A') }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="tel:{{ $lead->phone }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Call</a>
                                    <button type="button" onclick="toggleWaForm('wa-{{ $item->id }}')" class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700">WhatsApp</button>
                                    <form method="POST" action="{{ route('org.crm.follow-ups.complete', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700">Complete</button>
                                    </form>
                                    <button type="button" onclick="toggleReschedule('rs-{{ $item->id }}')" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">Reschedule</button>
                                </div>
                            </div>
                            <form id="wa-{{ $item->id }}" method="POST" action="{{ route('org.crm.follow-ups.whatsapp', $item) }}" class="hidden mt-3 flex gap-2">
                                @csrf
                                <input type="text" name="message" required placeholder="WhatsApp message..." class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white">Send</button>
                            </form>
                            <form id="rs-{{ $item->id }}" method="POST" action="{{ route('org.crm.follow-ups.reschedule', $item) }}" class="hidden mt-3 flex gap-2">
                                @csrf
                                <input type="datetime-local" name="scheduled_at" required class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white">Save</button>
                            </form>
                        </div>
                    @else
                        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <a href="{{ route('org.crm.leads.show', $item) }}" class="font-semibold text-slate-900">{{ $item->name }}</a>
                                    @if($item->interested_product)
                                        <p class="text-sm text-slate-600">{{ $item->interested_product }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500 mt-1">{{ $item->statusLabel() }} · {{ $item->sourceLabel() }}</p>
                                </div>
                                <a href="{{ route('org.crm.leads.show', $item) }}" class="rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700">Schedule</a>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-slate-500 py-2">None</p>
                @endforelse
            </div>
        </div>
    @endif
@endforeach

<x-crm-fab />
@endsection

@push('scripts')
<script>
function toggleWaForm(id) { document.getElementById(id).classList.toggle('hidden'); }
function toggleReschedule(id) { document.getElementById(id).classList.toggle('hidden'); }
</script>
@endpush
