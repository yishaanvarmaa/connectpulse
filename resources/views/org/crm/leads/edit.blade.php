@extends('layouts.app')

@section('title', 'Edit '.$lead->name)

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-6">
    <a href="{{ route('org.crm.leads.show', $lead) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back to lead</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">Edit Lead</h1>
</div>

<form method="POST" action="{{ route('org.crm.leads.update', $lead) }}" class="max-w-lg space-y-4">
    @csrf
    @method('PUT')

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $lead->name) }}" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone', $lead->phone) }}" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $lead->email) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="company" class="block text-sm font-medium text-slate-700">Company</label>
            <input type="text" name="company" id="company" value="{{ old('company', $lead->company) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
    </div>

    <div>
        <label for="designation" class="block text-sm font-medium text-slate-700">Designation</label>
        <input type="text" name="designation" id="designation" value="{{ old('designation', $lead->designation) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div>
        <label for="interested_product" class="block text-sm font-medium text-slate-700">Interested In</label>
        <input type="text" name="interested_product" id="interested_product" value="{{ old('interested_product', $lead->interested_product) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="estimated_value" class="block text-sm font-medium text-slate-700">Est. Value (₹)</label>
            <input type="number" name="estimated_value" id="estimated_value" value="{{ old('estimated_value', $lead->estimated_value) }}" min="0" step="1"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="source" class="block text-sm font-medium text-slate-700">Source</label>
            <select name="source" id="source"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach($sources as $key => $label)
                    <option value="{{ $key }}" @selected(old('source', $lead->source) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select name="status" id="status"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $lead->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="priority" class="block text-sm font-medium text-slate-700">Priority</label>
            <select name="priority" id="priority"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach($priorities as $key => $label)
                    <option value="{{ $key }}" @selected(old('priority', $lead->priority) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="next_follow_up_at" class="block text-sm font-medium text-slate-700">Next Follow-up</label>
        <input type="datetime-local" name="next_follow_up_at" id="next_follow_up_at"
               value="{{ old('next_follow_up_at', $lead->next_follow_up_at?->format('Y-m-d\TH:i')) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea name="notes" id="notes" rows="4"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes', $lead->notes) }}</textarea>
    </div>

    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save Changes</button>
</form>
@endsection
