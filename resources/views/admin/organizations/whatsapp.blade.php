@extends('layouts.app')

@section('title', $organization->company_name.' — WhatsApp')

@section('nav')
    <x-admin-org-nav :organization="$organization" />
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">WhatsApp — {{ $organization->company_name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Isolated session for organization #{{ $organization->id }}</p>
</div>

<div class="max-w-2xl rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500">Status</p>
            <p id="connection-status" class="text-lg font-semibold text-slate-900">{{ $connection?->getClientStatus() ?? 'Disconnected' }}</p>
            <p id="connected-phone" class="text-sm text-slate-600 mt-1">{{ $connection?->phone_number ? '+'.$connection->phone_number : '' }}</p>
            <p id="connected-at" class="text-xs text-slate-400 mt-1">
                @if($connection?->connected_at)
                    Last connected: {{ $connection->connected_at->format('M d, Y H:i') }}
                @endif
            </p>
        </div>
        <div id="status-indicator" class="h-3 w-3 rounded-full {{ $connection?->isConnected() ? 'bg-green-500' : 'bg-slate-300' }}"></div>
    </div>

    <div id="qr-container" class="hidden mb-6 text-center">
        <p class="text-sm text-slate-600 mb-4">Scan this QR code with WhatsApp on the organization's phone</p>
        <div class="inline-block p-4 bg-white border border-slate-200 rounded-lg">
            <img id="qr-image" src="" alt="WhatsApp QR Code" class="w-64 h-64">
        </div>
    </div>

    <div class="flex gap-3">
        <button id="btn-connect" type="button" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate QR / Connect</button>
        <button id="btn-disconnect" type="button" class="hidden rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Disconnect</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let pollInterval = null;

function formatPhone(phone) {
    return phone ? '+' + String(phone).replace(/^\+/, '') : '';
}

function formatConnectedAt(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return 'Last connected: ' + d.toLocaleString();
}

async function updateStatus() {
    const res = await fetch('{{ route('admin.organizations.whatsapp.status', $organization) }}');
    const data = await res.json();
    document.getElementById('connection-status').textContent = data.status || 'Disconnected';
    document.getElementById('connected-phone').textContent = formatPhone(data.phone);
    document.getElementById('connected-at').textContent = data.connected ? formatConnectedAt(data.connected_at) : '';
    document.getElementById('status-indicator').className = 'h-3 w-3 rounded-full ' + (data.connected ? 'bg-green-500' : 'bg-slate-300');
    document.getElementById('btn-disconnect').classList.toggle('hidden', !data.connected);
    if (data.connected) {
        document.getElementById('qr-container').classList.add('hidden');
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }
}

async function fetchQr() {
    const res = await fetch('{{ route('admin.organizations.whatsapp.qr', $organization) }}');
    const data = await res.json();
    if (data.qr) {
        document.getElementById('qr-container').classList.remove('hidden');
        document.getElementById('qr-image').src = data.qr;
    }
}

document.getElementById('btn-connect').addEventListener('click', async () => {
    await fetch('{{ route('admin.organizations.whatsapp.connect', $organization) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    document.getElementById('connection-status').textContent = 'Reconnect Required';
    await fetchQr();
    if (!pollInterval) pollInterval = setInterval(async () => { await fetchQr(); await updateStatus(); }, 3000);
});

document.getElementById('btn-disconnect').addEventListener('click', async () => {
    await fetch('{{ route('admin.organizations.whatsapp.disconnect', $organization) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    await updateStatus();
});

updateStatus();
</script>
@endpush
