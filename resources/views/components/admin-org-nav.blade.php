@props(['organization'])

<x-nav-link :href="route('admin.organizations.show', $organization)" :active="request()->routeIs('admin.organizations.show')">Overview</x-nav-link>
<x-nav-link :href="route('admin.organizations.whatsapp', $organization)" :active="request()->routeIs('admin.organizations.whatsapp')">WhatsApp</x-nav-link>
<x-nav-link :href="route('admin.organizations.api-test', $organization)" :active="request()->routeIs('admin.organizations.api-test')">API Test</x-nav-link>
<x-nav-link :href="route('admin.organizations.index')">All Organizations</x-nav-link>
