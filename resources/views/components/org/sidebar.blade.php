@props(['mobile' => false])

@php
    $user = auth()->user();
    $orgName = $shellOrganization->company_name ?? $user->organization?->company_name ?? 'Workspace';
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-14 items-center gap-2.5 border-b border-slate-100 px-4">
        <a href="{{ route('org.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 shadow-sm">
                <span class="text-xs font-bold text-white">CP</span>
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">ConnectPulse</p>
                <p class="truncate text-[11px] text-slate-500">{{ $orgName }}</p>
            </div>
        </a>
        @if($mobile)
            <button id="mobile-sidebar-close" type="button" class="ml-auto rounded-lg p-1.5 text-slate-500 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-2 scrollbar-thin">
        <a href="{{ route('org.dashboard') }}" class="cp-sidebar-link {{ request()->routeIs('org.dashboard') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h16"/></svg>
            Dashboard
        </a>

        @if($user->isOrganizationAdmin())
            <p class="cp-section-label">Sales</p>
            <a href="{{ route('org.crm.leads.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.crm.leads.*') ? 'cp-sidebar-link-active' : '' }}">
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Leads
            </a>
            <a href="{{ route('org.crm.pipeline.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.crm.pipeline.*') ? 'cp-sidebar-link-active' : '' }}">
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                Pipeline
            </a>
            <a href="{{ route('org.crm.follow-ups.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.crm.follow-ups.*') ? 'cp-sidebar-link-active' : '' }}">
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Follow-ups
            </a>
            <a href="{{ route('org.crm.reports.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.crm.reports.*') ? 'cp-sidebar-link-active' : '' }}">
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Reports
            </a>
        @endif

        <p class="cp-section-label">Communication</p>
        <a href="{{ route('org.logs.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.logs.*') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Inbox / Logs
        </a>
        <a href="{{ route('org.whatsapp.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.whatsapp.*') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.606.606l4.458-1.495A11.953 11.953 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.006-1.372l-.357-.212-3.305 1.108 1.108-3.305-.212-.357A9.818 9.818 0 1112 21.818z"/></svg>
            WhatsApp
        </a>

        <p class="cp-section-label">Platform</p>
        <a href="{{ route('org.api-keys.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.api-keys.*') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            API
        </a>
        <a href="{{ route('org.recharge.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.recharge.*') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Credits
        </a>
        <a href="{{ route('org.settings.index') }}" class="cp-sidebar-link {{ request()->routeIs('org.settings.*') ? 'cp-sidebar-link-active' : '' }}">
            <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
    </nav>

    <div class="border-t border-slate-100 p-3">
        <div class="flex items-center gap-2.5 rounded-lg bg-slate-50 px-2.5 py-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-slate-900">{{ $user->name }}</p>
                <p class="truncate text-[11px] text-slate-500">{{ $orgName }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded p-1 text-slate-400 hover:bg-white hover:text-slate-600" title="Sign out">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
