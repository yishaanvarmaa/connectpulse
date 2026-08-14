@if(auth()->user()?->isOrganizationAdmin())
<nav class="cp-mobile-nav fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden safe-area-pb">
    <div class="grid grid-cols-5 h-16">
        <a href="{{ route('org.dashboard') }}" class="cp-mobile-nav-item {{ request()->routeIs('org.dashboard') ? 'cp-mobile-nav-active' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <a href="{{ route('org.crm.leads.index') }}" class="cp-mobile-nav-item {{ request()->routeIs('org.crm.leads.*') ? 'cp-mobile-nav-active' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Leads
        </a>
        <button type="button" data-sheet-open="lead-sheet" class="cp-mobile-nav-item cp-mobile-nav-fab">
            <div class="flex h-11 w-11 -mt-4 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg ring-4 ring-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            </div>
        </button>
        <a href="{{ route('org.inbox.index') }}" class="cp-mobile-nav-item {{ request()->routeIs('org.inbox.*') ? 'cp-mobile-nav-active' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Inbox
        </a>
        <a href="{{ route('org.more') }}" class="cp-mobile-nav-item {{ request()->routeIs('org.more') ? 'cp-mobile-nav-active' : '' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            More
        </a>
    </div>
</nav>
@endif
