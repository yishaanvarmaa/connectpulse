@extends('layouts.org')

@section('title', $campaign->name)

@section('page-title', $campaign->name)
@section('page-subtitle')
    {{ $campaign->statusLabel() }}
@endsection

@php
    $showLaunchWizard = $campaign->status === 'draft';
    $testSent = filled($campaign->test_phone);
    $canLaunch = $campaign->status === 'draft' && (bool) $campaign->test_confirmed;
    $hasSchedule = $campaign->scheduled_at && $campaign->scheduled_at->isFuture();
    $canDelete = in_array($campaign->status, ['draft', 'completed', 'cancelled'], true);
@endphp

@section('content')
<div id="campaign-dashboard" data-status-url="{{ route('org.campaigns.status', $campaign) }}" data-campaign-status="{{ $campaign->status }}">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('org.campaigns.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All campaigns
        </a>
        @if($canDelete)
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

    @if($showLaunchWizard)
        <div class="cp-card mb-4">
            <div class="cp-card-header">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Step 2 of 2</p>
                <h2 class="text-sm font-semibold text-slate-900">Test and launch</h2>
            </div>
            <div class="cp-card-body space-y-5">
                <div>
                    <p class="text-sm font-semibold text-slate-900">1. Review message</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ number_format($campaign->total_recipients) }} recipients</p>
                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        @if(!empty($mediaUrl))
                            <img src="{{ $mediaUrl }}" alt="Campaign photo" class="mb-2 max-h-40 rounded-lg object-cover">
                        @endif
                        <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $campaign->message_body }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900">2. Send a test to your phone</p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        We will send the exact campaign message{{ ! empty($mediaUrl) ? ' with your photo' : '' }}.
                    </p>
                    @if(! $campaign->test_confirmed)
                        <form method="POST" action="{{ route('org.campaigns.test', $campaign) }}" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-end">
                            @csrf
                            <div class="flex-1">
                                <label for="test_phone" class="mb-1 block text-xs font-medium text-slate-600">Your WhatsApp number</label>
                                <input type="text" name="test_phone" id="test_phone" value="{{ old('test_phone', $campaign->test_phone) }}" placeholder="919876543210" required class="cp-input w-full">
                                <p class="mt-1 text-[11px] text-slate-400">Country code, no + or spaces</p>
                            </div>
                            <button type="submit" class="cp-btn-primary shrink-0">{{ $testSent ? 'Resend test' : 'Send test now' }}</button>
                        </form>
                    @else
                        <p class="mt-2 text-sm text-emerald-700">
                            Test confirmed{{ $campaign->test_phone ? ' for +'.$campaign->test_phone : '' }}
                        </p>
                    @endif
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900">3. Confirm you received it</p>
                    @if($campaign->test_confirmed)
                        <p class="mt-0.5 text-xs text-emerald-700">Ready to launch.</p>
                    @elseif($testSent)
                        <p class="mt-0.5 text-xs text-slate-500">Check WhatsApp, then confirm below.</p>
                        <form method="POST" action="{{ route('org.campaigns.confirm-test', $campaign) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="cp-btn-secondary">Yes, I received the test</button>
                        </form>
                    @else
                        <p class="mt-0.5 text-xs text-slate-400">Send a test first.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">4. Launch campaign</p>
                    @if($hasSchedule)
                        <p class="mt-0.5 text-xs text-slate-500">Scheduled for {{ $campaign->scheduled_at->format('M j, Y g:i A') }}</p>
                    @else
                        <p class="mt-0.5 text-xs text-slate-500">Messages start after you launch.</p>
                    @endif

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($canLaunch)
                            <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}">
                                @csrf
                                <input type="hidden" name="send_now" value="{{ $hasSchedule ? '0' : '1' }}">
                                <button type="submit" class="cp-btn-primary">{{ $hasSchedule ? 'Schedule launch' : 'Launch now' }}</button>
                            </form>
                            @if($hasSchedule)
                                <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}">
                                    @csrf
                                    <input type="hidden" name="send_now" value="1">
                                    <button type="submit" class="cp-btn-secondary">Send now instead</button>
                                </form>
                            @endif
                        @else
                            <button type="button" class="cp-btn-primary opacity-50" disabled>Launch locked</button>
                            <span class="self-center text-xs text-slate-500">Complete the test steps first</span>
                        @endif
                    </div>

                    @if(! $campaign->test_confirmed)
                        <form method="POST" action="{{ route('org.campaigns.launch', $campaign) }}" class="mt-3" onsubmit="return confirm('Launch without testing?');">
                            @csrf
                            <input type="hidden" name="skip_test" value="1">
                            <input type="hidden" name="send_now" value="{{ $hasSchedule ? '0' : '1' }}">
                            <button type="submit" class="text-xs font-medium text-amber-700 hover:underline">Skip test and launch anyway</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(! $showLaunchWizard)
        <div class="cp-card mb-4">
            <div class="cp-card-body">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        @php
                            $statusBadge = match ($campaign->status) {
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
            <div class="cp-card-header"><h2 class="text-sm font-semibold text-slate-900">Campaign completed</h2></div>
            <div class="cp-card-body grid gap-3 text-sm sm:grid-cols-2">
                <div><span class="text-slate-500">Recipients</span><p class="font-medium">{{ $campaign->total_recipients }}</p></div>
                <div><span class="text-slate-500">Sent</span><p class="font-medium text-emerald-700">{{ $campaign->sent_count }}</p></div>
                <div><span class="text-slate-500">Failed</span><p class="font-medium text-red-600">{{ $campaign->failed_count }}</p></div>
                <div><span class="text-slate-500">Credits used</span><p class="font-medium">{{ $campaign->credits_used }}</p></div>
            </div>
        </div>
    @endif

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
                            $rBadge = match ($recipient->status) {
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
                        @if($recipient->failure_reason)
                            <span class="text-red-600">{{ \Illuminate\Support\Str::limit($recipient->failure_reason, 40) }}</span>
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

@if(in_array($campaign->status, ['running', 'paused', 'scheduled'], true))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.getElementById('campaign-dashboard');
    const statusUrl = dashboard?.dataset.statusUrl;
    if (!statusUrl) return;

    const poll = () => {
        fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
                set('sent-count', data.sent_count);
                set('total-count', data.total_recipients);
                set('stat-sent', data.sent_count);
                set('stat-pending', data.pending_count);
                set('stat-failed', data.failed_count);
                set('stat-credits', data.credits_used);
                set('progress-percent', data.progress_percent);
                const bar = document.getElementById('progress-bar');
                if (bar) bar.style.width = data.progress_percent + '%';
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
