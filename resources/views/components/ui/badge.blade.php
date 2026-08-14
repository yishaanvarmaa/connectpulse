@props(['type' => 'neutral'])

@php
    $classes = match($type) {
        'success', 'won', 'sent', 'connected' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
        'warning', 'pending', 'queued', 'follow_up' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
        'danger', 'lost', 'failed' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
        'brand', 'new' => 'bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-600/20',
        default => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "cp-badge {$classes}"]) }}>{{ $slot }}</span>
