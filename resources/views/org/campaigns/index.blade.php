@extends('layouts.org')

@section('title', 'WhatsApp Campaigns')

@section('page-title', 'WhatsApp Campaigns')
@section('page-subtitle')
    Send WhatsApp messages to your customers
@endsection

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-500">Create and send WhatsApp campaigns to reach your customers with controlled sending.</p>
    <a href="{{ route('org.campaigns.create') }}" class="cp-btn-primary shrink-0">+ Create WhatsApp Campaign</a>
</div>

@if($campaigns->isEmpty())
    <x-ui.empty-state title="No WhatsApp campaigns yet" description="Create your first WhatsApp campaign to reach your customers.">
        <a href="{{ route('org.campaigns.create') }}" class="cp-btn-primary mt-4">+ Create WhatsApp Campaign</a>
    </x-ui.empty-state>
@else
    <div class="grid gap-3">
        @foreach($campaigns as $campaign)
            <a href="{{ route('org.campaigns.show', $campaign) }}" class="cp-card block transition-shadow hover:shadow-md">
                <div class="cp-card-body">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-slate-900 truncate">{{ $campaign->name }}</h3>
                                @php
                                    $badgeClass = match($campaign->status) {
                                        'running' => 'bg-emerald-100 text-emerald-800',
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-slate-100 text-slate-700',
                                        'paused' => 'bg-amber-100 text-amber-800',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-brand-100 text-brand-800',
                                    };
                                @endphp
                                <span class="cp-badge {{ $badgeClass }}">{{ $campaign->statusLabel() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ number_format($campaign->total_recipients) }} recipients
                                · {{ \App\Models\Campaign::audienceTypes()[$campaign->audience_type] ?? $campaign->audience_type }}
                            </p>
                        </div>
                        <div class="text-left sm:text-right shrink-0">
                            @if($campaign->status === 'scheduled' && $campaign->scheduled_at)
                                <p class="text-sm font-medium text-slate-900">{{ $campaign->scheduled_at->format('M j, g:i A') }}</p>
                                <p class="text-xs text-slate-500">Scheduled</p>
                            @elseif(in_array($campaign->status, ['running', 'completed', 'paused']))
                                <p class="text-sm font-medium text-slate-900">{{ $campaign->sent_count }} / {{ $campaign->total_recipients }}</p>
                                <p class="text-xs text-slate-500">Sent</p>
                                @if($campaign->failed_count > 0)
                                    <p class="text-xs text-red-600 mt-0.5">{{ $campaign->failed_count }} failed</p>
                                @endif
                            @else
                                <p class="text-xs text-slate-500">{{ $campaign->created_at->format('M j, Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @if($campaign->status === 'running')
                        <div class="mt-3">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $campaign->progressPercent() }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
@endif
@endsection
