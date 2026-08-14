@props([
    'href' => null,
    'variant' => 'full',
    'theme' => 'dark',
    'size' => 'md',
    'iconVariant' => 'color',
    'mobileWordmark' => true,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 min-w-0']) }}
>
    <x-brand.icon :size="$size" :variant="$iconVariant" class="shrink-0" />

    @if($variant === 'full')
        <x-brand.wordmark
            :size="$size"
            :theme="$theme"
            @class([
                'min-w-0 truncate',
                'hidden sm:inline' => $mobileWordmark,
                'inline' => ! $mobileWordmark,
            ])
        />
    @endif
</{{ $tag }}>
