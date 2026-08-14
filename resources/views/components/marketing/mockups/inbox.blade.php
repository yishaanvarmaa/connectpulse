<x-marketing.browser-frame>
    <div class="flex min-h-[240px]">
        <div class="w-1/3 border-r border-slate-200 bg-white p-2">
            @foreach(['Priya Sharma', 'Amit Patel', 'Neha Singh'] as $i => $name)
                <div class="mb-1 rounded-lg px-2 py-2 {{ $i === 0 ? 'bg-brand-50' : '' }}">
                    <p class="text-[10px] font-medium text-slate-900">{{ $name }}</p>
                    <p class="truncate text-[9px] text-slate-500">Thanks, I'll confirm...</p>
                </div>
            @endforeach
        </div>
        <div class="flex flex-1 flex-col bg-slate-50">
            <div class="border-b border-slate-200 bg-white px-3 py-2">
                <p class="text-xs font-semibold text-slate-900">Priya Sharma</p>
            </div>
            <div class="flex-1 space-y-2 p-3">
                <div class="ml-auto max-w-[80%] rounded-2xl rounded-br-md bg-brand-600 px-3 py-2 text-[10px] text-white">
                    Hi Priya, following up on your enquiry for the health package.
                </div>
                <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-3 py-2 text-[10px] text-slate-700 shadow-sm">
                    Yes, can we schedule a demo tomorrow?
                </div>
            </div>
            <div class="border-t border-slate-200 bg-white p-2">
                <div class="rounded-lg border border-slate-200 px-2 py-1.5 text-[10px] text-slate-400">Type a message...</div>
            </div>
        </div>
    </div>
</x-marketing.browser-frame>
