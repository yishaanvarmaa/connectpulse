@extends('layouts.org')

@section('title', $campaign->name)

@section('page-title', $campaign->name)
@section('page-subtitle')
    {{ $campaign->statusLabel() }}
@endsection

@section('content')
<div id="campaign-dashboard" data-status-url="{{ route('org.campaigns.status', $campaign) }}" data-campaign-status="{{ $campaign->status }}">
    {{-- Status banner --}}
    @if($campaign->pause_reason)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ $campaign->pause_reason }}
            @if(str_contains(strtolower($campaign->pause_reason), 'insufficient credits'))
                <a href="{{ route('org.recharge.index') }}" class="ml-1 font-medium underline">Recharge credits</a>
            @endif
        </div>
    @endif

    {{-- Live progress --}}
    <div class="cp-card mb-4">
        <div class="cp-card-body">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    @php
                        $statusBadge = match($campaign->status) {
                            'running' => 'bg-emerald-100 text-emerald-800',
                            'scheduled' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-slate-100 text-slate-700',
                            'paused' => 'bg-amber-100 text-amber-800',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default => 'bg-brand-100 text-brand-800',
                        };
                    @endphp
                    <span id="campaign-status-badge" class="cp-badge {{ $statusBadge }}">{{ strtoupper($campaign->statusLabel()) }}</span>
                    <p class="mt-2 text-2xl font-bold text-slate-900">
                        <span id="sent-count">{{ $campaign->sent_count }}</span> / <span id="total-count">{{ $campaign->total_recipients }}</span> sent
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($campaign->status === 'running')
                        <form method="POST" action="{{ route('org.campaigns.pause', $campaign) }}">@csrf<button type="submit" class="cp-btn-secondary">Pause</button></form>
                        <form method="POST" action="{{ route('org.campaigns.cancel', $campaign) }}">@csrf<button type="submit" class="cp-btn-danger">Cancel</button></form>
                    @elseif($campaign->status === 'paused')
                        <form method="POST" action="{{ route('org.campaigns.resume', $campaign) }}">@csrf<button type="submit" class="cp-btn-primary">Resume</button></form>
                        <form method="POST" action="{{ route('org.campaigns.cancel', $campaign) }}">@csrf<button type="submit" class="cp-btn-danger">Cancel</button></form>
                    @elseif(in_array($campaign->status, ['draft', 'scheduled']))
                        <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}">@csrf<button type="submit" class="cp-btn-primary" @disabled(!$campaign->test_confirmed)>Launch</button></form>
                    @elseif($campaign->status === 'completed' && $campaign->failed_count > 0)
                        <form method="POST" action="{{ route('org.campaigns.retry', $campaign) }}">@csrf<button type="submit" class="cp-btn-secondary">Retry failures</button></form>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    <div id="progress-bar" class="h-full rounded-full bg-brand-600 transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                </div>
                <p class="mt-1 text-xs text-slate-500"><span id="progress-percent">{{ $progressPercent }}</span>% complete</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Sent</p>
                    <p class="text-lg font-semibold text-slate-900" id="stat-sent">{{ $campaign->sent_count }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Pending</p>
                    <p class="text-lg font-semibold text-slate-900" id="stat-pending">{{ $pendingCount }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Failed</p>
                    <p class="text-lg font-semibold text-red-600" id="stat-failed">{{ $campaign->failed_count }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">Credits used</p>
                    <p class="text-lg font-semibold text-slate-900" id="stat-credits">{{ $campaign->credits_used }}</p>
                </div>
            </div>

            @if($currentRecipient && $campaign->status === 'running')
                <div class="mt-4 rounded-lg border border-brand-100 bg-brand-50/50 p-3">
                    <p class="text-xs font-medium text-brand-700">Current recipient</p>
                    <p class="text-sm text-slate-900" id="current-recipient">
                        {{ $currentRecipient->name ?: 'Unknown' }} · +{{ $currentRecipient->phone }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Completed report --}}
    @if($campaign->status === 'completed')
        <div class="cp-card mb-4">
            <div class="cp-card-header">
                <h2 class="text-sm font-semibold text-slate-900">Campaign completed</h2>
            </div>
            <div class="cp-card-body grid gap-3 sm:grid-cols-2 text-sm">
                <div><span class="text-slate-500">Recipients</span><p class="font-medium">{{ $campaign->total_recipients }}</p></div>
                <div><span class="text-slate-500">Sent</span><p class="font-medium text-emerald-700">{{ $campaign->sent_count }}</p></div>
                <div><span class="text-slate-500">Failed</span><p class="font-medium text-red-600">{{ $campaign->failed_count }}</p></div>
                <div><span class="text-slate-500">Credits used</span><p class="font-medium">{{ $campaign->credits_used }}</p></div>
                @if($campaign->started_at)
                    <div><span class="text-slate-500">Started</span><p class="font-medium">{{ $campaign->started_at->format('M j, g:i A') }}</p></div>
                @endif
                @if($campaign->completed_at)
                    <div><span class="text-slate-500">Completed</span><p class="font-medium">{{ $campaign->completed_at->format('M j, g:i A') }}</p></div>
                @endif
                @if($campaign->started_at && $campaign->completed_at)
                    <div><span class="text-slate-500">Duration</span><p class="font-medium">{{ $campaign->started_at->diffForHumans($campaign->completed_at, true) }}</p></div>
                @endif
            </div>
        </div>
    @endif

    {{-- Test & launch for draft --}}
    @if(in_array($campaign->status, ['draft', 'scheduled', 'paused']) && !$campaign->test_confirmed)
        <div class="cp-card mb-4">
            <div class="cp-card-header"><h2 class="text-sm font-semibold">Send test before launch</h2></div>
            <div class="cp-card-body">
                <form method="POST" action="{{ route('org.campaigns.test', $campaign) }}" class="flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <input type="text" name="test_phone" placeholder="Test phone" class="cp-input flex-1" required>
                    <button type="submit" class="cp-btn-primary shrink-0">Send test</button>
                </form>
                <form method="POST" action="{{ route('org.campaigns.confirm-test', $campaign) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="cp-btn-secondary text-xs">Confirm test received</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Message preview --}}
    @if($mediaUrl || $campaign->message_body)
        <div class="cp-card mb-4">
            <div class="cp-card-header"><h2 class="text-sm font-semibold">Message</h2></div>
            <div class="cp-card-body">
                @if($mediaUrl)
                    <img src="{{ $mediaUrl }}" alt="Campaign media" class="mb-3 max-h-48 rounded-lg">
                @endif
                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $campaign->message_body }}</p>
            </div>
        </div>
    @endif

    {{-- Recipient activity --}}
    <div class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">Recipient activity</h2>
        </div>
        <div class="divide-y divide-slate-100" id="recipient-list">
            @forelse($recipients as $recipient)
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $recipient->name ?: 'Unknown' }}</p>
                        <p class="text-xs text-slate-500">+{{ $recipient->phone }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @php
                            $rBadge = match($recipient->status) {
                                'sent', 'delivered' => 'bg-emerald-100 text-emerald-800',
                                'failed', 'skipped' => 'bg-red-100 text-red-700',
                                'sending' => 'bg-blue-100 text-blue-800',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="cp-badge {{ $rBadge }}">{{ $recipient->statusLabel() }}</span>
                        @if($recipient->sent_at)
                            <span class="text-slate-500">{{ $recipient->sent_at->format('g:i A') }}</span>
                        @endif
                        @if($recipient->attempts > 1)
                            <span class="text-slate-400">{{ $recipient->attempts }} attempts</span>
                        @endif
                        @if($recipient->failure_reason)
                            <span class="text-red-600">{{ Str::limit($recipient->failure_reason, 40) }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="p-4 text-sm text-slate-500">No recipients yet.</p>
            @endforelse
        </div>
        @if($recipients->hasPages())
            <div class="border-t border-slate-100 p-3">{{ $recipients->links() }}</div>
        @endif
    </div>
</div>

@if(in_array($campaign->status, ['running', 'paused', 'scheduled']))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.getElementById('campaign-dashboard');
    const statusUrl = dashboard?.dataset.statusUrl;
    if (!statusUrl) return;

    const poll = () => {
        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                document.getElementById('sent-count') && (document.getElementById('sent-count').textContent = data.sent_count);
                document.getElementById('total-count') && (document.getElementById('total-count').textContent = data.total_recipients);
                document.getElementById('stat-sent') && (document.getElementById('stat-sent').textContent = data.sent_count);
                document.getElementById('stat-pending') && (document.getElementById('stat-pending').textContent = data.pending_count);
                document.getElementById('stat-failed') && (document.getElementById('stat-failed').textContent = data.failed_count);
                document.getElementById('stat-credits') && (document.getElementById('stat-credits').textContent = data.credits_used);
                document.getElementById('progress-bar') && (document.getElementById('progress-bar').style.width = data.progress_percent + '%');
                document.getElementById('progress-percent') && (document.getElementById('progress-percent').textContent = data.progress_percent);

                if (data.current_recipient) {
                    const el = document.getElementById('current-recipient');
                    if (el) el.textContent = (data.current_recipient.name || 'Unknown') + ' · +' + data.current_recipient.phone;
                }

                if (['completed', 'cancelled'].includes(data.status)) {
                    clearInterval(interval);
                    location.reload();
                }
            })
            .catch(() => {});
    };

    const interval = setInterval(poll, 5000);
    poll();
});
</script>
@endif
@endsection
