@props(['href' => null])

<a href="{{ $href ?? route('org.crm.leads.create') }}"
   class="fixed bottom-20 right-4 z-40 md:bottom-8 md:right-8 flex items-center gap-2 rounded-full bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:bg-brand-700 active:scale-95 transition-transform">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    New Lead
</a>
