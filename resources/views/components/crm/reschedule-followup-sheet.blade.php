@props(['lead', 'followUp'])

<div id="reschedule-{{ $followUp->id }}" data-bottom-sheet class="hidden fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[70vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
        <div class="sticky top-0 border-b border-slate-100 bg-white px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">Reschedule follow-up</h3>
            <p class="text-xs text-slate-500">{{ $lead->name }}</p>
        </div>
        <form method="POST" action="{{ route('org.crm.follow-ups.reschedule', $followUp) }}" class="space-y-4 px-5 py-4 pb-8">
            @csrf
            <div>
                <label class="mb-2 block text-xs font-medium text-slate-600">When?</label>
                <div class="flex flex-wrap gap-2 mb-3">
                    <button type="button" data-next-followup="tomorrow" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">Tomorrow</button>
                    <button type="button" data-next-followup="3days" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">In 3 days</button>
                    <button type="button" data-next-followup="week" class="cp-quick-date cp-btn-secondary !py-1.5 text-xs">Next week</button>
                </div>
                <input type="datetime-local" name="scheduled_at" required class="cp-input" value="{{ $followUp->scheduled_at->format('Y-m-d\TH:i') }}">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" data-sheet-close class="cp-btn-secondary flex-1">Cancel</button>
                <button type="submit" class="cp-btn-primary flex-1">Save</button>
            </div>
        </form>
    </div>
</div>
