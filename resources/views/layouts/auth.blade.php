<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') — ConnectPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-[#f5f6fa] antialiased text-slate-900">
<div class="grid min-h-screen lg:grid-cols-2">
    {{-- Brand panel --}}
    <div class="relative hidden overflow-hidden bg-[#080b18] lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
        <div class="pointer-events-none absolute -right-20 top-20 h-80 w-80 rounded-full bg-[#635bff]/30 blur-[100px]"></div>
        <div class="pointer-events-none absolute -left-10 bottom-20 h-60 w-60 rounded-full bg-blue-500/10 blur-[80px]"></div>
        <div class="relative">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#635bff] shadow-lg shadow-[#635bff]/30">
                    <span class="text-sm font-bold text-white">CP</span>
                </div>
                <span class="text-lg font-bold text-white">ConnectPulse</span>
            </a>
            <h1 class="mt-12 max-w-md text-4xl font-extrabold leading-tight tracking-tight text-white xl:text-[2.75rem]">
                @yield('brand-headline', 'Never let a lead go cold.')
            </h1>
            <p class="mt-5 max-w-md text-base leading-relaxed text-slate-400">
                @yield('brand-sub', 'Leads, WhatsApp, follow-ups and pipeline — one workspace for businesses that sell through conversations.')
            </p>
        </div>
        <ul class="relative mt-10 space-y-3 text-sm text-slate-400">
            @foreach(['Capture leads from every channel', 'Schedule follow-ups that actually happen', 'Message customers from your CRM', 'Track pipeline and close deals'] as $item)
                <li class="flex items-center gap-3">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#635bff]/20 text-[10px] text-[#8b7cff]">✓</span>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
        <p class="relative text-xs text-slate-600">© {{ date('Y') }} ConnectPulse</p>
    </div>

    {{-- Form panel --}}
    <div class="flex min-h-screen flex-col px-5 py-8 sm:px-8 lg:px-12 xl:px-20">
        <div class="flex items-center justify-between lg:justify-end">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 lg:hidden">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#635bff] text-xs font-bold text-white">CP</div>
                <span class="font-bold text-slate-900">ConnectPulse</span>
            </a>
            <a href="{{ route('home') }}" class="text-sm font-medium text-slate-500 hover:text-[#635bff]">← Website</a>
        </div>

        <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-8">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-8">
                @yield('auth-content')
            </div>

            <p class="mt-6 text-center text-sm text-slate-500">
                @yield('auth-footer')
            </p>
        </div>
    </div>
</div>
</body>
</html>
