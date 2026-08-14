@extends('layouts.org')

@section('title', 'API')

@php $pageTitle = 'API'; $pageSubtitle = 'Integration credentials'; @endphp

@section('content')
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        <div class="cp-card cp-card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">API status</h2>
                    <p class="text-xs text-slate-500">Use these credentials in your application</p>
                </div>
                <x-ui.badge type="success">Active</x-ui.badge>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">API Base URL</label>
                    <div class="flex gap-2">
                        <input readonly value="{{ url('/api/v1') }}" class="cp-input font-mono text-xs">
                        <button type="button" data-copy="{{ url('/api/v1') }}" class="cp-btn-secondary shrink-0">Copy</button>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">API Key</label>
                    <div class="flex gap-2">
                        <input readonly id="api-key-field" value="{{ $apiKey?->api_key }}" class="cp-input font-mono text-xs">
                        <button type="button" data-copy="{{ $apiKey?->api_key }}" class="cp-btn-secondary shrink-0">Copy</button>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">API Secret</label>
                    <div class="flex gap-2">
                        <input readonly id="api-secret-field" type="password" value="{{ $apiKey?->api_secret }}" class="cp-input font-mono text-xs">
                        <button type="button" onclick="toggleSecret()" class="cp-btn-secondary shrink-0" id="reveal-btn">Reveal</button>
                        <button type="button" data-copy="{{ $apiKey?->api_secret }}" class="cp-btn-secondary shrink-0">Copy</button>
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                Never share API secrets publicly. Include <code class="bg-white/60 px-1 rounded">X-API-Key</code> and <code class="bg-white/60 px-1 rounded">X-API-Secret</code> on every request.
            </div>

            <form method="POST" action="{{ route('org.api-keys.regenerate') }}" class="mt-4" onsubmit="return confirm('Regenerating will invalidate current keys. Continue?')">@csrf
                <button type="submit" class="cp-btn-danger">Regenerate keys</button>
            </form>
        </div>

        <div class="cp-card">
            <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Endpoints</h2></div>
            <div class="divide-y divide-slate-100">
                @foreach([
                    ['POST', '/api/v1/messages/send', 'Send a message'],
                    ['POST', '/api/v1/messages/bulk', 'Bulk messages'],
                    ['GET', '/api/v1/connection', 'Connection status'],
                    ['GET', '/api/v1/credits/balance', 'Credit balance'],
                ] as [$method, $path, $desc])
                    <div class="flex items-center gap-3 px-5 py-3">
                        <x-ui.badge :type="$method === 'POST' ? 'brand' : 'neutral'">{{ $method }}</x-ui.badge>
                        <div>
                            <p class="font-mono text-xs text-slate-800">{{ $path }}</p>
                            <p class="text-xs text-slate-500">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="cp-card cp-card-body text-sm text-slate-600">
        <p class="font-medium text-slate-900 mb-2">For your dev team</p>
        <ul class="space-y-2 text-xs">
            <li>WhatsApp setup: <a href="{{ route('org.whatsapp.index') }}" class="text-brand-600 hover:underline">Connect here</a></li>
            <li>Credits: <a href="{{ route('org.recharge.index') }}" class="text-brand-600 hover:underline">Recharge</a></li>
            <li>Logs: <a href="{{ route('org.logs.index') }}" class="text-brand-600 hover:underline">Message history</a></li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSecret(){
    const f=document.getElementById('api-secret-field');
    const b=document.getElementById('reveal-btn');
    if(f.type==='password'){f.type='text';b.textContent='Hide';}else{f.type='password';b.textContent='Reveal';}
}
</script>
@endpush
