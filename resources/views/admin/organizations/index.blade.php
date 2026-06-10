@extends('layouts.app')

@section('title', 'Organizations')

@section('nav')
    <x-nav-link :href="route('admin.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('admin.organizations.index')" :active="true">Organizations</x-nav-link>
    <x-nav-link :href="route('admin.credits.index')">Credits</x-nav-link>
    <x-nav-link :href="route('admin.logs.index')">Logs</x-nav-link>
@endsection

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Organizations</h1>
        <p class="mt-1 text-sm text-slate-500">Manage all client organizations</p>
    </div>
    <a href="{{ route('admin.organizations.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add Organization</a>
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Company</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Credits</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">WhatsApp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($organizations as $org)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $org->company_name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $org->contact_person }}<br>{{ $org->email }}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">{{ number_format($org->creditWallet?->balance ?? 0) }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $org->whatsappConnection?->getClientStatus() ?? 'Disconnected' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $org->isActive() ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                            {{ ucfirst($org->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm space-x-3">
                        <a href="{{ route('admin.organizations.show', $org) }}" class="text-brand-600 hover:text-brand-700">View</a>
                        <a href="{{ route('admin.organizations.whatsapp', $org) }}" class="text-slate-600 hover:text-slate-900">WhatsApp</a>
                        <a href="{{ route('admin.organizations.api-test', $org) }}" class="text-slate-600 hover:text-slate-900">API Test</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-slate-200">{{ $organizations->links() }}</div>
</div>
@endsection
