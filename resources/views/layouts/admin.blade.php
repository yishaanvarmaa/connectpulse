<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — ConnectPulse</title>
    <x-brand.favicon />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">
<div class="flex min-h-screen bg-slate-50">
    <aside class="relative hidden w-56 shrink-0 flex-col border-r border-slate-200 bg-white lg:flex">
        <div class="flex h-14 items-center gap-2 border-b border-slate-100 px-4">
            <x-brand.icon size="sm" class="shrink-0" />
            <div>
                <x-brand.wordmark size="sm" theme="light" class="!text-sm !font-semibold" />
                <p class="text-[10px] text-slate-500">Super Admin</p>
            </div>
        </div>
        <nav class="flex-1 space-y-0.5 p-3">
            <a href="{{ route('admin.dashboard') }}" class="cp-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'cp-sidebar-link-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.organizations.index') }}" class="cp-sidebar-link {{ request()->routeIs('admin.organizations.*') ? 'cp-sidebar-link-active' : '' }}">Organizations</a>
            <a href="{{ route('admin.credits.index') }}" class="cp-sidebar-link {{ request()->routeIs('admin.credits.*') ? 'cp-sidebar-link-active' : '' }}">Credits</a>
            <a href="{{ route('admin.logs.index') }}" class="cp-sidebar-link {{ request()->routeIs('admin.logs.*') ? 'cp-sidebar-link-active' : '' }}">Logs</a>
        </nav>
        <div class="mt-auto border-t border-slate-100 p-3">
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="cp-btn-ghost w-full justify-start text-xs">Sign out</button></form>
        </div>
    </aside>
    <div class="flex flex-1 flex-col min-w-0">
        <header class="flex h-14 items-center justify-between border-b border-slate-200 bg-white px-6">
            <h1 class="text-base font-semibold text-slate-900">@yield('title', 'Admin')</h1>
            <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
        </header>
        @if(session('success'))<div data-toast data-toast-type="success" class="hidden">{{ session('success') }}</div>@endif
        @if(session('error'))<div data-toast data-toast-type="error" class="hidden">{{ session('error') }}</div>@endif
        <main class="flex-1 p-6">@yield('content')</main>
    </div>
</div>
@stack('scripts')
</body>
</html>
