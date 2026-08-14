@props([
    'size' => 'md',
    'theme' => 'dark',
])

@php
    $sizes = [
        'sm' => 'text-base',
        'md' => 'text-lg',
        'lg' => 'text-xl',
    ];
    $connectClass = $theme === 'light' ? 'text-slate-900' : 'text-white';
    $textSize = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "{$textSize} font-bold tracking-tight whitespace-nowrap"]) }}>
    <span class="{{ $connectClass }}">Connect</span><span class="bg-gradient-to-r from-[#5B4BFF] to-[#8B7CFF] bg-clip-text text-transparent">Pulse</span>
</span>
