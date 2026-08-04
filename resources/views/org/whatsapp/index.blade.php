@extends('layouts.app')

@section('title', 'WhatsApp Connection')

@section('nav')
    <x-org-nav />
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
                <p id="connected-phone" class="text-sm text-slate-500 mt-1">{{ $connection?->isConnected() ? $connection->phone_number : '' }}</p>
                <p id="status-hint" class="text-xs text-amber-600 mt-2 hidden"></p>
            </div>
            <div id="status-indicator" class="h-3 w-3 rounded-full {{ $connection?->isConnected() ? 'bg-green-500' : 'bg-slate-300' }}"></div>
        </div>

        <div id="qr-container" class="hidden mb-6 text-center">
            <p class="text-sm text-slate-600 mb-4">Scan this QR code with WhatsApp on your phone</p>
            <div class="inline-block p-4 bg-white border border-slate-200 rounded-lg">
                <img id="qr-image" src="" alt="WhatsApp QR Code" class="w-64 h-64">
            </div>
            <p class="text-xs text-slate-400 mt-3">Open WhatsApp → Linked Devices → Link a Device</p>
            <p class="text-xs text-slate-400 mt-1">QR refreshes automatically. If it expires, click Connect again.</p>
        </div>

        <div class="flex gap-3">
            <button id="btn-connect" type="button" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Connect WhatsApp</button>
            <button id="btn-disconnect" type="button" class="hidden rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Disconnect</button>
        </div>
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

function showHint(message) {
    const el = document.getElementById('status-hint');
    if (message) {
        el.textContent = message;
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function showQr(dataUrl) {
    if (!dataUrl) return;
    document.getElementById('qr-container').classList.remove('hidden');
    document.getElementById('qr-image').src = dataUrl;
}

function startPolling(ms = 2000) {
    stopPolling();
    pollInterval = setInterval(async () => {
        await fetchQr();
        await updateStatus();
    }, ms);
}

function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

async function updateStatus() {
    try {
        const res = await fetch('{{ route('org.whatsapp.status') }}');
        const data = await res.json();

        document.getElementById('connection-status').textContent = data.display_status || data.status;
        document.getElementById('connected-phone').textContent = data.connected ? formatPhone(data.phone) : '';
        document.getElementById('status-indicator').className = 'h-3 w-3 rounded-full ' + (data.connected ? 'bg-green-500' : 'bg-slate-300');
        document.getElementById('btn-disconnect').classList.toggle('hidden', !data.connected);

        if (data.connected) {
            document.getElementById('qr-container').classList.add('hidden');
            showHint('');
            stopPolling();
            return;
        }

        if (data.status === 'reconnecting') {
            showHint('Connection is unstable. Click Connect WhatsApp to scan a fresh QR code.');
            startPolling();
            return;
        }

        if (data.needs_qr || data.status === 'qr_required') {
            showHint('Scan the QR code below to link your WhatsApp number.');
            startPolling();
        }
    } catch (e) {
        showHint('Could not reach WhatsApp status. Refresh the page and try again.');
    }
}

async function fetchQr() {
    try {
        const res = await fetch('{{ route('org.whatsapp.qr') }}');
        const data = await res.json();
        if (data.qr) {
            showQr(data.qr);
            showHint('Scan the QR code below to link your WhatsApp number.');
        }
    } catch (e) {
        showHint('Could not load QR code. Click Connect WhatsApp again.');
    }
}

document.getElementById('btn-connect').addEventListener('click', async () => {
    const btn = document.getElementById('btn-connect');
    btn.disabled = true;
    stopPolling();
    showHint('Generating QR code… please wait.');
    document.getElementById('connection-status').textContent = 'Scan QR to Connect';

    try {
        const res = await fetch('{{ route('org.whatsapp.connect') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.qr) {
            showQr(data.qr);
            showHint('Scan the QR code below to link your WhatsApp number.');
        } else if (data.error) {
            showHint(data.error);
        } else {
            showHint('Waiting for QR… keep this page open.');
        }

        startPolling(1500);
        await fetchQr();
    } catch (e) {
        showHint('Connect failed. Refresh and try again, or contact support.');
    } finally {
        btn.disabled = false;
    }
});

document.getElementById('btn-disconnect').addEventListener('click', async () => {
    stopPolling();
    await fetch('{{ route('org.whatsapp.disconnect') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
    document.getElementById('qr-container').classList.add('hidden');
    await updateStatus();
});

updateStatus();
</script>
@endpush
