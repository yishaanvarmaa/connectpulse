@props(['title' => null, 'subtitle' => null])

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
    <div class="flex h-14 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2">
            <button id="mobile-sidebar-open" type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('org.search') }}" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Search">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </a>
            <div class="min-w-0 hidden sm:block lg:block">
                @if($title)
                    <h1 class="truncate text-base font-semibold text-slate-900">{{ $title }}</h1>
                @endif
                @if($subtitle)
                    <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('org.search') }}" method="GET" class="hidden md:block">
                <input type="search" name="q" placeholder="Search leads, phone... ⌘K" class="cp-input !py-1.5 w-48 lg:w-64 text-xs" data-command-trigger>
            </form>

            @isset($actions)
                <div class="hidden sm:flex items-center gap-2">{{ $actions }}</div>
            @endisset

            @if(($shellNotificationCount ?? 0) > 0)
                <div class="relative">
                    <button type="button" id="notifications-toggle" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ min($shellNotificationCount, 9) }}{{ $shellNotificationCount > 9 ? '+' : '' }}</span>
                    </button>
                    <div id="notifications-panel" class="hidden absolute right-0 mt-2 w-72 rounded-xl border border-slate-200 bg-white py-2 shadow-lg">
                        <p class="px-4 py-2 text-xs font-semibold uppercase text-slate-400">Notifications</p>
                        @foreach($shellNotifications ?? [] as $note)
                            <a href="{{ $note['url'] }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50 {{ $note['type'] === 'danger' ? 'text-red-700' : ($note['type'] === 'warning' ? 'text-amber-800' : 'text-slate-700') }}">
                                {{ $note['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($shellWhatsAppConnected))
                <a href="{{ route('org.whatsapp.index') }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium {{ $shellWhatsAppConnected ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $shellWhatsAppConnected ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    <span class="hidden lg:inline">{{ $shellWhatsAppConnected ? 'Connected' : 'Offline' }}</span>
                </a>
            @endif

            @if(isset($shellBalance))
                <a href="{{ route('org.recharge.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {{ number_format($shellBalance) }}
                </a>
            @endif
        </div>
    </div>
</header>

@push('scripts')
<script>
document.getElementById('notifications-toggle')?.addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('notifications-panel')?.classList.toggle('hidden');
});
document.addEventListener('click', () => document.getElementById('notifications-panel')?.classList.add('hidden'));
</script>
@endpush
