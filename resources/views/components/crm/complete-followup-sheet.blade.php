@props(['lead', 'followUp', 'outcomes' => null, 'followUpTypes' => null])

<div id="complete-{{ $followUp->id }}" data-bottom-sheet class="hidden fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[90vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
        <div class="sticky top-0 border-b border-slate-100 bg-white px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">Log call result</h3>
            <p class="text-xs text-slate-500">{{ $lead->name }} · {{ $followUp->typeLabel() }} due {{ $followUp->scheduled_at->format('M d, h:i A') }}</p>
        </div>

        <div class="px-5 py-4 pb-8">
            <x-crm.interaction-result-form
                :lead="$lead"
                :follow-up="$followUp"
                :follow-up-types="$followUpTypes"
                compact
            />
            <button type="button" data-sheet-close class="cp-btn-ghost mt-3 w-full">Cancel</button>
        </div>
    </div>
</div>
