<x-nav-link :href="route('org.crm.dashboard')" :active="request()->routeIs('org.crm.dashboard')">CRM</x-nav-link>
<x-nav-link :href="route('org.crm.leads.index')" :active="request()->routeIs('org.crm.leads.*')">Leads</x-nav-link>
<x-nav-link :href="route('org.crm.pipeline.index')" :active="request()->routeIs('org.crm.pipeline.*')">Pipeline</x-nav-link>
<x-nav-link :href="route('org.crm.follow-ups.index')" :active="request()->routeIs('org.crm.follow-ups.*')">Follow-ups</x-nav-link>
<x-nav-link :href="route('org.crm.reports.index')" :active="request()->routeIs('org.crm.reports.*')">Reports</x-nav-link>
<x-nav-link :href="route('org.dashboard')" :active="request()->routeIs('org.dashboard')">Messaging</x-nav-link>
<x-nav-link :href="route('org.whatsapp.index')" :active="request()->routeIs('org.whatsapp.*')">WhatsApp</x-nav-link>
<x-nav-link :href="route('org.api-keys.index')" :active="request()->routeIs('org.api-keys.*')">API Keys</x-nav-link>
<x-nav-link :href="route('org.recharge.index')" :active="request()->routeIs('org.recharge.*')">Recharge</x-nav-link>
<x-nav-link :href="route('org.logs.index')" :active="request()->routeIs('org.logs.*')">Logs</x-nav-link>
<x-nav-link :href="route('org.settings.index')" :active="request()->routeIs('org.settings.*')">Settings</x-nav-link>
