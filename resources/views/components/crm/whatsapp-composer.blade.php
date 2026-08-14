@props(['actionUrl', 'leadName' => null])

<div id="whatsapp-composer-sheet" data-bottom-sheet class="hidden fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-slate-900/50" data-sheet-close></div>
    <div class="absolute inset-x-0 bottom-0 rounded-t-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>
            <h3 class="text-base font-semibold text-slate-900">Send WhatsApp</h3>
            <p class="text-xs text-slate-500" id="whatsapp-composer-lead">{{ $leadName ?? 'Lead' }}</p>
        </div>
        <form id="whatsapp-composer-form" method="POST" action="{{ $actionUrl ?? '#' }}" class="space-y-3 px-5 py-4 pb-8">
            @csrf
            <textarea name="message" id="whatsapp-composer-message" rows="4" required class="cp-input" placeholder="Type your message..."></textarea>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-quick-reply="Thanks for your enquiry. I'll get back to you shortly." class="cp-btn-ghost !py-1 text-[11px]">Thanks for enquiry</button>
                <button type="button" data-quick-reply="Just following up regarding the software we discussed." class="cp-btn-ghost !py-1 text-[11px]">Following up</button>
                <button type="button" data-quick-reply="Would you like me to arrange a quick demo?" class="cp-btn-ghost !py-1 text-[11px]">Offer demo</button>
            </div>
            <button type="submit" class="cp-btn-success w-full">Send via ConnectPulse</button>
        </form>
    </div>
</div>
