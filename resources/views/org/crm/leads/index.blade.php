@extends('layouts.org')

@section('title', 'Leads')

@section('page-title', 'Leads')
@section('page-subtitle')
    {{ $leads->total() }} total
@endsection

@section('content')
{{-- Quick filters --}}
<div class="mb-4 -mx-1 overflow-x-auto scrollbar-thin">
    <div class="flex gap-2 px-1 pb-1 min-w-max">
        @foreach($quickFilters as $key => $label)
            <a href="{{ route('org.crm.leads.index', array_merge(request()->except('view', 'page'), ['view' => $key])) }}"
               class="cp-filter-chip {{ ($view ?? 'all') === $key ? 'cp-filter-chip-active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <form method="GET" class="flex flex-1 flex-wrap gap-2" id="leads-filter-form">
        @if(request('view') && request('view') !== 'all')
            <input type="hidden" name="view" value="{{ request('view') }}">
        @endif
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, phone..." class="cp-input flex-1 min-w-[140px]">
        <select name="source" class="cp-select w-auto" onchange="this.form.submit()">
            <option value="">All sources</option>
            @foreach($sources as $key => $label)
                <option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="sort" class="cp-select w-auto" onchange="this.form.submit()">
            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Newest</option>
            <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
            <option value="follow_up" @selected(($filters['sort'] ?? '') === 'follow_up')>Follow-up date</option>
            <option value="value" @selected(($filters['sort'] ?? '') === 'value')>Highest value</option>
        </select>
    </form>
    <div class="flex gap-2">
        <button type="button" data-sheet-open="lead-sheet" class="cp-btn-primary shrink-0 lg:hidden">+ Lead</button>
        <button type="button" data-slideover-open="lead-slideover" class="cp-btn-primary shrink-0 hidden lg:inline-flex">+ New Lead</button>
    </div>
</div>

{{-- Bulk actions (desktop) --}}
<form method="POST" action="{{ route('org.crm.leads.bulk-status') }}" id="bulk-form" class="hidden mb-3 lg:flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-2">
    @csrf
    <span class="text-xs font-medium text-brand-800"><span id="bulk-count">0</span> selected</span>
    <select name="status" class="cp-select !py-1 text-xs">
        @foreach($statuses as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="cp-btn-primary !py-1 text-xs">Update stage</button>
</form>

@if($leads->isEmpty())
    <x-ui.empty-state title="No leads found" description="Add your first lead or adjust filters.">
        <button type="button" data-sheet-open="lead-sheet" class="cp-btn-primary mt-4">+ Add lead</button>
    </x-ui.empty-state>
@else
    {{-- Mobile/tablet cards --}}
    <div class="grid gap-3 lg:hidden">
        @foreach($leads as $lead)
            <x-crm.lead-card :lead="$lead" />
        @endforeach
    </div>

    {{-- Desktop table --}}
    <div class="cp-table-wrap hidden lg:block">
        <table class="cp-table">
            <thead>
                <tr>
                    <th class="w-8"><input type="checkbox" id="select-all-leads" class="rounded border-slate-300"></th>
                    <th>Lead</th>
                    <th>Product</th>
                    <th>Source</th>
                    <th>Stage</th>
                    <th>Value</th>
                    <th>Follow-up</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($leads as $lead)
                    <tr class="hover:bg-slate-50/80">
                        <td><input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" form="bulk-form" class="lead-checkbox rounded border-slate-300"></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span>{{ $lead->temperatureIcon() }}</span>
                                <div>
                                    <a href="{{ route('org.crm.leads.show', $lead) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $lead->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $lead->phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $lead->interested_product ?? '—' }}</td>
                        <td>{{ $lead->sourceLabel() }}</td>
                        <td><x-ui.badge type="brand">{{ $lead->statusLabel() }}</x-ui.badge></td>
                        <td class="font-medium">{{ $lead->estimated_value ? '₹'.number_format($lead->estimated_value, 0) : '—' }}</td>
                        <td class="text-slate-600">{{ $lead->next_follow_up_at?->format('M d, h:i A') ?? '—' }}</td>
                        <td class="text-right">
                            <div class="flex justify-end gap-1">
                                <button type="button" data-whatsapp-open data-lead-name="{{ $lead->name }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $lead) }}" class="cp-btn-success !py-1 text-xs">WA</button>
                                <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-btn-ghost !py-1 text-xs">Open</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
(function(){
    const form = document.getElementById('bulk-form');
    const countEl = document.getElementById('bulk-count');
    const selectAll = document.getElementById('select-all-leads');
    const boxes = () => document.querySelectorAll('.lead-checkbox');

    function updateBulk(){
        const checked = [...boxes()].filter(b => b.checked);
        if (countEl) countEl.textContent = checked.length;
        form?.classList.toggle('hidden', checked.length === 0);
    }

    selectAll?.addEventListener('change', () => {
        boxes().forEach(b => { b.checked = selectAll.checked; });
        updateBulk();
    });
    boxes().forEach(b => b.addEventListener('change', updateBulk));
})();
</script>
@endpush
