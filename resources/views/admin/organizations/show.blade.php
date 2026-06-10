@extends('layouts.app')

@section('title', $organization->company_name)

@section('nav')
    <x-nav-link :href="route('admin.organizations.index')" :active="true">Organizations</x-nav-link>
@endsection

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.organizations.index') }}" class="text-sm text-brand-600 hover:text-brand-700">&larr; Back to organizations</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $organization->company_name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <x-stat-card label="Credit Balance" :value="number_format($organization->creditWallet?->balance ?? 0)" />
    <x-stat-card label="WhatsApp Status" :value="$organization->whatsappConnection?->getClientStatus() ?? 'Disconnected'" />
    <x-stat-card label="Status" :value="ucfirst($organization->status)" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Details</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Contact</dt><dd>{{ $organization->contact_person }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $organization->email }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Mobile</dt><dd>{{ $organization->mobile }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">API Key</dt><dd class="font-mono text-xs">{{ $organization->apiKey?->api_key }}</dd></div>
        </dl>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Actions</h2>
        <form method="POST" action="{{ route('admin.credits.store', $organization) }}" class="flex gap-2 mb-4">
            @csrf
            <input name="amount" type="number" min="1" placeholder="Credits" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm flex-1">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Add Credits</button>
        </form>
        <div class="flex gap-2">
            @if($organization->isActive())
                <form method="POST" action="{{ route('admin.organizations.suspend', $organization) }}">@csrf<button class="rounded-lg border border-amber-300 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">Suspend</button></form>
            @else
                <form method="POST" action="{{ route('admin.organizations.activate', $organization) }}">@csrf<button class="rounded-lg border border-green-300 px-4 py-2 text-sm text-green-700 hover:bg-green-50">Activate</button></form>
            @endif
            <form method="POST" action="{{ route('admin.organizations.destroy', $organization) }}" onsubmit="return confirm('Delete this organization?')">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
