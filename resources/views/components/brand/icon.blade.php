@props([
    'size' => 'md',
    'variant' => 'color',
])

@php
    $sizes = [
        'xs' => 'h-5 w-5',
        'sm' => 'h-7 w-7',
        'md' => 'h-9 w-9',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
    ];
    $uid = 'cp-' . substr(md5(uniqid('', true)), 0, 8);
    $class = $sizes[$size] ?? $sizes['md'];
@endphp

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 48 48', 'fill' => 'none', 'xmlns' => 'http://www.w3.org/2000/svg', 'role' => 'img', 'aria-hidden' => 'true']) }}>
    @if($variant === 'color')
        <defs>
            <linearGradient id="{{ $uid }}-main" x1="8" y1="8" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#5B4BFF"/>
                <stop offset="1" stop-color="#8B7CFF"/>
            </linearGradient>
            <linearGradient id="{{ $uid }}-flow" x1="16" y1="14" x2="38" y2="38" gradientUnits="userSpaceOnUse">
                <stop stop-color="#635BFF"/>
                <stop offset="1" stop-color="#3B82F6"/>
            </linearGradient>
        </defs>
        <path d="M33.8 12.2C24.2 10 14.2 16.8 12.5 24.2 10.8 31.6 16.9 38.8 26.5 41" stroke="url(#{{ $uid }}-main)" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M26.2 18.4C20.4 19.6 16.8 23.2 16.8 27.8 16.8 31.8 19.4 35.2 24 36.8" stroke="url(#{{ $uid }}-flow)" stroke-width="3.2" stroke-linecap="round" opacity="0.92"/>
        <circle cx="34.2" cy="23.6" r="3.4" fill="#FFFFFF"/>
        <circle cx="34.2" cy="23.6" r="2" fill="url(#{{ $uid }}-main)"/>
    @elseif($variant === 'white')
        <path d="M33.8 12.2C24.2 10 14.2 16.8 12.5 24.2 10.8 31.6 16.9 38.8 26.5 41" stroke="#FFFFFF" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M26.2 18.4C20.4 19.6 16.8 23.2 16.8 27.8 16.8 31.8 19.4 35.2 24 36.8" stroke="#FFFFFF" stroke-width="3.2" stroke-linecap="round" opacity="0.55"/>
        <circle cx="34.2" cy="23.6" r="3.4" fill="#FFFFFF" opacity="0.2"/>
        <circle cx="34.2" cy="23.6" r="2" fill="#FFFFFF"/>
    @else
        <path d="M33.8 12.2C24.2 10 14.2 16.8 12.5 24.2 10.8 31.6 16.9 38.8 26.5 41" stroke="currentColor" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M26.2 18.4C20.4 19.6 16.8 23.2 16.8 27.8 16.8 31.8 19.4 35.2 24 36.8" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" opacity="0.55"/>
        <circle cx="34.2" cy="23.6" r="3.4" fill="currentColor" opacity="0.15"/>
        <circle cx="34.2" cy="23.6" r="2" fill="currentColor"/>
    @endif
</svg>
