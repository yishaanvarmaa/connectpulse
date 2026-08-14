@props([
    'lead',
    'followUp' => null,
    'followUpTypes' => [],
    'outcomes' => [],
    'compact' => false,
])

<form
    method="POST"
    action="{{ route('org.crm.leads.log-interaction', $lead) }}"
    @class(['space-y-3', 'rounded-lg border border-slate-200 bg-slate-50 p-4' => $compact])
>
    @csrf
    @if($followUp)
        <input type="hidden" name="follow_up_id" value="{{ $followUp->id }}">
    @endif

    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600">What happened? *</label>
        <select name="outcome" required class="cp-select">
            <option value="">Select outcome...</option>
            @foreach($outcomes as $key => $label)
                <option value="{{ $key }}" @selected(old('outcome') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600">Notes for today</label>
        <textarea
            name="notes"
            rows="{{ $compact ? 2 : 3 }}"
            class="cp-input"
            placeholder="e.g. Rang twice, no answer. Will try again tomorrow morning."
        >{{ old('notes') }}</textarea>
    </div>

    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-3 space-y-3">
        <p class="text-xs font-medium text-slate-600">Schedule next follow-up (optional)</p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-[11px] text-slate-500">When</label>
                <input type="datetime-local" name="next_scheduled_at" value="{{ old('next_scheduled_at') }}" class="cp-input">
            </div>
            <div>
                <label class="mb-1 block text-[11px] text-slate-500">Type</label>
                <select name="next_type" class="cp-select">
                    @foreach($followUpTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('next_type', 'call') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="{{ $compact ? 'cp-btn-primary !py-1.5 text-xs w-full' : 'cp-btn-primary w-full' }}">
        Save log{{ $compact ? '' : ' & update timeline' }}
    </button>
</form>
