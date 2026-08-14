@extends('layouts.admin')

@section('title', $organization->company_name.' — API Test')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">API Test — {{ $organization->company_name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Sends a real request to <code class="bg-slate-100 px-1 rounded">POST /api/v1/messages/send</code></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500">WhatsApp</p>
                <p class="font-medium">{{ $organization->whatsappConnection?->getClientStatus() ?? 'Disconnected' }}</p>
            </div>
            <div>
                <p class="text-slate-500">Credits</p>
                <p class="font-medium">{{ number_format($organization->creditWallet?->balance ?? 0) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.organizations.api-test.send', $organization) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mobile Number</label>
                <input name="mobile" type="text" required value="{{ old('mobile') }}" placeholder="919876543210"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                <textarea name="message" rows="4" required placeholder="Test message from ConnectPulse"
                          class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('message', 'Test message from ConnectPulse admin.') }}</textarea>
                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Send Test Message</button>
        </form>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">API Response</h2>

        @if($lastStatus)
            <div class="mb-4 rounded-lg px-4 py-3 text-sm {{ $lastStatus === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' }}">
                {{ $lastStatus === 'success' ? 'Success — message queued via API' : 'Failed — see response below' }}
            </div>
        @else
            <p class="text-sm text-slate-500">Submit the form to see the live API response.</p>
        @endif

        @if($lastResponse)
            <p class="text-xs text-slate-500 mb-2">HTTP {{ $lastResponse['http_status'] ?? '—' }}</p>
            <pre class="rounded-lg bg-slate-900 text-green-400 p-4 text-xs overflow-x-auto">{{ json_encode($lastResponse['body'] ?? $lastResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
    </div>
</div>
@endsection
