@props(['label', 'value', 'hint' => null])

<div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $value }}</p>
    @if($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
