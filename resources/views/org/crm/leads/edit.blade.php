@extends('layouts.org')

@section('title', 'Edit Lead')

@php $pageTitle = 'Edit lead'; $pageSubtitle = $lead->name; @endphp

@section('content')
<div class="mx-auto max-w-2xl cp-card cp-card-body">
    <form method="POST" action="{{ route('org.crm.leads.update', $lead) }}" class="space-y-4">@csrf @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-slate-600">Name</label><input name="name" value="{{ old('name', $lead->name) }}" required class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Phone</label><input name="phone" value="{{ old('phone', $lead->phone) }}" required class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Email</label><input name="email" type="email" value="{{ old('email', $lead->email) }}" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Company</label><input name="company" value="{{ old('company', $lead->company) }}" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Designation</label><input name="designation" value="{{ old('designation', $lead->designation) }}" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Interested in</label><input name="interested_product" value="{{ old('interested_product', $lead->interested_product) }}" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Est. value</label><input name="estimated_value" type="number" value="{{ old('estimated_value', $lead->estimated_value) }}" class="cp-input"></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Source</label><select name="source" class="cp-select">@foreach($sources as $k=>$l)<option value="{{ $k }}" @selected(old('source',$lead->source)===$k)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Stage</label><select name="status" class="cp-select">@foreach($statuses as $k=>$l)<option value="{{ $k }}" @selected(old('status',$lead->status)===$k)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-xs font-medium text-slate-600">Priority</label><select name="priority" class="cp-select">@foreach($priorities as $k=>$l)<option value="{{ $k }}" @selected(old('priority',$lead->priority)===$k)>{{ $l }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-slate-600">Next follow-up</label><input name="next_follow_up_at" type="datetime-local" value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\TH:i')) }}" class="cp-input"></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-slate-600">Notes</label><textarea name="notes" rows="4" class="cp-input">{{ old('notes', $lead->notes) }}</textarea></div>
        </div>
        <div class="flex gap-2 pt-2">
            <a href="{{ route('org.crm.leads.show', $lead) }}" class="cp-btn-secondary">Cancel</a>
            <button type="submit" class="cp-btn-primary">Save changes</button>
        </div>
    </form>
</div>
@endsection
