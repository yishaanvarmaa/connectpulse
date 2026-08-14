@extends('layouts.org')

@section('title', 'New Lead')

@php $pageTitle = 'New Lead'; $pageSubtitle = 'Quick capture'; @endphp

@section('content')
<div class="mx-auto max-w-lg">
    <form method="POST" action="{{ route('org.crm.leads.store') }}" class="cp-card cp-card-body space-y-4">@csrf
        <div><label class="mb-1 block text-xs font-medium text-slate-600">Name *</label><input name="name" required autofocus class="cp-input"></div>
        <div><label class="mb-1 block text-xs font-medium text-slate-600">Phone *</label><input name="phone" type="tel" required class="cp-input"></div>
        <div><label class="mb-1 block text-xs font-medium text-slate-600">Interested in</label><input name="interested_product" class="cp-input"></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Value (₹)</label><input name="estimated_value" type="number" min="0" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Source</label><select name="source" class="cp-select">@foreach($sources as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
        </div>
        <div><label class="mb-1 block text-xs font-medium text-slate-600">Next follow-up</label><input name="next_follow_up_at" type="datetime-local" class="cp-input"></div>
        <div class="flex gap-2 pt-2">
            <a href="{{ route('org.crm.leads.index') }}" class="cp-btn-secondary flex-1">Cancel</a>
            <button type="submit" class="cp-btn-primary flex-1">Save lead</button>
        </div>
    </form>
</div>
@endsection
