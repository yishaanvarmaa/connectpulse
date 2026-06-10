@extends('layouts.app')

@section('title', 'WhatsApp Connection')

@section('nav')
    <x-nav-link :href="route('org.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('org.whatsapp.index')" :active="true">WhatsApp</x-nav-link>
    <x-nav-link :href="route('org.api-keys.index')">API Keys</x-nav-link>
    <x-nav-link :href="route('org.credits.index')">Credits</x-nav-link>
    <x-nav-link :href="route('org.logs.index')">Logs</x-nav-link>
    <x-nav-link :href="route('org.settings.index')">Settings</x-nav-link>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">WhatsApp Connection</h1>
    <p class="text-sm text-slate-500 mb-8">Connect your WhatsApp Business number to start sending messages via API.</p>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-slate-500">Status</p>
                <p id="connection-status" class="text-lg font-semibold text-slate-900">{{ $connection?->getClientStatus() ?? 'Disconnected' }}</p>
                <p id="connected-phone" class="text-sm text-slate-500 mt-1">{{ $connection?->phone_number ?? '' }}</p>
            </div>
            <div id="status-indicator" class="h-3 w-3 rounded-full {{ $connection?->isConnected() ? 'bg-green-500' : 'bg-slate-300' }}"></div>
        </div>

        <div id="qr-container" class="hidden mb-6 text-center">
            <p class="text-sm text-slate-600 mb-4">Scan this QR code with WhatsApp on your phone</p>
            <div class="inline-block p-4 bg-white border border-slate-200 rounded-lg">
                <img id="qr-image" src="" alt="WhatsApp QR Code" class="w-64 h-64">
            </div>
        </div>

        <div class="flex gap-3">
            <button id="btn-connect" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Connect WhatsApp</button>
            <button id="btn-disconnect" class="hidden rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Disconnect</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let pollInterval = null;

async function updateStatus() {
    const res = await fetch('{{ route('org.whatsapp.status') }}');
    const data = await res.json();
    document.getElementById('connection-status').textContent = data.status;
    document.getElementById('connected-phone').textContent = data.phone || '';
    document.getElementById('status-indicator').className = 'h-3 w-3 rounded-full ' + (data.connected ? 'bg-green-500' : 'bg-slate-300');
    document.getElementById('btn-disconnect').classList.toggle('hidden', !data.connected);
    if (data.connected) {
        document.getElementById('qr-container').classList.add('hidden');
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

async function fetchQr() {
    const res = await fetch('{{ route('org.whatsapp.qr') }}');
    const data = await res.json();
    if (data.qr) {
        document.getElementById('qr-container').classList.remove('hidden');
        document.getElementById('qr-image').src = data.qr;
    }
}

document.getElementById('btn-connect').addEventListener('click', async () => {
    await fetch('{{ route('org.whatsapp.connect') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    document.getElementById('connection-status').textContent = 'Reconnect Required';
    await fetchQr();
    if (!pollInterval) pollInterval = setInterval(async () => { await fetchQr(); await updateStatus(); }, 3000);
});

document.getElementById('btn-disconnect').addEventListener('click', async () => {
    await fetch('{{ route('org.whatsapp.disconnect') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    await updateStatus();
});

updateStatus();
</script>
@endpush
