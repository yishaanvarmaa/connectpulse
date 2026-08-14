@php
    use App\Models\Lead;
    use App\Models\LeadFollowUp;
    $sources = Lead::sources();
    $hasLeadFormErrors = $errors->any() && ($errors->has('name') || $errors->has('phone') || $errors->has('source'));
@endphp

<div id="lead-sheet" data-bottom-sheet @class(['hidden' => ! $hasLeadFormErrors]) class="fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[92vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
        <div class="sticky top-0 border-b border-slate-100 bg-white px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h2 class="text-base font-semibold text-slate-900">New Lead</h2>
            <p class="text-xs text-slate-500">Add in under 15 seconds</p>
        </div>
        <form method="POST" action="{{ route('org.crm.leads.store') }}" class="space-y-4 px-5 py-4 pb-8">
            @csrf
            @if($hasLeadFormErrors)
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="cp-input" placeholder="Ravi Kumar">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Phone *</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required class="cp-input" placeholder="9876543210">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Interested In</label>
                <input type="text" name="interested_product" value="{{ old('interested_product') }}" class="cp-input" placeholder="Diagnostic Software">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Value (₹)</label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" min="0" class="cp-input">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Source</label>
                    <select name="source" class="cp-select">
                        @foreach($sources as $key => $label)
                            <option value="{{ $key }}" @selected(old('source', 'manual') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Follow-up</label>
                <input type="datetime-local" name="next_follow_up_at" value="{{ old('next_follow_up_at') }}" class="cp-input">
            </div>
            <button type="submit" class="cp-btn-primary w-full !py-3 text-base">Save Lead</button>
        </form>
    </div>
</div>

@if($hasLeadFormErrors)
    @push('scripts')<script>document.body.classList.add('overflow-hidden');</script>@endpush
@endif
