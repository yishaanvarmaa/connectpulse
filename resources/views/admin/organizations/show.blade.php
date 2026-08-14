@extends('layouts.admin')

@section('title', $organization->company_name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.organizations.index') }}" class="text-sm text-brand-600 hover:text-brand-700">&larr; Back to organizations</a>
    <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $organization->company_name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <x-stat-card label="Credit Balance" :value="number_format($organization->creditWallet?->balance ?? 0)" />
    <x-stat-card label="WhatsApp Status" :value="$organization->whatsappConnection?->getClientStatus() ?? 'Disconnected'" />
    <x-stat-card label="Account Status" :value="ucfirst($organization->status)" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Organization Details</h2>
        <form method="POST" action="{{ route('admin.organizations.update', $organization) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Contact Person</label>
                <input name="contact_person" value="{{ old('contact_person', $organization->contact_person) }}" required
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
                <input name="email" type="email" value="{{ old('email', $organization->email) }}" required
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Mobile</label>
                <input name="mobile" value="{{ old('mobile', $organization->mobile) }}" required
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Details</button>
        </form>
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('admin.organizations.whatsapp', $organization) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">WhatsApp</a>
            <a href="{{ route('admin.organizations.api-test', $organization) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">API Test</a>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">API Credentials</h2>
        <p class="text-xs text-slate-500 mb-4">Both credentials are required by client applications (e.g. Surabhi Diagnostics).</p>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">API Key</label>
                <div class="flex gap-2">
                    <input readonly value="{{ $organization->apiKey?->api_key }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-mono">
                    <x-copy-button :value="$organization->apiKey?->api_key ?? ''" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">API Secret</label>
                <div class="flex gap-2">
                    <input readonly value="{{ $organization->apiKey?->api_secret }}" class="block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-mono">
                    <x-copy-button :value="$organization->apiKey?->api_secret ?? ''" />
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.organizations.api-keys.regenerate-key', $organization) }}" onsubmit="return confirm('Regenerate API key? Client apps must be updated.')">
                @csrf
                <button type="submit" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50">Regenerate Key</button>
            </form>
            <form method="POST" action="{{ route('admin.organizations.api-keys.regenerate-secret', $organization) }}" onsubmit="return confirm('Regenerate API secret? Client apps must be updated.')">
                @csrf
                <button type="submit" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50">Regenerate Secret</button>
            </form>
            <form method="POST" action="{{ route('admin.organizations.api-keys.regenerate', $organization) }}" onsubmit="return confirm('Regenerate both key and secret?')">
                @csrf
                <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs text-red-700 hover:bg-red-50">Regenerate Both</button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Credits</h2>
        <form method="POST" action="{{ route('admin.credits.set', $organization) }}" class="flex gap-2 mb-3">
            @csrf
            <input name="balance" type="number" min="0" value="{{ $organization->creditWallet?->balance ?? 0 }}" required
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm flex-1">
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white whitespace-nowrap">Set Balance</button>
        </form>
        <form method="POST" action="{{ route('admin.credits.store', $organization) }}" class="flex gap-2 mb-4">
            @csrf
            <input name="amount" type="number" min="1" placeholder="Credits to add" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm flex-1">
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 whitespace-nowrap">Add Credits</button>
        </form>
        <div class="flex gap-2">
            @if($organization->isActive())
                <form method="POST" action="{{ route('admin.organizations.suspend', $organization) }}">@csrf<button class="rounded-lg border border-amber-300 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">Suspend</button></form>
            @else
                <form method="POST" action="{{ route('admin.organizations.activate', $organization) }}">@csrf<button class="rounded-lg border border-green-300 px-4 py-2 text-sm text-green-700 hover:bg-green-50">Activate</button></form>
            @endif
            <form method="POST" action="{{ route('admin.organizations.destroy', $organization) }}" onsubmit="return confirm('Delete this organization permanently?')">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-2">Public API</h2>
        <p class="text-xs text-slate-500 mb-3">Base URL: <code class="bg-slate-100 px-1 rounded">{{ url('/api/v1') }}</code></p>
        <dl class="space-y-1 text-xs font-mono text-slate-600">
            <div><span class="text-green-600">POST</span> /messages/send</div>
            <div><span class="text-green-600">POST</span> /messages/bulk</div>
            <div><span class="text-blue-600">GET</span> /connection</div>
            <div><span class="text-blue-600">GET</span> /credits/balance</div>
        </dl>
    </div>
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-900">Message Logs</h2>
    </div>
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Mobile</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Message</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Credits</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Timestamp</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($messageLogs as $log)
                <tr>
                    <td class="px-6 py-4 text-sm text-slate-900">{{ $log->mobile }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">{{ $log->message }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="{{ $log->status === 'sent' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $log->credits_used }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $log->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No messages yet</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-200">{{ $messageLogs->links() }}</div>
</div>
@endsection
