@props([
    'lead',
    'followUp' => null,
    'followUpTypes' => [],
    'compact' => false,
])

@php
    use App\Models\LeadActivity;
    use App\Models\LeadFollowUp;
    $results = LeadActivity::interactionResults();
    $followUpTypes = $followUpTypes ?: LeadFollowUp::types();
    $nextInputId = $followUp ? 'next-'.$followUp->id : 'next-interaction';
@endphp

<form
    method="POST"
    action="{{ route('org.crm.leads.log-interaction', $lead) }}"
    class="space-y-4"
>
    @csrf
    @if($followUp)
        <input type="hidden" name="follow_up_id" value="{{ $followUp->id }}">
    @endif

    <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">What happened? *</label>
        <p class="mb-3 text-xs text-slate-500">Pick one — the lead stage updates automatically.</p>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach($results as $key => $item)
                <label @class([
                    'flex cursor-pointer flex-col rounded-xl border px-3 py-2.5 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50',
                    'border-red-200 hover:bg-red-50/50' => $key === 'lost',
                    'border-emerald-200 hover:bg-emerald-50/50' => $key === 'won',
                    'border-slate-200 hover:bg-slate-50' => ! in_array($key, ['lost', 'won']),
                ])>
                    <span class="flex items-center gap-2">
                        <input type="radio" name="result" value="{{ $key }}" class="text-brand-600" @checked(old('result', $followUp ? 'no_response' : '') === $key) required>
                        <span class="text-sm font-medium text-slate-900">{{ $item['label'] }}</span>
                    </span>
                    <span class="mt-0.5 pl-6 text-[11px] text-slate-500">{{ $item['hint'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600">Notes (optional)</label>
        <textarea
            name="notes"
            rows="{{ $compact ? 2 : 3 }}"
            class="cp-input"
            placeholder="e.g. Said price is too high. Will try again next month."
        >{{ old('notes') }}</textarea>
    </div>

    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-3 space-y-3">
        <p class="text-xs font-medium text-slate-600">Next follow-up <span class="font-normal text-slate-400">(skip if deal is won/lost)</span></p>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-next-followup="tomorrow" class="cp-btn-secondary !py-1.5 text-xs">Tomorrow</button>
            <button type="button" data-next-followup="3days" class="cp-btn-secondary !py-1.5 text-xs">In 3 days</button>
            <button type="button" data-next-followup="week" class="cp-btn-secondary !py-1.5 text-xs">Next week</button>
            <button type="button" data-next-followup="none" class="cp-btn-ghost !py-1.5 text-xs">None</button>
        </div>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <input type="datetime-local" name="next_scheduled_at" id="{{ $nextInputId }}" value="{{ old('next_scheduled_at') }}" class="cp-input">
            <select name="next_type" class="cp-select">
                @foreach($followUpTypes as $key => $label)
                    <option value="{{ $key }}" @selected(old('next_type', 'call') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="cp-btn-primary w-full">
        Save & update lead
    </button>
</form>
