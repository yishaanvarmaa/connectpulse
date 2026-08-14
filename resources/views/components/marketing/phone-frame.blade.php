@props(['label' => 'ConnectPulse'])

<div {{ $attributes->merge(['class' => 'mkt-phone']) }}>
    <div class="mx-auto mb-1 h-1 w-16 rounded-full bg-slate-700"></div>
    <div class="mkt-phone-screen">
        {{ $slot }}
    </div>
</div>
