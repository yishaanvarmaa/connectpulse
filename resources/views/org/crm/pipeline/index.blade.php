@extends('layouts.app')

@section('title', 'Pipeline')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Sales Pipeline</h1>
    <p class="mt-1 text-sm text-slate-500">Move leads between stages</p>
</div>

<div class="overflow-x-auto pb-4 -mx-4 px-4">
    <div class="flex gap-4 min-w-max">
        @foreach($columns as $status => $column)
            <div class="w-72 shrink-0">
                <div class="rounded-t-lg bg-slate-100 px-3 py-2 border border-slate-200 border-b-0">
                    <h2 class="text-sm font-semibold text-slate-700">{{ $column['label'] }}</h2>
                    <p class="text-xs text-slate-500">{{ $column['leads']->count() }} leads</p>
                </div>
                <div class="rounded-b-lg border border-slate-200 bg-slate-50 min-h-[200px] p-2 space-y-2 max-h-[70vh] overflow-y-auto">
                    @forelse($column['leads'] as $lead)
                        <div class="rounded-lg bg-white border border-slate-200 p-3 shadow-sm">
                            <a href="{{ route('org.crm.leads.show', $lead) }}" class="block">
                                <p class="font-medium text-sm text-slate-900">{{ $lead->name }}</p>
                                <p class="text-xs text-slate-500">{{ $lead->phone }}</p>
                                @if($lead->interested_product)
                                    <p class="text-xs text-slate-600 mt-1">{{ Str::limit($lead->interested_product, 30) }}</p>
                                @endif
                                @if($lead->estimated_value)
                                    <p class="text-xs font-semibold text-slate-900 mt-1">₹{{ number_format($lead->estimated_value, 0) }}</p>
                                @endif
                            </a>
                            <div class="mt-2 flex flex-wrap gap-1 text-xs">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $lead->sourceLabel() }}</span>
                                @if($lead->priority === 'high')
                                    <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-600">High</span>
                                @endif
                            </div>
                            @if($lead->next_follow_up_at)
                                <p class="text-xs text-slate-500 mt-1">{{ $lead->next_follow_up_at->format('d M, h:i A') }}</p>
                            @endif
                            @if(!$lead->isClosed())
                                <form method="POST" action="{{ route('org.crm.pipeline.status', $lead) }}" class="mt-2">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()"
                                            class="w-full rounded border border-slate-200 px-2 py-1 text-xs text-slate-600">
                                        @foreach($statuses as $key => $label)
                                            @if(!in_array($key, ['won', 'lost']))
                                                <option value="{{ $key }}" @selected($lead->status === $key)>Move to {{ $label }}</option>
                                            @endif
                                        @endforeach
                                        <option value="won">Mark Won</option>
                                        <option value="lost">Mark Lost</option>
                                    </select>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Empty</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<x-crm-fab />
@endsection
