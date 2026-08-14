{{-- Hero dashboard product mockup --}}
<x-marketing.browser-frame class="mkt-reveal">
    <div class="flex min-h-[320px] sm:min-h-[380px]">
        <div class="hidden w-14 shrink-0 border-r border-slate-200 bg-white sm:block">
            <div class="flex h-12 items-center justify-center border-b border-slate-100">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-[10px] font-bold text-white">CP</div>
            </div>
            <div class="space-y-2 p-2">
                <div class="rounded-lg bg-brand-50 px-2 py-1.5 text-[9px] font-medium text-brand-700">Home</div>
                <div class="rounded-lg px-2 py-1.5 text-[9px] font-medium text-slate-400">Leads</div>
                <div class="rounded-lg px-2 py-1.5 text-[9px] font-medium text-slate-400">Inbox</div>
            </div>
        </div>
        <div class="min-w-0 flex-1 p-3 sm:p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Good morning</p>
            <p class="text-sm font-bold text-slate-900">Sales Command Center</p>

            <div class="mt-3 rounded-xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-white p-3">
                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Next action</p>
                <div class="mt-2 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-[10px] font-bold text-brand-700">RK</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-slate-900">Ravi Kumar</p>
                        <p class="text-[10px] text-slate-500">Follow-up overdue · ₹24,999</p>
                    </div>
                </div>
                <div class="mt-2 flex gap-1">
                    <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[9px] font-medium text-emerald-700">WhatsApp</span>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[9px] font-medium text-slate-600">Call</span>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-1.5">
                <div class="rounded-lg border border-red-200 bg-red-50 px-2 py-2 text-center">
                    <p class="text-[8px] font-semibold uppercase text-red-700">Overdue</p>
                    <p class="text-sm font-bold text-slate-900">3</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-2 py-2 text-center">
                    <p class="text-[8px] font-semibold uppercase text-amber-700">Today</p>
                    <p class="text-sm font-bold text-slate-900">5</p>
                </div>
                <div class="rounded-lg border border-brand-200 bg-brand-50 px-2 py-2 text-center">
                    <p class="text-[8px] font-semibold uppercase text-brand-700">New</p>
                    <p class="text-sm font-bold text-slate-900">2</p>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="rounded-lg border border-slate-200 bg-white p-2">
                    <p class="text-[9px] text-slate-500">Pipeline</p>
                    <p class="text-sm font-bold text-slate-900">₹1.24L</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-2">
                    <p class="text-[9px] text-slate-500">Won revenue</p>
                    <p class="text-sm font-bold text-emerald-700">₹48,500</p>
                </div>
            </div>
        </div>
    </div>
</x-marketing.browser-frame>
