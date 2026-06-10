@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('nav')
    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
    <x-nav-link :href="route('admin.organizations.index')" :active="request()->routeIs('admin.organizations.*')">Organizations</x-nav-link>
    <x-nav-link :href="route('admin.credits.index')" :active="request()->routeIs('admin.credits.*')">Credits</x-nav-link>
    <x-nav-link :href="route('admin.logs.index')" :active="request()->routeIs('admin.logs.*')">Logs</x-nav-link>
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Super Admin Dashboard</h1>
    <p class="mt-1 text-sm text-slate-500">Global overview of ConnectPulse platform</p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <x-stat-card label="Total Organizations" :value="$organizationsCount" />
    <x-stat-card label="Active Organizations" :value="$activeOrganizations" />
    <x-stat-card label="WhatsApp Connected" :value="$connectedWhatsApp" />
    <x-stat-card label="Messages Today" :value="$messagesToday" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-900">Active Connections</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($activeConnections as $connection)
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium text-slate-900">{{ $connection->organization->company_name }}</p>
                        <p class="text-sm text-slate-500">{{ $connection->phone_number ?? '—' }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Connected</span>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-slate-500 text-center">No active connections</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-900">Recent Messages</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentLogs as $log)
                <div class="px-6 py-4">
                    <div class="flex justify-between">
                        <p class="font-medium text-slate-900 text-sm">{{ $log->organization->company_name }}</p>
                        <span class="text-xs {{ $log->status === 'sent' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">{{ ucfirst($log->status) }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">{{ $log->mobile }} — {{ Str::limit($log->message, 50) }}</p>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-slate-500 text-center">No messages yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
