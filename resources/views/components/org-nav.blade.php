<x-nav-link :href="route('org.dashboard')" :active="request()->routeIs('org.dashboard')">Dashboard</x-nav-link>
<x-nav-link :href="route('org.whatsapp.index')" :active="request()->routeIs('org.whatsapp.*')">WhatsApp</x-nav-link>
<x-nav-link :href="route('org.api-keys.index')" :active="request()->routeIs('org.api-keys.*')">API Keys</x-nav-link>
<x-nav-link :href="route('org.recharge.index')" :active="request()->routeIs('org.recharge.*')">Recharge</x-nav-link>
<x-nav-link :href="route('org.logs.index')" :active="request()->routeIs('org.logs.*')">Logs</x-nav-link>
<x-nav-link :href="route('org.settings.index')" :active="request()->routeIs('org.settings.*')">Settings</x-nav-link>
@if(auth()->user()?->isOrganizationAdmin())
    <x-nav-link :href="route('org.crm.dashboard')" :active="request()->routeIs('org.crm.*')">CRM</x-nav-link>
@endif
