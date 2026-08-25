@extends('layouts.org')

@section('title', 'Inbox')

@section('page-title', 'Inbox')
@section('page-subtitle', 'WhatsApp conversations')

@section('content')
<div class="flex flex-col xl:grid xl:grid-cols-12 xl:gap-0 xl:border xl:border-slate-200 xl:rounded-xl xl:overflow-hidden xl:bg-white xl:min-h-[70vh]">
    {{-- Conversation list --}}
    <div class="{{ $selectedMobile ? 'hidden xl:block' : '' }} xl:col-span-3 border-b xl:border-b-0 xl:border-r border-slate-200">
        <div class="divide-y divide-slate-100 max-h-[60vh] xl:max-h-none overflow-y-auto">
            @forelse($conversations as $conv)
                @php
                    $latest = $latestMessages[$conv->mobile] ?? null;
                    $normalized = preg_replace('/\D/', '', $conv->mobile);
                    $lead = $conversationLeads[$normalized] ?? $conversationLeads[$conv->mobile] ?? null;
                    $name = $lead?->name ?? $conv->mobile;
                @endphp
                <a href="{{ route('org.inbox.index', ['with' => $conv->mobile]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 {{ $selectedMobile === $conv->mobile ? 'bg-brand-50' : '' }}">
                    <x-ui.avatar :name="$name" />
                    <div class="min-w-0 flex-1">
                        <div class="flex justify-between gap-2">
                            <p class="truncate font-medium text-slate-900">{{ $name }}</p>
                            <span class="shrink-0 text-[10px] text-slate-400">{{ $latest?->created_at?->diffForHumans(null, true, true) }}</span>
                        </div>
                        <p class="truncate text-xs text-slate-500">{{ Str::limit($latest?->message ?? '', 50) }}</p>
                    </div>
                </a>
            @empty
                <div class="px-5 py-10 text-center text-sm text-slate-500">
                    <p>No messages yet.</p>
                    <a href="{{ route('org.crm.leads.index') }}" class="mt-2 inline-block text-brand-600 font-medium">Send from a lead →</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Thread --}}
    <div class="{{ $selectedMobile ? '' : 'hidden xl:flex' }} xl:col-span-5 flex flex-col min-h-[50vh] border-r border-slate-200">
        @if($selectedMobile)
            <div class="border-b border-slate-100 px-4 py-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <a href="{{ route('org.inbox.index') }}" class="xl:hidden text-slate-500">←</a>
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ $activeLead?->name ?? $selectedMobile }}</p>
                        <p class="text-xs text-slate-500">{{ $selectedMobile }}</p>
                    </div>
                </div>
                <div class="flex gap-1 shrink-0">
                    @if($activeLead)
                        <a href="{{ route('org.crm.leads.show', $activeLead) }}" class="cp-btn-secondary !py-1 text-xs">Lead</a>
                        <button type="button" data-whatsapp-open data-lead-name="{{ $activeLead->name }}" data-whatsapp-url="{{ route('org.crm.leads.whatsapp', $activeLead) }}" class="cp-btn-success !py-1 text-xs">Reply</button>
                    @endif
                    <a href="tel:{{ $selectedMobile }}" class="cp-btn-ghost !py-1 text-xs">Call</a>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50">
                @forelse($thread as $msg)
                    <div class="flex justify-end">
                        <div class="max-w-[85%] rounded-2xl rounded-br-md bg-brand-600 px-3 py-2 text-sm text-white">
                            <p>{{ $msg->message }}</p>
                            <p class="mt-1 text-[10px] text-brand-200 text-right">{{ $msg->created_at->format('M d, h:i A') }} · {{ ucfirst($msg->status) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-500 py-8">No messages in this thread.</p>
                @endforelse
            </div>
            @if($activeLead)
                <div class="border-t border-slate-100 p-3">
                    <div class="mb-2 flex flex-wrap gap-1">
                        @foreach(['Hi, following up on our conversation.', 'Are you available for a quick call?', 'Sharing the details you requested.'] as $reply)
                            <button type="button" data-quick-reply="{{ $reply }}" class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] text-slate-600 hover:bg-slate-50">{{ Str::limit($reply, 24) }}</button>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('org.crm.leads.whatsapp', $activeLead) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="message" required placeholder="Type a message..." class="cp-input flex-1" id="inbox-composer">
                        <button class="cp-btn-success shrink-0">Send</button>
                    </form>
                </div>
            @endif
        @else
            <div class="hidden xl:flex flex-1 items-center justify-center text-sm text-slate-500">
                Select a conversation
            </div>
        @endif
    </div>

    {{-- Lead info (desktop) --}}
    <div class="hidden xl:block xl:col-span-4 bg-slate-50/50">
        @if($activeLead)
            <div class="p-5">
                <h3 class="text-sm font-semibold text-slate-900">{{ $activeLead->name }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ $activeLead->phone }}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <x-ui.badge type="brand">{{ $activeLead->statusLabel() }}</x-ui.badge>
                    <x-ui.badge>{{ $activeLead->sourceLabel() }}</x-ui.badge>
                </div>
                @if($activeLead->estimated_value)
                    <p class="mt-3 text-lg font-bold text-slate-900">₹{{ number_format($activeLead->estimated_value, 0) }}</p>
                @endif
                @if($activeLead->interested_product)
                    <p class="mt-2 text-sm text-slate-600">{{ $activeLead->interested_product }}</p>
                @endif
                @if($activeLead->next_follow_up_at)
                    <p class="mt-3 text-xs text-slate-500">Next follow-up: {{ $activeLead->next_follow_up_at->format('M d, h:i A') }}</p>
                @endif
                @if($activeCampaign?->campaign)
                    <div class="mt-4 rounded-lg border border-brand-100 bg-brand-50/50 p-3">
                        <p class="text-xs font-medium text-brand-700">Campaign</p>
                        <a href="{{ route('org.campaigns.show', $activeCampaign->campaign) }}" class="text-sm font-medium text-brand-800 hover:underline">
                            {{ $activeCampaign->campaign->name }}
                        </a>
                    </div>
                @endif
                <div class="mt-5 flex flex-col gap-2">
                    <a href="{{ route('org.crm.leads.show', $activeLead) }}" class="cp-btn-primary w-full text-center">Open lead</a>
                    <a href="tel:{{ $activeLead->phone }}" class="cp-btn-secondary w-full text-center">Call</a>
                    <a href="{{ route('org.crm.leads.show', $activeLead) }}#follow-up" class="cp-btn-ghost w-full text-center">Schedule follow-up</a>
                </div>
            </div>
        @else
            <div class="flex h-full items-center justify-center p-5 text-sm text-slate-500">
                Lead details appear here
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('#inbox-composer').forEach((input) => {
    document.querySelectorAll('[data-quick-reply]').forEach((btn) => {
        btn.addEventListener('click', () => { input.value = btn.dataset.quickReply || ''; input.focus(); });
    });
});
</script>
@endpush
