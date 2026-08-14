@php
    use App\Models\Lead;
    use App\Models\LeadFollowUp;
    $sources = Lead::sources();
    $priorities = Lead::priorities();
@endphp

<div id="lead-slideover" data-slideover class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-900/40" data-slideover-close></div>
    <div class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">New Lead</h2>
                <p class="text-xs text-slate-500">Capture a lead in seconds</p>
            </div>
            <button type="button" data-slideover-close class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('org.crm.leads.store') }}" class="flex flex-1 flex-col overflow-hidden">
            @csrf
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Name *</label>
                    <input type="text" name="name" required autofocus class="cp-input" placeholder="Ravi Kumar">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Phone *</label>
                    <input type="tel" name="phone" required inputmode="tel" class="cp-input" placeholder="9876543210">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Interested In</label>
                    <input type="text" name="interested_product" class="cp-input" placeholder="Diagnostic Software">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Est. Value (₹)</label>
                        <input type="number" name="estimated_value" min="0" class="cp-input" placeholder="24999">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Source</label>
                        <select name="source" class="cp-select">
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'manual')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Next Follow-up</label>
                    <input type="datetime-local" name="next_follow_up_at" class="cp-input">
                </div>
                <details class="rounded-lg border border-slate-200 p-3">
                    <summary class="cursor-pointer text-xs font-medium text-slate-600">Optional details</summary>
                    <div class="mt-3 space-y-3">
                        <input type="email" name="email" class="cp-input" placeholder="Email">
                        <input type="text" name="company" class="cp-input" placeholder="Company">
                        <select name="priority" class="cp-select">
                            @foreach($priorities as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'medium')>{{ $label }}</option>
                            @endforeach
                        </select>
                        <textarea name="notes" rows="2" class="cp-input" placeholder="Notes"></textarea>
                    </div>
                </details>
            </div>
            <div class="border-t border-slate-100 px-5 py-4 flex gap-2">
                <button type="button" data-slideover-close class="cp-btn-secondary flex-1">Cancel</button>
                <button type="submit" class="cp-btn-primary flex-1">Save Lead</button>
            </div>
        </form>
    </div>
</div>
