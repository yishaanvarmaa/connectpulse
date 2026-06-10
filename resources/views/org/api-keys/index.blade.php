@extends('layouts.app')

@section('title', 'API Keys')

@section('nav')
    <x-nav-link :href="route('org.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('org.whatsapp.index')">WhatsApp</x-nav-link>
    <x-nav-link :href="route('org.api-keys.index')" :active="true">API Keys</x-nav-link>
    <x-nav-link :href="route('org.credits.index')">Credits</x-nav-link>
    <x-nav-link :href="route('org.logs.index')">Logs</x-nav-link>
    <x-nav-link :href="route('org.settings.index')">Settings</x-nav-link>
@endsection

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-6">API Keys</h1>

<div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm max-w-3xl">
    <p class="text-sm text-slate-500 mb-6">Use these credentials in your API requests. Include them as headers: <code class="bg-slate-100 px-1 rounded">X-API-KEY</code> and <code class="bg-slate-100 px-1 rounded">X-API-SECRET</code></p>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
            <div class="flex gap-2">
                <input readonly value="{{ $apiKey?->api_key }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-mono">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">API Secret</label>
            <input readonly value="{{ $apiKey?->api_secret }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-mono">
        </div>
    </div>

    <form method="POST" action="{{ route('org.api-keys.regenerate') }}" class="mt-6" onsubmit="return confirm('Regenerating will invalidate your current keys. Continue?')">
        @csrf
        <button type="submit" class="rounded-lg border border-amber-300 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">Regenerate Keys</button>
    </form>

    <div class="mt-8 border-t border-slate-200 pt-6">
        <h2 class="font-semibold text-slate-900 mb-3">API Endpoints</h2>
        <dl class="space-y-2 text-sm font-mono text-slate-600">
            <div><span class="text-green-600">POST</span> {{ url('/api/v1/send-message') }}</div>
            <div><span class="text-green-600">POST</span> {{ url('/api/v1/send-bulk') }}</div>
            <div><span class="text-blue-600">GET</span> {{ url('/api/v1/balance') }}</div>
            <div><span class="text-blue-600">GET</span> {{ url('/api/v1/status') }}</div>
        </dl>
    </div>
</div>
@endsection
