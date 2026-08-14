<x-marketing.browser-frame>
    <div class="p-4">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-900">Follow-ups</p>
            <span class="text-[10px] text-red-600 font-medium">3 overdue</span>
        </div>
        <div class="space-y-2">
            @foreach([
                ['Priya Sharma', '10:30 AM', 'Demo', 'overdue'],
                ['Amit Patel', '2:00 PM', 'Call', 'today'],
                ['Neha Singh', 'Tomorrow', 'WhatsApp', 'upcoming'],
            ] as [$name, $time, $type, $status])
                <div class="flex items-center gap-2 rounded-lg border {{ $status === 'overdue' ? 'border-red-200 bg-red-50/50' : ($status === 'today' ? 'border-amber-200 bg-amber-50/30' : 'border-slate-200 bg-white') }} px-3 py-2">
                    <span class="w-14 shrink-0 text-[10px] font-medium text-slate-500">{{ $time }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-slate-900">{{ $name }}</p>
                        <p class="text-[10px] text-slate-500">{{ $type }}</p>
                    </div>
                    <span class="rounded-md bg-brand-600 px-2 py-0.5 text-[9px] font-medium text-white">Done</span>
                </div>
            @endforeach
        </div>
    </div>
</x-marketing.browser-frame>
