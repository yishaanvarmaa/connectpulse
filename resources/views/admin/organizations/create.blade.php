@extends('layouts.app')

@section('title', 'Create Organization')

@section('nav')
    <x-nav-link :href="route('admin.organizations.index')" :active="true">Organizations</x-nav-link>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Create Organization</h1>

    <form method="POST" action="{{ route('admin.organizations.store') }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">Company Name</label>
            <input name="company_name" value="{{ old('company_name') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Contact Person</label>
            <input name="contact_person" value="{{ old('contact_person') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Mobile</label>
            <input name="mobile" value="{{ old('mobile') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Admin Password</label>
            <input name="password" type="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Initial Credits</label>
            <input name="initial_credits" type="number" min="0" value="{{ old('initial_credits', 0) }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create</button>
            <a href="{{ route('admin.organizations.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
