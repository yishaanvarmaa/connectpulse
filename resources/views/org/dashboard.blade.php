@extends('layouts.app')

@section('title', 'Dashboard')

@section('nav')
    <x-org-nav />
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">{{ $organization->company_name }}</h1>
    <p class="mt-1 text-sm text-slate-500">ConnectPulse messaging dashboard</p>
</div>

@if($stats['connection_status'] !== 'Connected')
    <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
        WhatsApp is not connected.
        <a href="{{ route('org.whatsapp.index') }}" class="font-medium underline">Connect now</a>
        before your application can send messages.
    </div>
@endif

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
    <x-stat-card label="Available Credits" :value="number_format($stats['balance'])" />
    <x-stat-card label="WhatsApp Number" :value="$stats['connected_number'] ?? '—'" :hint="$stats['connection_status']" />
    <x-stat-card label="Success Rate" :value="$stats['success_rate'].'%'" />
    <x-stat-card label="Messages Today" :value="number_format($stats['messages_today'])" />
    <x-stat-card label="Messages This Month" :value="number_format($stats['messages_this_month'])" />
    <x-stat-card label="Failed Messages" :value="number_format($stats['failed_messages'])" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-4">Quick Links</h2>
        <div class="space-y-3 text-sm">
            <a href="{{ route('org.whatsapp.index') }}" class="flex justify-between items-center rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">
                <span>WhatsApp Connection</span>
                <span class="text-slate-500">{{ $stats['connection_status'] }}</span>
            </a>
            <a href="{{ route('org.recharge.index') }}" class="flex justify-between items-center rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">
                <span>Recharge Credits</span>
                <span class="text-brand-600">{{ number_format($stats['balance']) }} left</span>
            </a>
            <a href="{{ route('org.api-keys.index') }}" class="flex justify-between items-center rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50">
                <span>API Credentials</span>
                <span class="text-slate-500">View keys</span>
            </a>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900 mb-2">Integration URLs</h2>
        <p class="text-xs text-slate-500 mb-3">Share these with your application (Surabhi Diagnostics, etc.)</p>
        <dl class="space-y-2 text-sm font-mono text-slate-600">
            <div><span class="text-slate-400 font-sans">API:</span> {{ url('/api/v1') }}</div>
            <div><span class="text-slate-400 font-sans">WhatsApp:</span> {{ url('/whatsapp') }}</div>
            <div><span class="text-slate-400 font-sans">Recharge:</span> {{ url('/recharge') }}</div>
        </dl>
    </div>
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
