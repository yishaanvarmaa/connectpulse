@props(['title' => null, 'subtitle' => null])

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="flex h-14 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button id="mobile-sidebar-open" type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0">
                @if($title)
                    <h1 class="truncate text-base font-semibold text-slate-900">{{ $title }}</h1>
                @endif
                @if($subtitle)
                    <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @isset($actions)
                <div class="hidden sm:flex items-center gap-2">{{ $actions }}</div>
            @endisset

            @if(isset($shellWhatsAppConnected))
                <a href="{{ route('org.whatsapp.index') }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium {{ $shellWhatsAppConnected ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $shellWhatsAppConnected ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $shellWhatsAppConnected ? 'WhatsApp connected' : 'WhatsApp offline' }}
                </a>
            @endif

            @if(isset($shellBalance))
                <a href="{{ route('org.recharge.index') }}" class="hidden md:inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {{ number_format($shellBalance) }} credits
                </a>
            @endif
        </div>
    </div>
</header>
