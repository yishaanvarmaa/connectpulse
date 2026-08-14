@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-ui.stat label="Organizations" :value="$organizationsCount" />
    <x-ui.stat label="Active" :value="$activeOrganizations" />
    <x-ui.stat label="WhatsApp connected" :value="$connectedWhatsApp" />
    <x-ui.stat label="Messages today" :value="$messagesToday" />
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Active connections</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($activeConnections as $connection)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $connection->organization->company_name }}</p>
                        <p class="text-xs text-slate-500">{{ $connection->phone_number ?? '—' }}</p>
                    </div>
                    <x-ui.badge type="success">Connected</x-ui.badge>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">No active connections</p>
            @endforelse
        </div>
    </div>
    <div class="cp-card">
        <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Recent messages</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($recentLogs as $log)
                <div class="px-5 py-3">
                    <div class="flex justify-between gap-2">
                        <p class="text-sm font-medium text-slate-900">{{ $log->organization->company_name }}</p>
                        <x-ui.badge :type="$log->status">{{ ucfirst($log->status) }}</x-ui.badge>
                    </div>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $log->mobile }} — {{ Str::limit($log->message, 60) }}</p>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">No messages yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
