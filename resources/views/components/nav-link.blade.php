@props(['href', 'active' => false])

<a href="{{ $href }}"
   class="px-3 py-2 rounded-md text-sm font-medium {{ $active ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
    {{ $slot }}
</a>
