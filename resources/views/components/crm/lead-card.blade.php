@props(['lead', 'showActions' => true])

@php
    $isOverdue = $lead->next_follow_up_at && $lead->next_follow_up_at->isPast() && ! $lead->isClosed();
@endphp

<div class="cp-lead-card cp-card">
    <div class="cp-card-body">
        <div class="flex items-start gap-3">
            <a href="{{ route('org.crm.leads.show', $lead) }}" class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">{{ $lead->temperatureIcon() }}</span>
                            <p class="truncate font-semibold text-slate-900">{{ $lead->name }}</p>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $lead->interested_product ?? $lead->phone }}</p>
                    </div>
                    @if($lead->estimated_value)
                        <span class="shrink-0 text-sm font-semibold text-slate-900">₹{{ number_format($lead->estimated_value, 0) }}</span>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <x-ui.badge type="brand">{{ $lead->statusLabel() }}</x-ui.badge>
                    <x-ui.badge>{{ $lead->sourceLabel() }}</x-ui.badge>
                    @if($isOverdue)
                        <x-ui.badge type="danger">Overdue</x-ui.badge>
                    @elseif($lead->next_follow_up_at)
                        <span class="text-[11px] text-slate-500">Follow-up: {{ $lead->next_follow_up_at->format('M d, h:i A') }}</span>
                    @endif
                </div>
            </a>
        </div>

        @if($showActions)
            <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                <a href="{{ route('org.crm.leads.show', $lead) }}#log-interaction" class="cp-btn-primary flex-1 !py-2 text-xs text-center">Log result</a>
                <button type="button" data-whatsapp-open data-lead-id="{{ $lead->id }}" data-lead-name="{{ $lead->name }}" data-lead-phone="{{ $lead->phone }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success !px-3 !py-2 text-xs">WA</button>
                <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary !px-3 !py-2 text-xs">Call</a>
            </div>
        @endif
    </div>
</div>
