@extends('layouts.org')

@section('title', 'WhatsApp Campaigns')

@section('page-title', 'WhatsApp Campaigns')
@section('page-subtitle')
    Create, test, and send WhatsApp messages to your customers
@endsection

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-slate-500">Compose a message → send yourself a test → launch to your audience.</p>
    <a href="{{ route('org.campaigns.create') }}" class="cp-btn-primary shrink-0">+ New campaign</a>
</div>

@if($campaigns->isEmpty())
    <x-ui.empty-state title="No WhatsApp campaigns yet" description="Create a campaign, test it on your phone, then send it to your customers.">
        <a href="{{ route('org.campaigns.create') }}" class="cp-btn-primary mt-4">+ Create campaign</a>
    </x-ui.empty-state>
@else
    <div class="grid gap-3">
        @foreach($campaigns as $campaign)
            <div class="cp-card transition-shadow hover:shadow-md">
                <div class="cp-card-body">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <a href="{{ route('org.campaigns.show', $campaign) }}" class="min-w-0 flex-1 block">
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
                            @if($campaign->status === 'draft' && ! $campaign->test_confirmed)
                                <p class="mt-1 text-xs font-medium text-amber-700">Needs test before launch</p>
                            @endif
                        </a>
                        <div class="flex items-start gap-3 shrink-0">
                            <div class="text-left sm:text-right">
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
                            @if($campaign->canBeDeleted())
                                <form method="POST" action="{{ route('org.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Delete campaign" aria-label="Delete campaign">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
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
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $campaigns->links() }}</div>
@endif
@endsection
