@props(['name'])

@php
    $initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($w) => strtoupper(substr($w, 0, 1)))->join('');
    $colors = ['bg-brand-100 text-brand-700', 'bg-violet-100 text-violet-700', 'bg-sky-100 text-sky-700', 'bg-emerald-100 text-emerald-700'];
    $color = $colors[crc32($name) % count($colors)];
@endphp

<div {{ $attributes->merge(['class' => "flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold {$color}"]) }}>
    {{ $initials ?: '?' }}
</div>
