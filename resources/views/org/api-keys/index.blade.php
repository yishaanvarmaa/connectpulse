@extends('layouts.app')

@section('title', 'API Keys')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<h1 class="text-2xl font-bold text-slate-900 mb-2">API Credentials</h1>
<p class="text-sm text-slate-500 mb-6">Use these credentials in your application (e.g. Surabhi Diagnostics). Your app only needs these — never WhatsApp or Baileys.</p>

<div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm max-w-3xl">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
            <input readonly value="{{ $apiKey?->api_key }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">API Secret</label>
            <input readonly value="{{ $apiKey?->api_secret }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-mono">
        </div>
    </div>

    <p class="mt-4 text-sm text-slate-500">Include on every request: <code class="bg-slate-100 px-1 rounded">X-API-Key</code> and <code class="bg-slate-100 px-1 rounded">X-API-Secret</code></p>

    <form method="POST" action="{{ route('org.api-keys.regenerate') }}" class="mt-6" onsubmit="return confirm('Regenerating will invalidate your current keys. Continue?')">
        @csrf
        <button type="submit" class="rounded-lg border border-amber-300 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">Regenerate Keys</button>
    </form>

    <div class="mt-8 border-t border-slate-200 pt-6">
        <h2 class="font-semibold text-slate-900 mb-1">Public API</h2>
        <p class="text-sm text-slate-500 mb-4">Base URL: <code class="bg-slate-100 px-1 rounded">{{ url('/api/v1') }}</code></p>
        <dl class="space-y-2 text-sm font-mono text-slate-600">
            <div><span class="text-green-600">POST</span> /api/v1/messages/send</div>
            <div><span class="text-green-600">POST</span> /api/v1/messages/bulk</div>
            <div><span class="text-blue-600">GET</span> /api/v1/connection</div>
            <div><span class="text-blue-600">GET</span> /api/v1/credits/balance</div>
        </dl>
    </div>

    <div class="mt-6 rounded-lg bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
        <p class="font-medium text-slate-900 mb-2">For your application team</p>
        <ul class="list-disc list-inside space-y-1">
            <li>Link WhatsApp: <a href="{{ route('org.whatsapp.index') }}" class="text-brand-600 hover:underline">{{ url('/whatsapp') }}</a></li>
            <li>Recharge credits: <a href="{{ route('org.recharge.index') }}" class="text-brand-600 hover:underline">{{ url('/recharge') }}</a></li>
        </ul>
    </div>
</div>
@endsection
