@extends('layouts.org')

@section('title', $campaign->name)

@section('page-title', $campaign->name)
@section('page-subtitle')
    {{ $campaign->statusLabel() }}
@endsection

@php
    $showLaunchWizard = $campaign->status === 'draft';
    $testSent = $campaign->hasTestBeenSent();
    $canLaunch = $campaign->canLaunch();
    $hasSchedule = $campaign->scheduled_at && $campaign->scheduled_at->isFuture();
@endphp

@section('content')
<div id="campaign-dashboard" data-status-url="{{ route('org.campaigns.status', $campaign) }}" data-campaign-status="{{ $campaign->status }}">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('org.campaigns.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All campaigns
        </a>
        @if($campaign->canBeDeleted())
            <form method="POST" action="{{ route('org.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="cp-btn-ghost !text-xs text-red-600 hover:text-red-700">Delete campaign</button>
            </form>
        @endif
    </div>

    @if($campaign->pause_reason)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ $campaign->pause_reason }}
            @if(str_contains(strtolower($campaign->pause_reason), 'insufficient credits'))
                <a href="{{ route('org.recharge.index') }}" class="ml-1 font-medium underline">Recharge credits</a>
            @endif
        </div>
    @endif

    {{-- Step-by-step launch for drafts --}}
    @if($showLaunchWizard)
        <div class="cp-card mb-4 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Step 2 of 2 · Test &amp; launch</p>
                <h2 class="mt-0.5 text-base font-semibold text-slate-900">Almost ready — check your message first</h2>
            </div>
            <div class="divide-y divide-slate-100">
                {{-- Step 1: Review --}}
                <div class="px-4 py-4 sm:px-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">✓</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">1. Review message</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ number_format($campaign->total_recipients) }} recipients · {{ $campaign->delay_min_seconds }}–{{ $campaign->delay_max_seconds }}s between sends</p>
                            <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3">
                                @if($mediaUrl)
                                    <img src="{{ $mediaUrl }}" alt="Campaign photo" class="mb-2 max-h-40 rounded-lg object-cover">
                                @endif
                                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $campaign->message_body }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Send test --}}
                <div class="px-4 py-4 sm:px-5">
                    <div class="flex items-start gap-3">
                        <span @class([
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            'bg-emerald-100 text-emerald-700' => $testSent || $campaign->test_confirmed,
                            'bg-brand-100 text-brand-700' => ! $testSent && ! $campaign->test_confirmed,
                        ])>
                            {{ ($testSent || $campaign->test_confirmed) ? '✓' : '2' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">2. Send a test to your phone</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                We’ll send the exact campaign message
                                @if($mediaUrl)<span class="font-medium text-slate-700">including your photo</span>@endif
                                so you can check how it looks.
                            </p>

                            @if(! $campaign->test_confirmed)
                                <form method="POST" action="{{ route('org.campaigns.test', $campaign) }}" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end">
                                    @csrf
                                    <div class="flex-1">
                                        <label for="test_phone" class="mb-1 block text-xs font-medium text-slate-600">Your WhatsApp number</label>
                                        <input
                                            type="text"
                                            name="test_phone"
                                            id="test_phone"
                                            value="{{ old('test_phone', $campaign->test_phone) }}"
                                            placeholder="919876543210"
                                            required
                                            class="cp-input w-full"
                                        >
                                        <p class="mt-1 text-[11px] text-slate-400">Include country code, no + or spaces</p>
                                    </div>
                                    <button type="submit" class="cp-btn-primary shrink-0">
                                        {{ $testSent ? 'Resend test' : 'Send test now' }}
                                    </button>
                                </form>
                            @else
                                <p class="mt-2 text-sm text-emerald-700">Test confirmed@if($campaign->test_phone) for +{{ $campaign->test_phone }}@endif</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Step 3: Confirm --}}
                <div class="px-4 py-4 sm:px-5">
                    <div class="flex items-start gap-3">
                        <span @class([
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            'bg-emerald-100 text-emerald-700' => $campaign->test_confirmed,
                            'bg-slate-100 text-slate-500' => ! $campaign->test_confirmed,
                        ])>
                            {{ $campaign->test_confirmed ? '✓' : '3' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">3. Confirm you received it</p>
                            @if($campaign->test_confirmed)
                                <p class="mt-0.5 text-xs text-emerald-700">You’re ready to launch.</p>
                            @elseif($testSent)
                                <p class="mt-0.5 text-xs text-slate-500">Open WhatsApp and check the test. If photo + text look good, confirm below.</p>
                                <form method="POST" action="{{ route('org.campaigns.confirm-test', $campaign) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="cp-btn-secondary">Yes, I received the test</button>
                                </form>
                            @else
                                <p class="mt-0.5 text-xs text-slate-400">Send a test first, then confirm here.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Step 4: Launch --}}
                <div class="px-4 py-4 sm:px-5 bg-slate-50/80">
                    <div class="flex items-start gap-3">
                        <span @class([
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            'bg-brand-600 text-white' => $canLaunch,
                            'bg-slate-200 text-slate-500' => ! $canLaunch,
                        ])>4</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">4. Launch campaign</p>
                            @if($hasSchedule)
                                <p class="mt-0.5 text-xs text-slate-500">Scheduled for {{ $campaign->scheduled_at->format('M j, Y · g:i A') }}</p>
                            @else
                                <p class="mt-0.5 text-xs text-slate-500">Messages will start sending one by one after you launch.</p>
                            @endif

                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                @if($canLaunch)
                                    <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}">
                                        @csrf
                                        @if($hasSchedule)
                                            <input type="hidden" name="send_now" value="0">
                                            <button type="submit" class="wa-composer-btn-launch">Schedule launch</button>
                                        @else
                                            <input type="hidden" name="send_now" value="1">
                                            <button type="submit" class="wa-composer-btn-launch">Launch now</button>
                                        @endif
                                    </form>
                                    @if($hasSchedule)
                                        <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}">
                                            @csrf
                                            <input type="hidden" name="send_now" value="1">
                                            <button type="submit" class="cp-btn-secondary">Send now instead</button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" class="wa-composer-btn-launch opacity-50" disabled>Launch locked</button>
                                    <span class="text-xs text-slate-500">Complete the test steps above first</span>
                                @endif
                            </div>

                            @if(! $campaign->test_confirmed)
                                <details class="mt-4 text-xs text-slate-500">
                                    <summary class="cursor-pointer font-medium text-slate-600 hover:text-slate-800">Skip test (not recommended)</summary>
                                    <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}" class="mt-2" onsubmit="return confirm('Launch without testing? Your photo and message won’t be checked first.');">
                                        @csrf
                                        <input type="hidden" name="skip_test" value="1">
                                        <input type="hidden" name="send_now" value="{{ $hasSchedule ? '0' : '1' }}">
                                        <button type="submit" class="cp-btn-ghost !text-xs text-amber-700">Skip test and launch anyway</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Live progress (active / finished campaigns) --}}
    @if(! $showLaunchWizard)
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
                        @elseif($campaign->status === 'scheduled')
                            <form method="POST" action="{{ route('org.campaigns.cancel', $campaign) }}">@csrf<button type="submit" class="cp-btn-danger">Cancel schedule</button></form>
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
    @endif

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
            </div>
        </div>
    @endif

    {{-- Message preview for non-draft --}}
    @if(! $showLaunchWizard && ($mediaUrl || $campaign->message_body))
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

    <div class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">Recipients</h2>
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
