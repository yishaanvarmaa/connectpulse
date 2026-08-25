{{-- Product dashboard mockup — full UI visible on every screen size --}}
@props(['compact' => false])

@php
    $minH = $compact ? 'min-h-[220px] sm:min-h-[280px]' : 'min-h-[300px] sm:min-h-[360px] lg:min-h-[420px]';
@endphp

<div class="mkt-product-shot">
    <div class="mkt-product-shot__glow" aria-hidden="true"></div>
    <div class="mkt-product-shot__frame">
        <x-marketing.browser-frame class="w-full">
            <div class="flex {{ $minH }}">
                {{-- Side nav: sm+ --}}
                <div class="hidden w-14 shrink-0 border-r border-white/5 bg-[#0d1020] sm:block lg:w-16">
                    <div class="flex h-12 items-center justify-center border-b border-white/5 lg:h-14">
                        <x-brand.icon size="sm" />
                    </div>
                    @foreach(['Home', 'Leads', 'Inbox', 'Pipe', 'Stats'] as $i => $n)
                        <div class="mx-1.5 mt-1.5 rounded-lg px-1 py-1.5 text-center text-[8px] font-medium lg:mx-2 lg:mt-2 lg:px-2 lg:py-2 lg:text-[9px] {{ $i === 0 ? 'bg-[#635bff]/20 text-[#8b7cff]' : 'text-slate-600' }}">{{ $n }}</div>
                    @endforeach
                </div>

                <div class="min-w-0 flex-1 overflow-hidden p-3 sm:p-4 lg:p-5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-500 sm:text-[10px]">Sales Command Center</p>
                            <p class="truncate text-base font-bold text-white sm:text-lg lg:text-xl">Good morning, Admin</p>
                        </div>
                        <span class="shrink-0 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[9px] font-medium text-emerald-400 sm:px-2.5 sm:py-1 sm:text-[10px]">
                            <span class="sm:hidden">● Live</span>
                            <span class="hidden sm:inline">WhatsApp Connected</span>
                        </span>
                    </div>

                    <div class="mt-3 rounded-xl border-2 border-amber-500/30 bg-gradient-to-br from-amber-500/10 to-transparent p-3 sm:mt-4 sm:p-4">
                        <p class="text-[8px] font-bold uppercase tracking-widest text-amber-400/80 sm:text-[9px]">Next action</p>
                        <div class="mt-2 flex items-center gap-2.5 sm:gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#635bff]/30 text-[11px] font-bold text-[#8b7cff] sm:h-10 sm:w-10 sm:text-xs">RK</div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-white sm:text-base">Ravi Kumar</p>
                                <p class="truncate text-[10px] text-slate-400 sm:text-xs">Follow-up overdue · Diagnostic Package</p>
                            </div>
                            <p class="shrink-0 text-xs font-bold text-white sm:text-sm">₹24,999</p>
                        </div>
                        <div class="mt-2.5 flex flex-wrap gap-1.5 sm:mt-3 sm:gap-2">
                            <span class="rounded-lg bg-emerald-600 px-2.5 py-1 text-[9px] font-semibold text-white sm:px-3 sm:py-1.5 sm:text-[10px]">WhatsApp</span>
                            <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-semibold text-slate-300 sm:px-3 sm:py-1.5 sm:text-[10px]">Call</span>
                            <span class="rounded-lg bg-[#635bff] px-2.5 py-1 text-[9px] font-semibold text-white sm:px-3 sm:py-1.5 sm:text-[10px]">Complete</span>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-1.5 sm:mt-4 sm:gap-3">
                        <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-2 text-center sm:p-3">
                            <p class="text-[8px] font-bold uppercase text-red-400 sm:text-[9px]">Overdue</p>
                            <p class="text-xl font-bold text-white sm:text-2xl">3</p>
                        </div>
                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-2 text-center sm:p-3">
                            <p class="text-[8px] font-bold uppercase text-amber-400 sm:text-[9px]">Today</p>
                            <p class="text-xl font-bold text-white sm:text-2xl">5</p>
                        </div>
                        <div class="rounded-xl border border-[#635bff]/30 bg-[#635bff]/10 p-2 text-center sm:p-3">
                            <p class="text-[8px] font-bold uppercase text-[#8b7cff] sm:text-[9px]">New</p>
                            <p class="text-xl font-bold text-white sm:text-2xl">2</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:gap-3">
                        <div class="rounded-xl border border-white/10 bg-[#12162a] p-2.5 sm:p-3">
                            <p class="text-[9px] text-slate-500 sm:text-[10px]">Pipeline value</p>
                            <p class="text-lg font-bold text-white sm:text-xl">₹1.24L</p>
                            <p class="text-[9px] text-slate-500 sm:text-[10px]">22 open leads</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-[#12162a] p-2.5 sm:p-3">
                            <p class="text-[9px] text-slate-500 sm:text-[10px]">Won revenue</p>
                            <p class="text-lg font-bold text-emerald-400 sm:text-xl">₹48,500</p>
                            <p class="text-[9px] text-emerald-500/70 sm:text-[10px]">↑ 12% this month</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-marketing.browser-frame>
    </div>
</div>
