<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ConnectPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">
<div class="min-h-full">
    <nav class="bg-white border-b border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <div class="flex items-center gap-8">
                    <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('org.dashboard') }}" class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-brand-600 flex items-center justify-center">
                            <span class="text-white font-bold text-sm">CP</span>
                        </div>
                        <span class="font-semibold text-slate-900">ConnectPulse</span>
                    </a>
                    <div class="hidden md:flex items-center gap-1">
                        @yield('nav')
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        </div>
    @endif

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 {{ request()->routeIs('org.crm.*') ? 'pb-24 md:pb-8' : '' }}">
        @yield('content')
    </main>
</div>
<x-crm-mobile-nav />
@stack('scripts')
</body>
</html>
