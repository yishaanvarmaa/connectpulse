@if(auth()->user()?->isOrganizationAdmin())
<nav class="fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden">
    <div class="grid grid-cols-5 h-16">
        <a href="{{ route('org.dashboard') }}" class="flex flex-col items-center justify-center gap-0.5 text-[10px] {{ request()->routeIs('org.dashboard') ? 'text-brand-600 font-medium' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h16"/></svg>
            Home
        </a>
        <a href="{{ route('org.crm.leads.index') }}" class="flex flex-col items-center justify-center gap-0.5 text-[10px] {{ request()->routeIs('org.crm.leads.*') ? 'text-brand-600 font-medium' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Leads
        </a>
        <button type="button" data-slideover-open="lead-slideover" class="flex flex-col items-center justify-center gap-0.5 text-[10px] text-brand-600">
            <div class="flex h-9 w-9 -mt-3 items-center justify-center rounded-full bg-brand-600 text-white shadow-md">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            Add
        </button>
        <a href="{{ route('org.crm.follow-ups.index') }}" class="flex flex-col items-center justify-center gap-0.5 text-[10px] {{ request()->routeIs('org.crm.follow-ups.*') ? 'text-brand-600 font-medium' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tasks
        </a>
        <a href="{{ route('org.whatsapp.index') }}" class="flex flex-col items-center justify-center gap-0.5 text-[10px] {{ request()->routeIs('org.whatsapp.*') ? 'text-brand-600 font-medium' : 'text-slate-500' }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            WhatsApp
        </a>
    </div>
</nav>
@endif
