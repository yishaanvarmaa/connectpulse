@props(['label', 'value', 'hint' => null, 'trend' => null])

<div class="cp-card cp-card-body">
    <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
    @if($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @if($trend)
        <p class="mt-1 text-xs font-medium {{ str_starts_with($trend, '+') ? 'text-emerald-600' : 'text-slate-500' }}">{{ $trend }}</p>
    @endif
</div>
