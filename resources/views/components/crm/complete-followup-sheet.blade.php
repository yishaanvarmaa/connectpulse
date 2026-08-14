@props(['lead', 'followUp', 'outcomes' => null, 'followUpTypes' => null])

@php
    use App\Models\LeadFollowUp;
    $followUpTypes = $followUpTypes ?? LeadFollowUp::types();
    $results = [
        'interested' => 'Interested',
        'needs_time' => 'Needs more time',
        'demo' => 'Demo required',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
        'no_response' => 'No response',
    ];
@endphp

<div id="complete-{{ $followUp->id }}" data-bottom-sheet class="hidden fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[90vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
        <div class="sticky top-0 border-b border-slate-100 bg-white px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">What happened?</h3>
            <p class="text-xs text-slate-500">{{ $lead->name }} · {{ $followUp->typeLabel() }}</p>
        </div>

        <form method="POST" action="{{ route('org.crm.leads.log-interaction', $lead) }}" class="space-y-4 px-5 py-4 pb-8">
            @csrf
            <input type="hidden" name="follow_up_id" value="{{ $followUp->id }}">

            <div>
                <label class="mb-2 block text-xs font-medium text-slate-600">Outcome</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($results as $key => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-xs has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" name="result" value="{{ $key }}" class="text-brand-600" @checked($key === 'no_response') required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Notes</label>
                <textarea name="notes" rows="2" class="cp-input" placeholder="Optional details..."></textarea>
            </div>

            <div>
                <label class="mb-2 block text-xs font-medium text-slate-600">Next follow-up</label>
                <div class="flex flex-wrap gap-2 mb-3">
                    <button type="button" data-next-followup="tomorrow" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">Tomorrow</button>
                    <button type="button" data-next-followup="3days" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">3 days</button>
                    <button type="button" data-next-followup="week" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">Next week</button>
                    <button type="button" data-next-followup="none" class="cp-quick-date cp-btn-ghost !py-1.5 text-xs">No follow-up</button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="datetime-local" name="next_scheduled_at" id="next-{{ $followUp->id }}" class="cp-input col-span-2 sm:col-span-1">
                    <select name="next_type" class="cp-select">
                        @foreach($followUpTypes as $key => $label)
                            <option value="{{ $key }}" @selected($key === 'call')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" data-sheet-close class="cp-btn-secondary flex-1">Cancel</button>
                <button type="submit" class="cp-btn-primary flex-1">Save</button>
            </div>
        </form>
    </div>
</div>
