@extends('layouts.app')

@section('title', 'Settings')

@section('nav')
    <x-nav-link :href="route('org.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('org.whatsapp.index')">WhatsApp</x-nav-link>
    <x-nav-link :href="route('org.api-keys.index')">API Keys</x-nav-link>
    <x-nav-link :href="route('org.credits.index')">Credits</x-nav-link>
    <x-nav-link :href="route('org.logs.index')">Logs</x-nav-link>
    <x-nav-link :href="route('org.settings.index')" :active="true">Settings</x-nav-link>
@endsection

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-6">Settings</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Organization</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Company</dt><dd>{{ $organization->company_name }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Contact</dt><dd>{{ $organization->contact_person }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $organization->email }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Mobile</dt><dd>{{ $organization->mobile }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Change Password</h2>
        <form method="POST" action="{{ route('org.settings.password') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Current Password</label>
                <input name="current_password" type="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">New Password</label>
                <input name="password" type="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                <input name="password_confirmation" type="password" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update Password</button>
        </form>
    </div>
</div>
@endsection
