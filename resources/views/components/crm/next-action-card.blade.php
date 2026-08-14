@props(['action'])

@if($action)
    @php
        $lead = $action['lead'];
        $followUp = $action['followUp'] ?? null;
        $urgencyClass = match ($action['urgency'] ?? $action['type']) {
            'overdue' => 'border-red-200 bg-gradient-to-br from-red-50 to-white',
            'today', 'follow_up' => 'border-amber-200 bg-gradient-to-br from-amber-50 to-white',
            'demo' => 'border-violet-200 bg-gradient-to-br from-violet-50 to-white',
            'new_lead' => 'border-brand-200 bg-gradient-to-br from-brand-50 to-white',
            default => 'border-slate-200 bg-white',
        };
    @endphp

    <div class="cp-next-action cp-card border-2 {{ $urgencyClass }}">
        <div class="cp-card-body">
            <div class="flex items-center justify-between gap-2 mb-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Next action</p>
                <span class="text-xs font-medium text-slate-600">{{ $action['label'] }}</span>
            </div>

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$lead->name" class="!h-11 !w-11" />
                <div class="min-w-0 flex-1">
                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="text-lg font-semibold text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                    <p class="text-sm text-slate-600">{{ $action['subtitle'] ?? $lead->interested_product }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        @if($action['time'] ?? null)
                            <span>{{ ($action['time'])->format('h:i A · M d') }}</span>
                        @endif
                        @if(($action['days_overdue'] ?? 0) > 0)
                            <x-ui.badge type="danger">{{ $action['days_overdue'] }} day{{ $action['days_overdue'] > 1 ? 's' : '' }} overdue</x-ui.badge>
                        @endif
                        @if($action['value'] ?? null)
                            <span class="font-semibold text-slate-700">₹{{ number_format($action['value'], 0) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <button type="button" data-whatsapp-open data-lead-id="{{ $lead->id }}" data-lead-name="{{ $lead->name }}" data-lead-phone="{{ $lead->phone }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success col-span-1">WhatsApp</button>
                <a href="tel:{{ $lead->phone }}" class="cp-btn-secondary col-span-1 text-center">Call</a>
                <a href="{{ route('org.crm.leads.show', $lead) }}#log-interaction" class="cp-btn-primary col-span-2 sm:col-span-1 text-center">Log outcome</a>
                @if($followUp)
                    <button type="button" data-sheet-open="complete-{{ $followUp->id }}" class="cp-btn-ghost col-span-2 sm:col-span-1">Complete</button>
                @endif
            </div>
        </div>
    </div>

    @if($followUp)
        <x-crm.complete-followup-sheet :follow-up="$followUp" :lead="$lead" />
    @endif
@else
    <div class="cp-card cp-card-body text-center">
        <p class="text-sm font-medium text-slate-900">You're all caught up</p>
        <p class="mt-1 text-xs text-slate-500">No urgent follow-ups right now.</p>
        <button type="button" data-sheet-open="lead-sheet" class="cp-btn-primary mt-4">+ Add lead</button>
    </div>
@endif
