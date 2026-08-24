<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ConnectPulse</title>
    <x-brand.favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        try {
            echo app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css', 'resources/js/app.js']);
        } catch (\Throwable $e) {
            report($e);
        }
    @endphp
</head>
<body class="h-full font-sans antialiased">
<div class="flex h-full min-h-screen bg-slate-50">
    {{-- Desktop sidebar --}}
    <aside class="hidden lg:flex lg:w-60 lg:flex-col lg:fixed lg:inset-y-0 border-r border-slate-200/80 bg-white">
        <x-org.sidebar />
    </aside>

    {{-- Mobile sidebar --}}
    <div id="mobile-sidebar-backdrop" class="hidden fixed inset-0 z-40 bg-slate-900/40 lg:hidden"></div>
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transform border-r border-slate-200 bg-white transition-transform duration-200 lg:hidden">
        <x-org.sidebar mobile />
    </aside>

    <div class="flex flex-1 flex-col lg:pl-60">
        @php
            $pageHeaderTitle = $__env->yieldContent('page-title');
            $pageHeaderSubtitle = $__env->yieldContent('page-subtitle');
            $pageHeaderTitle = is_string($pageHeaderTitle) && trim($pageHeaderTitle) !== '' ? trim($pageHeaderTitle) : null;
            $pageHeaderSubtitle = is_string($pageHeaderSubtitle) && trim($pageHeaderSubtitle) !== '' ? trim($pageHeaderSubtitle) : null;
        @endphp
        <x-org.topbar :title="$pageHeaderTitle" :subtitle="$pageHeaderSubtitle">
            @isset($headerActions)
                <x-slot:actions>{{ $headerActions }}</x-slot:actions>
            @endisset
        </x-org.topbar>

        @if(session('success'))
            <div class="mx-4 mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:mx-6 lg:mx-8">{{ session('success') }}</div>
            <div data-toast data-toast-type="success" class="hidden">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6 lg:mx-8">{{ session('error') }}</div>
            <div data-toast data-toast-type="error" class="hidden">{{ session('error') }}</div>
        @endif
        @if($errors->any() && ($errors->has('name') || $errors->has('phone') || $errors->has('source')))
            <div data-toast data-toast-type="error" class="hidden">{{ $errors->first() }}</div>
        @endif

        <main class="flex-1 overflow-y-auto scrollbar-thin">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 pb-24 lg:pb-8">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<x-org.mobile-nav />
@if(auth()->user()?->isOrganizationAdmin())
    <x-crm.lead-slideover />
    <x-crm.lead-sheet />
    <x-crm.whatsapp-composer action-url="#" />
@endif
@stack('scripts')
</body>
</html>
