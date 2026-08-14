@if(auth()->user()?->isOrganizationAdmin() && request()->routeIs('org.crm.*'))
<nav class="fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white md:hidden">
    <div class="grid grid-cols-5 h-16">
        <a href="{{ route('org.crm.dashboard') }}" class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('org.crm.dashboard') ? 'text-brand-600' : 'text-slate-500' }}">
            <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            Home
        </a>
        <a href="{{ route('org.crm.leads.index') }}" class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('org.crm.leads.*') ? 'text-brand-600' : 'text-slate-500' }}">
            <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Leads
        </a>
        <a href="{{ route('org.crm.leads.create') }}" class="flex flex-col items-center justify-center text-xs text-brand-600">
            <div class="h-8 w-8 -mt-4 rounded-full bg-brand-600 flex items-center justify-center text-white shadow">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            Add
        </a>
        <a href="{{ route('org.crm.follow-ups.index') }}" class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('org.crm.follow-ups.*') ? 'text-brand-600' : 'text-slate-500' }}">
            <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tasks
        </a>
        <a href="{{ route('org.crm.pipeline.index') }}" class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('org.crm.pipeline.*') ? 'text-brand-600' : 'text-slate-500' }}">
            <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
            Pipeline
        </a>
    </div>
</nav>
@endif
