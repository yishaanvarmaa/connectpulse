@props(['light' => false, 'url' => 'connectpulse.cloud/dashboard'])

<div {{ $attributes->merge(['class' => 'mkt-browser' . ($light ? ' mkt-browser--light' : '')]) }}>
    <div class="mkt-browser__chrome">
        <span class="mkt-browser__dot bg-red-500/80"></span>
        <span class="mkt-browser__dot bg-amber-500/80"></span>
        <span class="mkt-browser__dot bg-emerald-500/80"></span>
        <span class="mkt-browser__url">{{ $url }}</span>
    </div>
    <div class="{{ $light ? 'bg-slate-50' : 'bg-[#0a0e1a]' }}">
        {{ $slot }}
    </div>
</div>
