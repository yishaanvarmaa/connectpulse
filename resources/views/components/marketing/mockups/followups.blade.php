<x-marketing.browser-frame :light="true" url="connectpulse.cloud/crm/follow-ups" class="w-full">
    <div class="p-4 sm:p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-900">Follow-ups</p>
            <div class="flex gap-2">
                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-[10px] font-bold text-red-700">3 overdue</span>
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-700">5 today</span>
            </div>
        </div>
        @foreach([
            ['Ravi Kumar', '10:30 AM', 'Call', '₹24,999', 'border-red-200 bg-red-50'],
            ['Priya Sharma', '2:00 PM', 'Demo', '₹18,500', 'border-amber-200 bg-amber-50/80'],
            ['Amit Patel', 'Tomorrow', 'WhatsApp', '₹12,000', 'border-slate-200 bg-white'],
        ] as [$name, $time, $type, $val, $cls])
            <div class="mb-2 flex items-center gap-3 rounded-xl border {{ $cls }} px-3 py-3">
                <span class="w-16 shrink-0 text-[10px] font-semibold text-slate-500">{{ $time }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-900">{{ $name }}</p>
                    <p class="text-[10px] text-slate-500">{{ $type }}</p>
                </div>
                <span class="text-xs font-bold text-slate-800">{{ $val }}</span>
                <div class="hidden gap-1 sm:flex">
                    <span class="rounded-md bg-emerald-600 px-2 py-1 text-[9px] font-medium text-white">WA</span>
                    <span class="rounded-md bg-[#635bff] px-2 py-1 text-[9px] font-medium text-white">Done</span>
                </div>
            </div>
        @endforeach
    </div>
</x-marketing.browser-frame>
