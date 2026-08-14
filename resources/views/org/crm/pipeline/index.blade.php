@extends('layouts.org')

@section('title', 'Pipeline')

@section('page-title', 'Pipeline')
@section('page-subtitle', 'Move leads through your sales stages')

@section('content')
{{-- Mobile list view --}}
<div class="space-y-4 lg:hidden">
    @php $totalValue = collect($columns)->sum('value'); @endphp
    <div class="cp-card cp-card-body">
        <p class="text-xs text-slate-500">Open pipeline</p>
        <p class="text-2xl font-bold text-slate-900">₹{{ number_format($totalValue, 0) }}</p>
    </div>
    @foreach($columns as $columnKey => $column)
        @if($column['leads']->isNotEmpty())
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">{{ $column['label'] }}</h2>
                    <span class="text-xs text-slate-500">{{ $column['leads']->count() }} · ₹{{ number_format($column['value'] ?? 0, 0) }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($column['leads'] as $lead)
                        <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-card cp-card-body flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900">{{ $lead->name }}</p>
                                <p class="text-xs text-slate-500">{{ $lead->interested_product ?? $lead->phone }}</p>
                            </div>
                            @if($lead->estimated_value)
                                <span class="text-sm font-semibold">₹{{ number_format($lead->estimated_value, 0) }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>

{{-- Desktop kanban --}}
<div class="hidden lg:block overflow-x-auto pb-2 -mx-4 px-4 scrollbar-thin">
    <div class="flex gap-3 min-w-max">
        @foreach($columns as $columnKey => $column)
            <div class="w-72 shrink-0">
                <div class="mb-2 flex items-end justify-between px-1">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">{{ $column['label'] }}</h2>
                        <p class="text-xs text-slate-500">
                            {{ $column['leads']->count() }} leads
                            @if(($column['value'] ?? 0) > 0)
                                · ₹{{ number_format($column['value'], 0) }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="min-h-[280px] space-y-2 rounded-xl border border-slate-200/80 bg-slate-100/50 p-2">
                    @forelse($column['leads'] as $lead)
                        <div class="cp-card cp-card-body !p-3 shadow-sm hover:shadow-md transition-shadow">
                            <a href="{{ route('org.crm.leads.show', $lead) }}" class="block">
                                <div class="flex items-start gap-2">
                                    <x-ui.avatar :name="$lead->name" class="!h-7 !w-7 !text-[10px]" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-900">{{ $lead->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $lead->interested_product ?? $lead->phone }}</p>
                                    </div>
                                </div>
                                @if($lead->estimated_value)
                                    <p class="mt-2 text-sm font-semibold text-slate-900">₹{{ number_format((float) $lead->estimated_value, 0) }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <x-ui.badge>{{ $lead->sourceLabel() }}</x-ui.badge>
                                    @if($lead->next_follow_up_at)
                                        <x-ui.badge type="warning">{{ $lead->next_follow_up_at->isToday() ? 'Today' : $lead->next_follow_up_at->format('M d') }}</x-ui.badge>
                                    @endif
                                </div>
                            </a>
                            @if(!$lead->isClosed())
                                <form method="POST" action="{{ route('org.crm.pipeline.status', $lead) }}" class="mt-2">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="cp-select !py-1.5 !text-xs">
                                        @foreach($statuses as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" @selected($lead->status === $statusKey)>{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-200 py-8 text-center text-xs text-slate-400">Empty</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection