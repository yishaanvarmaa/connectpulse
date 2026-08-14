@props(['title' => 'ConnectPulse'])

<div {{ $attributes->merge(['class' => 'mkt-browser']) }}>
    <div class="mkt-browser-chrome">
        <span class="mkt-browser-dot bg-red-400"></span>
        <span class="mkt-browser-dot bg-amber-400"></span>
        <span class="mkt-browser-dot bg-emerald-400"></span>
        <span class="ml-3 flex-1 truncate rounded-md bg-white px-3 py-1 text-[11px] text-slate-400">connectpulse.cloud/dashboard</span>
    </div>
    <div class="bg-slate-50">
        {{ $slot }}
    </div>
</div>
