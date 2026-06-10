@extends('layouts.app')

@section('title', 'Dashboard')

@section('nav')
    <x-nav-link :href="route('org.dashboard')" :active="true">Dashboard</x-nav-link>
    <x-nav-link :href="route('org.whatsapp.index')">WhatsApp</x-nav-link>
    <x-nav-link :href="route('org.api-keys.index')">API Keys</x-nav-link>
    <x-nav-link :href="route('org.credits.index')">Credits</x-nav-link>
    <x-nav-link :href="route('org.logs.index')">Logs</x-nav-link>
    <x-nav-link :href="route('org.settings.index')">Settings</x-nav-link>
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">{{ $organization->company_name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Messaging dashboard overview</p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
    <x-stat-card label="Credit Balance" :value="number_format($stats['balance'])" />
    <x-stat-card label="Connected Number" :value="$stats['connected_number'] ?? '—'" :hint="$stats['connection_status']" />
    <x-stat-card label="Success Rate" :value="$stats['success_rate'].'%'" />
    <x-stat-card label="Messages Today" :value="number_format($stats['messages_today'])" />
    <x-stat-card label="Messages This Month" :value="number_format($stats['messages_this_month'])" />
    <x-stat-card label="Failed Messages" :value="number_format($stats['failed_messages'])" />
</div>

<div class="rounded-xl bg-white border border-slate-200 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-900">Recent Messages</h2>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse($recentLogs as $log)
            <div class="px-6 py-4 flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $log->mobile }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ Str::limit($log->message, 80) }}</p>
                </div>
                <span class="text-xs font-medium {{ $log->status === 'sent' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">{{ ucfirst($log->status) }}</span>
            </div>
        @empty
            <p class="px-6 py-8 text-sm text-slate-500 text-center">No messages sent yet</p>
        @endforelse
    </div>
</div>
@endsection
