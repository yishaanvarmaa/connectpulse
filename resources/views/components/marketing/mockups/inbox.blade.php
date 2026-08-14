<x-marketing.browser-frame :light="true" url="connectpulse.cloud/inbox" class="w-full">
    <div class="flex min-h-[280px] sm:min-h-[320px]">
        <div class="w-[35%] shrink-0 border-r border-slate-200 bg-white p-2">
            @foreach([['Priya Sharma', 'Thanks, can we schedule...', true], ['Amit Patel', 'Report received', false], ['Neha Singh', 'Interested in demo', false]] as [$name, $preview, $active])
                <div class="mb-1 rounded-lg px-2 py-2.5 {{ $active ? 'bg-[#635bff]/10 ring-1 ring-[#635bff]/20' : '' }}">
                    <div class="flex justify-between">
                        <p class="text-[11px] font-semibold text-slate-900">{{ $name }}</p>
                        @if($active)<span class="h-2 w-2 rounded-full bg-[#635bff]"></span>@endif
                    </div>
                    <p class="truncate text-[9px] text-slate-500">{{ $preview }}</p>
                </div>
            @endforeach
        </div>
        <div class="flex flex-1 flex-col bg-slate-50">
            <div class="border-b border-slate-200 bg-white px-3 py-2.5">
                <p class="text-xs font-bold text-slate-900">Priya Sharma</p>
                <p class="text-[10px] text-slate-500">+91 98765 43210 · Lead · Interested</p>
            </div>
            <div class="flex-1 space-y-2 p-3">
                <div class="ml-auto max-w-[85%] rounded-2xl rounded-br-sm bg-[#635bff] px-3 py-2 text-[11px] text-white">
                    Hi Priya, following up on your health package enquiry.
                </div>
                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-white px-3 py-2 text-[11px] text-slate-700 shadow-sm">
                    Yes! Can we schedule a demo tomorrow?
                </div>
            </div>
            <div class="border-t border-slate-200 bg-white p-2">
                <div class="flex gap-2">
                    <div class="flex-1 rounded-lg border border-slate-200 px-2 py-2 text-[10px] text-slate-400">Type a message...</div>
                    <span class="rounded-lg bg-emerald-600 px-3 py-2 text-[10px] font-semibold text-white">Send</span>
                </div>
            </div>
        </div>
        <div class="hidden w-[28%] shrink-0 border-l border-slate-200 bg-white p-3 xl:block">
            <p class="text-[10px] font-bold uppercase text-slate-400">Lead info</p>
            <p class="mt-2 text-sm font-bold text-slate-900">Priya Sharma</p>
            <p class="mt-1 text-lg font-bold text-slate-900">₹18,500</p>
            <p class="mt-2 text-[10px] text-slate-500">Source: Instagram</p>
            <p class="text-[10px] text-slate-500">Next: Demo tomorrow</p>
        </div>
    </div>
</x-marketing.browser-frame>
