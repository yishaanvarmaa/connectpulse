<x-marketing.browser-frame>
    <div class="p-4">
        <div class="mb-3 flex items-baseline justify-between">
            <p class="text-xs font-semibold text-slate-900">Pipeline</p>
            <p class="text-sm font-bold text-slate-900">₹1.24L</p>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach([
                ['New', 4, 'bg-slate-500', 'bg-slate-200'],
                ['Contacted', 6, 'bg-blue-500', 'bg-blue-200'],
                ['Interested', 3, 'bg-violet-500', 'bg-violet-200'],
                ['Demo', 2, 'bg-amber-500', 'bg-amber-200'],
                ['Negotiation', 2, 'bg-orange-500', 'bg-orange-200'],
                ['Won', 5, 'bg-emerald-500', 'bg-emerald-200'],
            ] as [$stage, $count, $bar, $track])
                <div class="min-w-[72px] shrink-0 rounded-xl border border-slate-200 bg-white p-2">
                    <p class="text-[9px] font-medium text-slate-500">{{ $stage }}</p>
                    <p class="text-lg font-bold text-slate-900">{{ $count }}</p>
                    <div class="mt-1 h-1 rounded-full {{ $track }}">
                        <div class="h-1 rounded-full {{ $bar }}" style="width: {{ min(100, $count * 15) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-marketing.browser-frame>
