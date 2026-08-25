{{-- Large hero dashboard mockup --}}
<div class="relative mkt-float-slow max-w-full">
    <div class="pointer-events-none absolute inset-0 -z-10 rounded-3xl bg-[#635bff]/20 blur-3xl"></div>
    <x-marketing.browser-frame class="relative w-full max-w-full">
        <div class="flex min-h-[300px] sm:min-h-[420px] lg:min-h-[480px]">
            <div class="hidden w-16 shrink-0 border-r border-white/5 bg-[#0d1020] lg:block">
                <div class="flex h-14 items-center justify-center border-b border-white/5">
                    <x-brand.icon size="sm" />
                </div>
                @foreach(['Home', 'Leads', 'Inbox', 'Pipeline', 'Reports'] as $i => $n)
                    <div class="mx-2 mt-2 rounded-lg px-2 py-2 text-center text-[9px] font-medium {{ $i === 0 ? 'bg-[#635bff]/20 text-[#8b7cff]' : 'text-slate-600' }}">{{ $n }}</div>
                @endforeach
            </div>
            <div class="min-w-0 flex-1 p-4 sm:p-5 lg:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Sales Command Center</p>
                        <p class="text-lg font-bold text-white sm:text-xl">Good morning, Admin</p>
                    </div>
                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-medium text-emerald-400">WhatsApp Connected</span>
                </div>

                <div class="mt-4 rounded-xl border-2 border-amber-500/30 bg-gradient-to-br from-amber-500/10 to-transparent p-4">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-amber-400/80">Next action</p>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#635bff]/30 text-xs font-bold text-[#8b7cff]">RK</div>
                        <div class="flex-1">
                            <p class="font-semibold text-white">Ravi Kumar</p>
                            <p class="text-xs text-slate-400">Follow-up overdue · Diagnostic Package</p>
                        </div>
                        <p class="text-sm font-bold text-white">₹24,999</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <span class="rounded-lg bg-emerald-600 px-3 py-1.5 text-[10px] font-semibold text-white">WhatsApp</span>
                        <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-semibold text-slate-300">Call</span>
                        <span class="rounded-lg bg-[#635bff] px-3 py-1.5 text-[10px] font-semibold text-white">Complete</span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-center">
                        <p class="text-[9px] font-bold uppercase text-red-400">Overdue</p>
                        <p class="text-2xl font-bold text-white">3</p>
                    </div>
                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-center">
                        <p class="text-[9px] font-bold uppercase text-amber-400">Today</p>
                        <p class="text-2xl font-bold text-white">5</p>
                    </div>
                    <div class="rounded-xl border border-[#635bff]/30 bg-[#635bff]/10 p-3 text-center">
                        <p class="text-[9px] font-bold uppercase text-[#8b7cff]">New</p>
                        <p class="text-2xl font-bold text-white">2</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-white/10 bg-[#12162a] p-3">
                        <p class="text-[10px] text-slate-500">Pipeline value</p>
                        <p class="text-xl font-bold text-white">₹1.24L</p>
                        <p class="text-[10px] text-slate-500">22 open leads</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-[#12162a] p-3">
                        <p class="text-[10px] text-slate-500">Won revenue</p>
                        <p class="text-xl font-bold text-emerald-400">₹48,500</p>
                        <p class="text-[10px] text-emerald-500/70">↑ 12% this month</p>
                    </div>
                </div>
            </div>
        </div>
    </x-marketing.browser-frame>
</div>
