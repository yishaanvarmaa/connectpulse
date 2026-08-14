@extends('layouts.app')

@section('title', 'New Lead')

@section('nav')
    <x-crm-nav />
@endsection

@section('content')
<div class="mb-6">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('org.crm.leads.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">New Lead</h1>
    <p class="mt-1 text-sm text-slate-500">Quick capture — only essentials required</p>
</div>

<form method="POST" action="{{ route('org.crm.leads.store') }}" class="max-w-lg space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">Phone *</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="interested_product" class="block text-sm font-medium text-slate-700">Interested In</label>
        <input type="text" name="interested_product" id="interested_product" value="{{ old('interested_product') }}" placeholder="e.g. Diagnostic Software"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="estimated_value" class="block text-sm font-medium text-slate-700">Est. Value (₹)</label>
            <input type="number" name="estimated_value" id="estimated_value" value="{{ old('estimated_value') }}" min="0" step="1" inputmode="numeric"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label for="source" class="block text-sm font-medium text-slate-700">Source *</label>
            <select name="source" id="source" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach($sources as $key => $label)
                    <option value="{{ $key }}" @selected(old('source', 'manual') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="next_follow_up_at" class="block text-sm font-medium text-slate-700">Next Follow-up</label>
        <input type="datetime-local" name="next_follow_up_at" id="next_follow_up_at" value="{{ old('next_follow_up_at') }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-brand-500 focus:ring-brand-500">
    </div>

    <details class="rounded-lg border border-slate-200 p-4">
        <summary class="text-sm font-medium text-slate-700 cursor-pointer">Optional details</summary>
        <div class="mt-4 space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label for="company" class="block text-sm font-medium text-slate-700">Company</label>
                <input type="text" name="company" id="company" value="{{ old('company') }}"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label for="priority" class="block text-sm font-medium text-slate-700">Priority</label>
                <select name="priority" id="priority"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @foreach($priorities as $key => $label)
                        <option value="{{ $key }}" @selected(old('priority', 'medium') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
            </div>
        </div>
    </details>

    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-3 text-base font-semibold text-white hover:bg-brand-700 active:scale-[0.98] transition-transform">
        Save Lead
    </button>
</form>
@endsection
