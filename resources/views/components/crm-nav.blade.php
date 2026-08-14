@if(auth()->user()?->isOrganizationAdmin())
<x-nav-link :href="route('org.crm.dashboard')" :active="request()->routeIs('org.crm.dashboard')">Dashboard</x-nav-link>
<x-nav-link :href="route('org.crm.leads.index')" :active="request()->routeIs('org.crm.leads.*')">Leads</x-nav-link>
<x-nav-link :href="route('org.crm.pipeline.index')" :active="request()->routeIs('org.crm.pipeline.*')">Pipeline</x-nav-link>
<x-nav-link :href="route('org.crm.follow-ups.index')" :active="request()->routeIs('org.crm.follow-ups.*')">Follow-ups</x-nav-link>
<x-nav-link :href="route('org.crm.reports.index')" :active="request()->routeIs('org.crm.reports.*')">Reports</x-nav-link>
<x-nav-link :href="route('org.dashboard')" :active="request()->routeIs('org.dashboard')">Messaging</x-nav-link>
@endif
