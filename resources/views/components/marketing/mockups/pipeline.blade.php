{{-- Pipeline product mockup — horizontal scroll, nothing clipped --}}
<div class="relative w-full min-w-0">
    <x-marketing.browser-frame url="connectpulse.cloud/crm/pipeline" class="w-full">
        <div class="p-3 sm:p-5">
            <div class="mb-3 flex items-baseline justify-between gap-2 sm:mb-4">
                <p class="text-sm font-bold text-white">Sales Pipeline</p>
                <p class="shrink-0 text-lg font-bold text-[#8b7cff] sm:text-2xl">₹1.24L</p>
            </div>
            <div class="mkt-kanban-scroll flex gap-2 pb-1">
                @foreach([
                    ['New', [['Anita', '₹8K'], ['Rohit', '₹12K']], 'border-slate-500/30'],
                    ['Contacted', [['Kavita', '₹15K']], 'border-blue-500/30'],
                    ['Interested', [['Priya', '₹18K'], ['Suresh', '₹22K']], 'border-violet-500/30'],
                    ['Demo', [['Amit', '₹24K']], 'border-amber-500/30'],
                    ['Negotiation', [['Ravi', '₹25K']], 'border-orange-500/30'],
                    ['Won', [['Neha', '₹30K']], 'border-emerald-500/30'],
                ] as [$stage, $cards, $border])
                    <div class="mkt-kanban-col w-[100px] shrink-0 sm:w-[108px] {{ $border }}">
                        <p class="mb-2 truncate px-1 text-[9px] font-bold uppercase text-slate-500">{{ $stage }}</p>
                        @foreach($cards as [$name, $val])
                            <div class="mkt-kanban-card">
                                <p class="truncate font-semibold text-white">{{ $name }}</p>
                                <p class="text-[#8b7cff]">{{ $val }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </x-marketing.browser-frame>
</div>
