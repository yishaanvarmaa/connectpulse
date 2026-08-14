@props(['title', 'description' => null, 'actionLabel' => null, 'actionHref' => null])

<div class="cp-card">
    <div class="cp-card-body flex flex-col items-center py-12 text-center">
        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        </div>
        <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
        @if($description)
            <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
        @endif
        @if($actionLabel && $actionHref)
            <a href="{{ $actionHref }}" class="cp-btn-primary mt-5">{{ $actionLabel }}</a>
        @endif
        {{ $slot }}
    </div>
</div>
