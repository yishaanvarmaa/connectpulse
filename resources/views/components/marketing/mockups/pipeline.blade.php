<div class="relative">
    <x-marketing.browser-frame url="connectpulse.cloud/crm/pipeline" class="w-full">
        <div class="p-4 sm:p-5">
            <div class="mb-4 flex items-baseline justify-between">
                <p class="text-sm font-bold text-white">Sales Pipeline</p>
                <p class="text-2xl font-bold text-[#8b7cff]">₹1.24L</p>
            </div>
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach([
                    ['New', [['Anita', '₹8K'], ['Rohit', '₹12K']], 'border-slate-500/30'],
                    ['Contacted', [['Kavita', '₹15K']], 'border-blue-500/30'],
                    ['Interested', [['Priya', '₹18K'], ['Suresh', '₹22K']], 'border-violet-500/30'],
                    ['Demo', [['Amit', '₹24K']], 'border-amber-500/30'],
                    ['Negotiation', [['Ravi', '₹25K']], 'border-orange-500/30'],
                    ['Won', [['Neha', '₹30K']], 'border-emerald-500/30'],
                ] as [$stage, $cards, $border])
                    <div class="mkt-kanban-col min-w-[100px] {{ $border }}">
                        <p class="mb-2 px-1 text-[9px] font-bold uppercase text-slate-500">{{ $stage }}</p>
                        @foreach($cards as [$name, $val])
                            <div class="mkt-kanban-card">
                                <p class="font-semibold text-white">{{ $name }}</p>
                                <p class="text-[#8b7cff]">{{ $val }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </x-marketing.browser-frame>
    <div class="mkt-float-card--light absolute bottom-3 left-3 right-3 rounded-2xl px-4 py-3 sm:bottom-4 sm:left-4 sm:right-auto sm:px-6 sm:py-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Pipeline</p>
        <p class="text-2xl font-extrabold text-slate-900 sm:text-3xl">₹1.24L</p>
    </div>
</div>
