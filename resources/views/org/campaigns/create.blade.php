@extends('layouts.org')

@section('title', 'Create WhatsApp Campaign')

@section('page-title', 'Create WhatsApp Campaign')
@section('page-subtitle', 'Send a WhatsApp message to multiple customers')

@php
    $audienceLabels = [
        'all_contacts' => 'All customers',
        'contact_list' => 'Customer list',
        'tags' => 'By tag',
        'leads' => 'All leads',
        'manual' => 'Pick customers',
        'csv' => 'Paste phone numbers',
    ];
    $whatsappConnected = $shellWhatsAppConnected ?? false;
@endphp

@section('content')
<div class="wa-composer-page pb-28 lg:pb-24">
    {{-- Hero header --}}
    <div class="wa-composer-hero">
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('org.campaigns.index') }}" class="mb-3 inline-flex items-center gap-1.5 text-sm text-indigo-200/80 transition-colors hover:text-white lg:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                WhatsApp Campaigns
            </a>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-widest text-indigo-300/60">WhatsApp Campaigns</p>
                    <h1 class="mt-1 text-xl font-bold text-white sm:text-2xl">Create WhatsApp Campaign</h1>
                    <p class="mt-1 text-sm text-indigo-200/70">Compose a message and send it to your customers on WhatsApp</p>
                </div>
                <div class="wa-composer-progress hidden sm:flex">
                    <span class="wa-composer-progress-step wa-composer-progress-step-active">Campaign</span>
                    <span class="wa-composer-progress-arrow">→</span>
                    <span class="wa-composer-progress-step">Audience</span>
                    <span class="wa-composer-progress-arrow">→</span>
                    <span class="wa-composer-progress-step">Message</span>
                    <span class="wa-composer-progress-arrow">→</span>
                    <span class="wa-composer-progress-step">Send</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        {{-- WhatsApp not connected warning --}}
        <div id="whatsapp-warning" @class(['wa-composer-error mb-5', 'hidden' => $whatsappConnected])>
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-medium">WhatsApp is not connected</p>
                <p class="mt-0.5 text-xs text-red-700/80">Connect WhatsApp before sending campaigns.</p>
                <a href="{{ route('org.whatsapp.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">Connect WhatsApp →</a>
            </div>
        </div>

        {{-- Validation banner --}}
        <div id="validation-banner" class="wa-composer-error mb-5 hidden">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p id="validation-text"></p>
        </div>

        <form method="POST" action="{{ route('org.campaigns.store') }}" enctype="multipart/form-data" id="campaign-form">
            @csrf

            <div class="wa-composer-grid">
                {{-- LEFT: Composer + Preview --}}
                <div class="space-y-5">
                    {{-- Campaign name --}}
                    <div class="wa-composer-panel p-5">
                        <label for="campaign-name" class="wa-composer-label">Campaign name</label>
                        <input type="text" name="name" id="campaign-name" value="{{ old('name') }}" required placeholder="Diwali Saree Collection" class="wa-composer-input mt-2">
                        <p class="mt-1.5 text-xs text-slate-500">Give this campaign a name so you can find it later.</p>
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Message composer --}}
                    <div class="wa-composer-panel p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <label for="message-body" class="wa-composer-label">Write your message</label>
                            <button type="button" id="insert-name-btn" class="wa-composer-btn-insert">+ Name</button>
                        </div>
                        <textarea name="message_body" id="message-body" required class="wa-composer-textarea" placeholder="Hi @{{name}} 👋&#10;&#10;Our new festive collection has arrived.&#10;&#10;Reply to know more.">{{ old('message_body') }}</textarea>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                            <span>Use <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px]">@{{name}}</code> to personalize with customer name</span>
                            <span id="char-count">0 / 4096</span>
                        </div>
                        @error('message_body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Media upload --}}
                    <div class="wa-composer-panel p-5">
                        <p class="wa-composer-label mb-3">Add photo</p>
                        <input type="file" name="media" accept="image/jpeg,image/png,image/gif,image/webp" class="sr-only" id="media-input">

                        <div id="media-upload-area" class="wa-composer-upload" role="button" tabindex="0" aria-label="Add photo">
                            <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm font-medium text-slate-700">Add photo</p>
                            <p class="mt-1 text-xs text-slate-500">JPG, PNG, WebP up to 5MB</p>
                        </div>

                        <div id="media-preview-wrap" class="hidden">
                            <div class="relative overflow-hidden rounded-xl border border-slate-200">
                                <img id="media-preview-img" src="" alt="Uploaded image" class="max-h-48 w-full object-cover">
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="button" id="media-replace-btn" class="cp-btn-secondary !text-xs !py-1.5">Replace</button>
                                <button type="button" id="media-remove-btn" class="cp-btn-ghost !text-xs !py-1.5 text-red-600">Remove</button>
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp Preview --}}
                    <div class="wa-composer-panel-dark p-5 lg:p-6">
                        <p class="wa-composer-label-light mb-4">WhatsApp preview</p>
                        <div class="wa-phone">
                            <div class="wa-phone-header">
                                <div class="wa-phone-avatar" id="preview-avatar">{{ strtoupper(substr($businessName, 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-white" id="preview-business">{{ $businessName }}</p>
                                    <p class="text-[11px] text-emerald-400/80">Business account</p>
                                </div>
                            </div>
                            <div class="wa-phone-body">
                                <div class="flex justify-end">
                                    <div>
                                        <div class="wa-phone-bubble">
                                            <div id="preview-image-wrap" class="hidden -mx-1 -mt-1 mb-1.5">
                                                <img id="preview-image" src="" alt="" class="wa-phone-bubble-image w-full">
                                            </div>
                                            <span id="preview-message">Write your message…</span>
                                        </div>
                                        <div class="wa-phone-meta">
                                            <span id="preview-time">{{ now()->format('g:i') }}</span>
                                            <svg class="h-3.5 w-3.5 text-sky-300" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 00-.51.063L8.28 9.885 5.03 6.636a.364.364 0 00-.516.005l-.423.433a.364.364 0 00.006.514l3.255 3.185a.364.364 0 00.514-.005l6.756-7.008a.365.365 0 00-.063-.51z"/><path d="M10.01 3.316l-.478-.372a.365.365 0 00-.51.063L3.28 9.885.03 6.636a.364.364 0 00-.516.005l-.423.433a.364.364 0 00.006.514l3.255 3.185a.364.364 0 00.514-.005l6.756-7.008a.365.365 0 00-.063-.51z"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Configuration --}}
                <div class="space-y-5">
                    {{-- Audience --}}
                    <div class="wa-composer-panel p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="wa-composer-section-title">Audience</p>
                            <span class="text-lg font-bold text-brand-600"><span data-recipient-count>0</span></span>
                        </div>

                        {{-- Desktop audience selector --}}
                        <div class="audience-desktop-only hidden lg:block space-y-3">
                            <select name="audience_type" id="audience-type" class="wa-composer-input" required>
                                @foreach(\App\Models\Campaign::audienceTypes() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('audience_type', 'all_contacts') === $key)>{{ $audienceLabels[$key] ?? $label }}</option>
                                @endforeach
                            </select>

                            <div id="audience-list" class="hidden space-y-2">
                                <label class="text-xs font-medium text-slate-600">Choose list</label>
                                <select name="contact_list_id" class="wa-composer-input">
                                    <option value="">Select list</option>
                                    @foreach($lists as $list)
                                        <option value="{{ $list->id }}" @selected(old('contact_list_id') == $list->id)>{{ $list->name }} ({{ $list->contacts_count }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="audience-tags" class="hidden space-y-2">
                                <label class="text-xs font-medium text-slate-600">Choose tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <label class="wa-composer-chip wa-composer-chip-selectable has-[:checked]:wa-composer-chip-active">
                                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="sr-only" @checked(in_array($tag->id, (array) old('tag_ids', [])))>
                                            {{ $tag->name }}
                                            <span class="text-slate-400">({{ $tag->contacts_count }})</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div id="audience-manual" class="hidden space-y-3">
                                <div>
                                    <label class="text-xs font-medium text-slate-600">Customers</label>
                                    <div class="mt-1 max-h-36 overflow-y-auto rounded-xl border border-slate-200 p-2 space-y-1 scrollbar-thin">
                                        @forelse($contacts as $contact)
                                            <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-slate-50">
                                                <input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" class="rounded border-slate-300 text-brand-600">
                                                <span>{{ $contact->name ?: 'Unknown' }} · {{ $contact->phone }}</span>
                                            </label>
                                        @empty
                                            <p class="px-2 py-2 text-xs text-slate-500">No customers yet. <a href="{{ route('org.contacts.index') }}" class="text-brand-600">Add customers</a></p>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-slate-600">Leads</label>
                                    <div class="mt-1 max-h-36 overflow-y-auto rounded-xl border border-slate-200 p-2 space-y-1 scrollbar-thin">
                                        @forelse($leads as $lead)
                                            <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-slate-50">
                                                <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="rounded border-slate-300 text-brand-600">
                                                <span>{{ $lead->name }} · {{ $lead->phone }}</span>
                                            </label>
                                        @empty
                                            <p class="px-2 py-2 text-xs text-slate-500">No leads with phone numbers.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div id="audience-csv" class="hidden space-y-2">
                                <label class="text-xs font-medium text-slate-600">Paste phone numbers (one per line)</label>
                                <textarea name="csv_phones" rows="4" class="wa-composer-input font-mono text-xs" placeholder="Ravi Kumar,919876543210&#10;919876543211">{{ old('csv_phones') }}</textarea>
                            </div>
                        </div>

                        {{-- Mobile audience trigger --}}
                        <div class="lg:hidden audience-mobile-only">
                            <button type="button" data-sheet-open="audience-sheet" class="wa-composer-input flex items-center justify-between text-left">
                                <span id="audience-chip-display">All customers</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        <p id="audience-summary" class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                            <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Choose who should receive this message
                        </p>
                    </div>

                    {{-- Message status --}}
                    <div class="wa-composer-panel p-5">
                        <p class="wa-composer-section-title mb-3">Message status</p>
                        <div class="space-y-2">
                            <div id="status-message" class="wa-composer-status-item wa-composer-status-warn">Write your message</div>
                            <div id="status-media" class="wa-composer-status-item"><span class="text-slate-400">No image</span></div>
                            <div id="status-recipients" class="wa-composer-status-item wa-composer-status-warn">Choose audience</div>
                        </div>
                    </div>

                    {{-- Sending --}}
                    <div class="wa-composer-panel p-5">
                        <p class="wa-composer-section-title mb-3">Send</p>
                        <div class="space-y-2">
                            <label class="wa-composer-radio">
                                <input type="radio" name="send_mode" value="now" checked>
                                Send now
                            </label>
                            <label class="wa-composer-radio">
                                <input type="radio" name="send_mode" value="schedule">
                                Schedule for later
                            </label>
                        </div>

                        <div id="schedule-field" class="mt-3 hidden space-y-2">
                            <label class="text-xs font-medium text-slate-600">Date & time</label>
                            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="wa-composer-input">
                        </div>

                        {{-- Mobile: open sending settings sheet --}}
                        <button type="button" data-sheet-open="sending-sheet" class="mt-3 w-full cp-btn-secondary !text-xs lg:hidden">Controlled sending settings</button>

                        <div class="mt-4 sending-desktop-only hidden lg:block space-y-2">
                            <p class="text-xs font-medium text-slate-600">Controlled sending</p>
                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                                <input type="number" name="delay_min_seconds" value="{{ old('delay_min_seconds', $defaults['delay_min']) }}" min="5" max="300" class="wa-composer-input !w-16 text-center">
                                <span class="text-slate-500">–</span>
                                <input type="number" name="delay_max_seconds" value="{{ old('delay_max_seconds', $defaults['delay_max']) }}" min="5" max="300" class="wa-composer-input !w-16 text-center">
                                <span class="text-slate-500">sec between messages</span>
                            </div>
                            <p class="text-xs text-slate-500">Messages are queued and sent individually.</p>
                        </div>

                        <div class="wa-composer-alert mt-4">
                            Only message customers you have permission to contact. You are responsible for consent and WhatsApp policy compliance. Sending speed does not guarantee delivery.
                        </div>
                    </div>

                    {{-- Test message --}}
                    <div class="wa-composer-panel p-5 ring-2 ring-brand-500/20">
                        <p class="wa-composer-section-title mb-1">Test before sending</p>
                        <p class="mb-3 text-xs text-slate-500">Send this campaign to yourself first.</p>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="text" name="test_phone" value="{{ old('test_phone') }}" placeholder="+91 98765 43210" class="wa-composer-input flex-1">
                            <p class="self-center text-[11px] text-slate-400 sm:hidden">Sent when you launch</p>
                        </div>
                        <label class="mt-3 flex items-start gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="confirm_test" value="1" class="mt-0.5 rounded border-slate-300 text-brand-600" @checked(old('confirm_test'))>
                            Skip test — I've already verified my message
                        </label>
                    </div>

                    {{-- Desktop launch buttons --}}
                    <div class="hidden lg:block space-y-3">
                        <button type="submit" name="launch" value="0" class="cp-btn-secondary w-full">Save Draft</button>
                        <button type="button" id="launch-campaign-btn" data-launch-trigger class="wa-composer-btn-launch w-full">
                            Launch Campaign
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Sticky action bar --}}
<div class="wa-composer-sticky-bar">
    <div class="wa-composer-sticky-inner">
        <div class="min-w-0 flex-1">
            <p class="text-xs text-slate-500"><span data-recipient-count>0</span> recipients</p>
            <p class="text-sm font-semibold text-slate-900">Est. credits: <span data-credit-estimate>0</span></p>
        </div>
        <button type="submit" form="campaign-form" name="launch" value="0" class="cp-btn-secondary hidden sm:inline-flex">Save Draft</button>
        <button type="button" id="sticky-launch-btn" data-launch-trigger class="wa-composer-btn-launch shrink-0">
            Launch Campaign
        </button>
    </div>
</div>

{{-- Mobile: Audience bottom sheet --}}
<div id="audience-sheet" data-bottom-sheet class="audience-mobile-only hidden fixed inset-0 z-[60] lg:hidden">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
        <div class="sticky top-0 border-b border-slate-100 bg-white px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">Choose customers</h3>
        </div>
        <div class="space-y-4 px-5 py-4 pb-8">
            <select id="audience-type-mobile" class="wa-composer-input">
                @foreach(\App\Models\Campaign::audienceTypes() as $key => $label)
                    <option value="{{ $key }}" @selected(old('audience_type', 'all_contacts') === $key)>{{ $audienceLabels[$key] ?? $label }}</option>
                @endforeach
            </select>

            <div id="audience-list-mobile" class="hidden space-y-2">
                <label class="text-xs font-medium text-slate-600">Choose list</label>
                <select id="contact-list-mobile" class="wa-composer-input">
                    <option value="">Select list</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->contacts_count }})</option>
                    @endforeach
                </select>
            </div>

            <div id="audience-tags-mobile" class="hidden space-y-2">
                <label class="text-xs font-medium text-slate-600">Choose tags</label>
                <div class="flex flex-wrap gap-2" id="tags-mobile-wrap">
                    @foreach($tags as $tag)
                        <label class="wa-composer-chip wa-composer-chip-selectable has-[:checked]:wa-composer-chip-active">
                            <input type="checkbox" value="{{ $tag->id }}" class="sr-only tag-mobile-cb">
                            {{ $tag->name }} ({{ $tag->contacts_count }})
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="audience-manual-mobile" class="hidden space-y-3">
                <div class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 p-2 space-y-1" id="contacts-mobile-wrap">
                    @foreach($contacts as $contact)
                        <label class="flex items-center gap-2 text-xs px-2 py-1">
                            <input type="checkbox" value="{{ $contact->id }}" class="rounded border-slate-300 contact-mobile-cb">
                            {{ $contact->name ?: 'Unknown' }} · {{ $contact->phone }}
                        </label>
                    @endforeach
                </div>
                <div class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 p-2 space-y-1" id="leads-mobile-wrap">
                    @foreach($leads as $lead)
                        <label class="flex items-center gap-2 text-xs px-2 py-1">
                            <input type="checkbox" value="{{ $lead->id }}" class="rounded border-slate-300 lead-mobile-cb">
                            {{ $lead->name }} · {{ $lead->phone }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="audience-csv-mobile" class="hidden space-y-2">
                <textarea id="csv-mobile" rows="4" class="wa-composer-input font-mono text-xs" placeholder="Ravi Kumar,919876543210"></textarea>
            </div>

            <button type="button" data-sheet-close class="cp-btn-primary w-full">Done</button>
        </div>
    </div>
</div>

{{-- Mobile: Sending settings bottom sheet --}}
<div id="sending-sheet" data-bottom-sheet class="sending-mobile-only hidden fixed inset-0 z-[60] lg:hidden">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 rounded-t-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">Controlled sending</h3>
        </div>
        <div class="space-y-4 px-5 py-4 pb-8">
            <p class="text-sm text-slate-600">Delay between messages</p>
            <div class="flex items-center gap-2">
                <input type="number" id="delay-min-mobile" value="{{ old('delay_min_seconds', $defaults['delay_min']) }}" min="5" max="300" class="wa-composer-input w-20 text-center">
                <span>–</span>
                <input type="number" id="delay-max-mobile" value="{{ old('delay_max_seconds', $defaults['delay_max']) }}" min="5" max="300" class="wa-composer-input w-20 text-center">
                <span class="text-sm text-slate-500">seconds</span>
            </div>
            <p class="text-xs text-slate-500">Messages are queued and sent individually.</p>
            <button type="button" data-sheet-close class="cp-btn-primary w-full">Done</button>
        </div>
    </div>
</div>

{{-- Launch confirmation sheet --}}
<div id="launch-confirm-sheet" data-bottom-sheet class="hidden fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-slate-900/60" data-confirm-cancel></div>
    <div class="absolute inset-x-0 bottom-0 lg:inset-auto lg:top-1/2 lg:left-1/2 lg:-translate-x-1/2 lg:-translate-y-1/2 lg:max-w-md lg:w-full lg:rounded-2xl rounded-t-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5 lg:rounded-t-2xl" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-white/30 lg:hidden"></div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-200">Ready to send</p>
            <h3 class="mt-1 text-lg font-bold text-white">Launch WhatsApp Campaign</h3>
        </div>
        <div class="space-y-4 px-6 py-5">
            <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <p class="text-xs text-slate-500">Campaign</p>
                    <p class="font-medium text-slate-900" id="confirm-campaign-name">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Audience</p>
                    <p class="font-medium text-slate-900" id="confirm-audience-count">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Media</p>
                    <p class="font-medium text-slate-900" id="confirm-media-status">—</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Sending</p>
                    <p class="font-medium text-slate-900" id="confirm-sending-mode">—</p>
                </div>
                <div class="col-span-2 rounded-xl bg-brand-50 px-4 py-3">
                    <p class="text-xs text-brand-600">Estimated credits</p>
                    <p class="text-xl font-bold text-brand-700" id="confirm-credits">0</p>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" data-confirm-cancel class="cp-btn-secondary flex-1">Cancel</button>
                <button type="button" id="confirm-launch-btn" class="wa-composer-btn-launch flex-1">Launch Campaign</button>
            </div>
        </div>
    </div>
</div>

{{-- Meta for JS --}}
<script type="application/json" id="campaign-audience-meta">@json($audienceMeta)</script>
<span id="campaign-business-name" class="hidden">{{ $businessName }}</span>
<span id="campaign-whatsapp-connected" class="hidden">{{ $whatsappConnected ? '1' : '0' }}</span>
@endsection
