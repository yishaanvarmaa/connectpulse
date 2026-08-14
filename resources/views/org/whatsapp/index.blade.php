@extends('layouts.org')

@section('title', 'WhatsApp')

@php $pageTitle = 'WhatsApp'; $pageSubtitle = 'Connection & messaging'; @endphp

@section('content')
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2 cp-card cp-card-body">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</p>
                <p id="connection-status" class="mt-1 text-2xl font-semibold text-slate-900">{{ $connection?->getClientStatus() ?? 'Disconnected' }}</p>
                <p id="connected-phone" class="mt-1 text-sm text-slate-500">{{ $connection?->isConnected() ? '+'.$connection->phone_number : '' }}</p>
                <p id="status-hint" class="mt-2 text-xs text-amber-600 hidden"></p>
            </div>
            <div id="status-indicator" class="flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium {{ $connection?->isConnected() ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                <span class="h-2 w-2 rounded-full {{ $connection?->isConnected() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                <span id="status-pill-text">{{ $connection?->isConnected() ? 'Connected' : 'Offline' }}</span>
            </div>
        </div>

        <div id="qr-container" class="hidden mt-6 rounded-xl border border-slate-200 bg-slate-50 p-6 text-center">
            <p class="mb-4 text-sm text-slate-600">Scan with WhatsApp → Linked Devices → Link a Device</p>
            <div class="inline-block rounded-xl border border-white bg-white p-3 shadow-sm">
                <img id="qr-image" src="" alt="WhatsApp QR Code" class="h-56 w-56">
            </div>
            <p class="mt-3 text-xs text-slate-400">QR refreshes automatically. Existing sessions are not affected.</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            <button id="btn-connect" type="button" class="cp-btn-primary">Connect WhatsApp</button>
            <button id="btn-disconnect" type="button" class="cp-btn-danger {{ $connection?->isConnected() ? '' : 'hidden' }}">Disconnect</button>
            <a href="{{ route('org.logs.index') }}" class="cp-btn-secondary">Open inbox / logs</a>
        </div>
    </div>

    <div class="space-y-5">
        <div class="cp-card cp-card-body">
            <p class="text-xs font-medium text-slate-500">Quick info</p>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Organization</dt><dd class="font-medium">{{ $organization->company_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Credits</dt><dd class="font-medium">{{ number_format($shellBalance ?? 0) }}</dd></div>
            </dl>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">
            Your WhatsApp session is preserved on reconnect. Do not disconnect unless necessary.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let pollInterval = null;
function formatPhone(phone){return phone? '+'+String(phone).replace(/^\+/,''):'';}
function showHint(m){const el=document.getElementById('status-hint'); if(m){el.textContent=m;el.classList.remove('hidden');}else el.classList.add('hidden');}
function showQr(d){if(!d)return; document.getElementById('qr-container').classList.remove('hidden'); document.getElementById('qr-image').src=d;}
function stopPolling(){if(pollInterval){clearInterval(pollInterval);pollInterval=null;}}
function startPolling(ms=2000){stopPolling(); pollInterval=setInterval(async()=>{await fetchQr();await updateStatus();},ms);}
async function updateStatus(){
    try{
        const res=await fetch('{{ route('org.whatsapp.status') }}'); const data=await res.json();
        document.getElementById('connection-status').textContent=data.display_status||data.status;
        document.getElementById('connected-phone').textContent=data.connected?formatPhone(data.phone):'';
        const connected=!!data.connected;
        document.getElementById('status-indicator').className='flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium '+(connected?'border-emerald-200 bg-emerald-50 text-emerald-700':'border-slate-200 bg-slate-50 text-slate-600');
        document.getElementById('status-pill-text').textContent=connected?'Connected':'Offline';
        document.getElementById('btn-disconnect').classList.toggle('hidden',!connected);
        if(connected){document.getElementById('qr-container').classList.add('hidden');showHint('');stopPolling();return;}
        if(data.status==='logging_in'){showHint('QR scanned — finishing login…');startPolling(1500);return;}
        if(data.status==='reconnecting'){showHint('Connection unstable. Click Connect for fresh QR.');startPolling();return;}
        if(data.needs_qr||data.status==='qr_required'){showHint('Scan the QR code below.');startPolling();}
    }catch(e){showHint('Could not reach WhatsApp status.');}
}
async function fetchQr(){try{const res=await fetch('{{ route('org.whatsapp.qr') }}');const data=await res.json();if(data.qr){showQr(data.qr);showHint('Scan the QR code below.');}}catch(e){}}
document.getElementById('btn-connect').addEventListener('click',async()=>{
    const btn=document.getElementById('btn-connect'); btn.disabled=true; stopPolling(); showHint('Generating QR…');
    try{const res=await fetch('{{ route('org.whatsapp.connect') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});const data=await res.json();
    if(data.qr){showQr(data.qr);}else if(data.error){showHint(data.error);} startPolling(1500); await fetchQr();
    }catch(e){showHint('Connect failed.');}finally{btn.disabled=false;}
});
document.getElementById('btn-disconnect').addEventListener('click',async()=>{stopPolling();await fetch('{{ route('org.whatsapp.disconnect') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf}});document.getElementById('qr-container').classList.add('hidden');await updateStatus();});
updateStatus();
</script>
@endpush
