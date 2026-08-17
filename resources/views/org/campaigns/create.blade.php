@extends('layouts.org')

@section('title', 'New Campaign')

@section('page-title', 'New Campaign')

@section('content')
<form method="POST" action="{{ route('org.campaigns.store') }}" enctype="multipart/form-data" id="campaign-form" class="max-w-3xl mx-auto space-y-6">
    @csrf

    {{-- Step 1: Name --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">1. Campaign name</h2>
        </div>
        <div class="cp-card-body">
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Diwali Collection" class="cp-input">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- Step 2: Audience --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">2. Audience</h2>
        </div>
        <div class="cp-card-body space-y-4">
            <select name="audience_type" id="audience-type" class="cp-select" required>
                @foreach(\App\Models\Campaign::audienceTypes() as $key => $label)
                    <option value="{{ $key }}" @selected(old('audience_type') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <div id="audience-list" class="hidden space-y-2">
                <label class="text-xs font-medium text-slate-600">Contact list</label>
                <select name="contact_list_id" class="cp-select">
                    <option value="">Select list</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->contacts()->count() }})</option>
                    @endforeach
                </select>
            </div>

            <div id="audience-tags" class="hidden space-y-2">
                <label class="text-xs font-medium text-slate-600">Tags</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="rounded border-slate-300">
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="audience-manual" class="hidden space-y-3">
                <div>
                    <label class="text-xs font-medium text-slate-600">Contacts</label>
                    <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-slate-200 p-2 space-y-1">
                        @forelse($contacts as $contact)
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" class="rounded border-slate-300">
                                <span>{{ $contact->name ?: 'Unknown' }} · {{ $contact->phone }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-slate-500">No contacts yet. <a href="{{ route('org.contacts.index') }}" class="text-brand-600">Add contacts</a></p>
                        @endforelse
                    </div>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600">Leads</label>
                    <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-slate-200 p-2 space-y-1">
                        @foreach($leads as $lead)
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="rounded border-slate-300">
                                <span>{{ $lead->name }} · {{ $lead->phone }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="audience-csv" class="hidden space-y-2">
                <label class="text-xs font-medium text-slate-600">Paste phone numbers (one per line, optional name,phone)</label>
                <textarea name="csv_phones" rows="5" class="cp-input font-mono text-xs" placeholder="Ravi Kumar,919876543210&#10;919876543211"></textarea>
            </div>
        </div>
    </section>

    {{-- Step 3: Message --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">3. Message</h2>
        </div>
        <div class="cp-card-body space-y-2">
            <textarea name="message_body" id="message-body" rows="5" required class="cp-input" placeholder="Hello @{{name}}, ...">{{ old('message_body') }}</textarea>
            <p class="text-xs text-slate-500">Use <code class="rounded bg-slate-100 px-1">@{{name}}</code> for personalization.</p>
        </div>
    </section>

    {{-- Step 4: Media --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">4. Media (optional)</h2>
        </div>
        <div class="cp-card-body">
            <input type="file" name="media" accept="image/jpeg,image/png,image/gif,image/webp" class="cp-input" id="media-input">
            <p class="mt-1 text-xs text-slate-500">Image with caption. JPG, PNG, GIF, WebP up to 5MB.</p>
        </div>
    </section>

    {{-- Step 5: Preview --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">5. Preview</h2>
        </div>
        <div class="cp-card-body">
            <div class="mx-auto max-w-xs rounded-2xl bg-[#0b141a] p-4 shadow-lg">
                <div class="mb-3 flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-emerald-600"></div>
                    <span class="text-xs text-slate-300">Your Business</span>
                </div>
                <div class="rounded-lg bg-[#005c4b] p-3 text-sm text-white whitespace-pre-wrap" id="preview-message">Hello there,</div>
                <img id="preview-image" src="" alt="" class="mt-2 hidden max-w-full rounded-lg">
            </div>
        </div>
    </section>

    {{-- Step 6: Sending settings --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">6. Sending settings</h2>
        </div>
        <div class="cp-card-body space-y-4">
            <div>
                <p class="text-xs font-medium text-slate-600 mb-2">Sending mode</p>
                <div class="flex flex-col gap-2 sm:flex-row sm:gap-4">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="send_mode" value="now" checked class="text-brand-600">
                        Send now
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="send_mode" value="schedule" class="text-brand-600">
                        Schedule
                    </label>
                </div>
            </div>
            <div id="schedule-field" class="hidden">
                <label class="text-xs font-medium text-slate-600">Schedule for</label>
                <input type="datetime-local" name="scheduled_at" class="cp-input mt-1">
            </div>
            <div>
                <p class="text-xs font-medium text-slate-600 mb-2">Sending pace — Controlled</p>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span>Delay between messages:</span>
                    <input type="number" name="delay_min_seconds" value="{{ old('delay_min_seconds', $defaults['delay_min']) }}" min="5" max="300" class="cp-input w-20 text-center">
                    <span>to</span>
                    <input type="number" name="delay_max_seconds" value="{{ old('delay_max_seconds', $defaults['delay_max']) }}" min="5" max="300" class="cp-input w-20 text-center">
                    <span>seconds</span>
                </div>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                Messages are queued and sent individually. Sending speed does not guarantee delivery or prevent platform restrictions. Only message contacts you are permitted to contact. You are responsible for consent and WhatsApp policy compliance.
            </div>
        </div>
    </section>

    {{-- Step 7: Test --}}
    <section class="cp-card">
        <div class="cp-card-header">
            <h2 class="text-sm font-semibold text-slate-900">7. Send test</h2>
        </div>
        <div class="cp-card-body space-y-3">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" name="test_phone" placeholder="Test phone number" class="cp-input flex-1">
                <p class="text-xs text-slate-500 sm:self-center">A test message will be sent when you create the campaign.</p>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="confirm_test" value="1" class="rounded border-slate-300">
                I confirm the test message was received correctly (skip test send)
            </label>
        </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end pb-8">
        <a href="{{ route('org.campaigns.index') }}" class="cp-btn-secondary">Cancel</a>
        <button type="submit" name="launch" value="0" class="cp-btn-secondary">Save as draft</button>
        <button type="submit" name="launch" value="1" class="cp-btn-primary">Create & Launch</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const audienceType = document.getElementById('audience-type');
    const panels = {
        contact_list: document.getElementById('audience-list'),
        tags: document.getElementById('audience-tags'),
        manual: document.getElementById('audience-manual'),
        csv: document.getElementById('audience-csv'),
    };

    function updateAudience() {
        Object.values(panels).forEach(p => p?.classList.add('hidden'));
        const val = audienceType.value;
        if (panels[val]) panels[val].classList.remove('hidden');
    }
    audienceType.addEventListener('change', updateAudience);
    updateAudience();

    const messageBody = document.getElementById('message-body');
    const previewMessage = document.getElementById('preview-message');
    messageBody?.addEventListener('input', () => {
        previewMessage.textContent = messageBody.value.replace(/\{\{name\}\}/gi, 'Priya');
    });

    const mediaInput = document.getElementById('media-input');
    const previewImage = document.getElementById('preview-image');
    mediaInput?.addEventListener('change', () => {
        const file = mediaInput.files?.[0];
        if (file) {
            previewImage.src = URL.createObjectURL(file);
            previewImage.classList.remove('hidden');
        }
    });

    document.querySelectorAll('[name=send_mode]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('schedule-field').classList.toggle('hidden', document.querySelector('[name=send_mode]:checked')?.value !== 'schedule');
        });
    });
});
</script>
@endsection
